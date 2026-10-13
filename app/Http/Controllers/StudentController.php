<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentRequest;
use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Grade;
use App\Models\Guardian;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Student::class);

        $user = $request->user();
        $canManageAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);

        $query = Student::query()
            ->with([
                'branch',
                'latestEnrollment.grade',
                'latestEnrollment.section',
                'latestEnrollment.academicYear',
                'guardians',
            ]);

        // Branch scoping
        if (! $canManageAll) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        // Search
        if ($request->filled('search')) {
            $query->search($request->string('search')->toString());
        }

        // Grade filter
        if ($request->filled('grade_id')) {
            $gradeId = $request->integer('grade_id');
            $query->whereHas('enrollments', fn (Builder $q) => $q->where('grade_id', $gradeId));
        }

        // Section filter
        if ($request->filled('section_id')) {
            $sectionId = $request->integer('section_id');
            $query->whereHas('enrollments', fn (Builder $q) => $q->where('section_id', $sectionId));
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $branches = $this->availableBranches($user->branch_id, $canManageAll);
        $selectedBranchId = $canManageAll
            ? ($request->filled('branch_id') ? $request->integer('branch_id') : null)
            : $user->branch_id;

        $grades = Grade::query()
            ->when($selectedBranchId, fn (Builder $q) => $q->where('branch_id', $selectedBranchId))
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        $sections = Section::query()
            ->when($request->filled('grade_id'), fn (Builder $q) => $q->where('grade_id', $request->integer('grade_id')))
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::query()
            ->when($selectedBranchId, fn (Builder $q) => $q->where('branch_id', $selectedBranchId))
            ->orderByDesc('start_date')
            ->get();

        // Stats counts
        $baseCountQuery = Student::query()
            ->when(! $canManageAll, fn ($q) => $q->where('branch_id', $user->branch_id))
            ->when($canManageAll && $selectedBranchId, fn ($q) => $q->where('branch_id', $selectedBranchId));

        $totalStudents = (clone $baseCountQuery)->count();
        $activeStudents = (clone $baseCountQuery)->where('status', 'active')->count();
        $inactiveStudents = (clone $baseCountQuery)->where('status', '!=', 'active')->count();

        $students = $query->orderBy('last_name')->orderBy('first_name')->paginate(15)->withQueryString();

        return view('students.index', [
            'students' => $students,
            'branches' => $branches,
            'grades' => $grades,
            'sections' => $sections,
            'academicYears' => $academicYears,
            'selectedBranchId' => $selectedBranchId,
            'totalStudents' => $totalStudents,
            'activeStudents' => $activeStudents,
            'inactiveStudents' => $inactiveStudents,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Student::class);

        $user = $request->user();
        $canManageAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);
        $branches = $this->availableBranches($user->branch_id, $canManageAll);

        $selectedBranchId = $canManageAll
            ? ($request->filled('branch_id') ? $request->integer('branch_id') : $branches->first()?->id)
            : $user->branch_id;

        $selectedBranch = $branches->firstWhere('id', $selectedBranchId);

        $grades = Grade::query()
            ->when($selectedBranchId, fn (Builder $q) => $q->where('branch_id', $selectedBranchId))
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        $sections = Section::query()
            ->whereIn('grade_id', $grades->pluck('id'))
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::query()
            ->when($selectedBranchId, fn (Builder $q) => $q->where('branch_id', $selectedBranchId))
            ->orderByDesc('start_date')
            ->get();

        $studentTypes = \App\Models\StudentType::query()
            ->when($selectedBranchId, fn (Builder $q) => $q->where(fn ($sub) => $sub->whereNull('branch_id')->orWhere('branch_id', $selectedBranchId)))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $generatedAdmissionNumber = Student::generateAdmissionNumber($selectedBranch);

        return view('students.form', [
            'student' => null,
            'branches' => $branches,
            'selectedBranchId' => $selectedBranchId,
            'grades' => $grades,
            'sections' => $sections,
            'academicYears' => $academicYears,
            'studentTypes' => $studentTypes,
            'generatedAdmissionNumber' => $generatedAdmissionNumber,
            'currentEnrollment' => null,
            'primaryGuardian' => null,
        ]);
    }

    public function store(StudentRequest $request): RedirectResponse
    {
        $this->authorize('create', Student::class);

        $data = $request->validated();

        $branch = Branch::findOrFail($data['branch_id']);

        if (empty($data['admission_number'])) {
            $data['admission_number'] = Student::generateAdmissionNumber($branch);
        }

        $student = DB::transaction(function () use ($data) {
            $student = Student::create([
                'branch_id' => $data['branch_id'],
                'student_type_id' => $data['student_type_id'] ?? null,
                'admission_number' => $data['admission_number'],
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'last_name' => $data['last_name'],
                'gender' => $data['gender'],
                'date_of_birth' => $data['date_of_birth'],
                'admission_date' => $data['admission_date'],
                'blood_group' => $data['blood_group'] ?? null,
                'religion' => $data['religion'] ?? null,
                'national_id' => $data['national_id'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
                'medical_notes' => $data['medical_notes'] ?? null,
                'status' => $data['status'] ?? 'active',
            ]);

            // Create primary guardian if supplied
            if (! empty($data['guardian_first_name']) && ! empty($data['guardian_phone'])) {
                $guardian = Guardian::create([
                    'first_name' => $data['guardian_first_name'],
                    'last_name' => $data['guardian_last_name'] ?? '',
                    'phone' => $data['guardian_phone'],
                    'email' => $data['guardian_email'] ?? null,
                    'occupation' => $data['guardian_occupation'] ?? null,
                    'address' => $data['guardian_address'] ?? $data['address'] ?? null,
                ]);

                $student->guardians()->attach($guardian->id, [
                    'relationship' => $data['guardian_relationship'] ?? 'guardian',
                    'is_primary' => true,
                    'is_emergency_contact' => true,
                ]);
            }

            // Create initial enrollment
            $academicYearId = $data['academic_year_id'] ?? null;
            if (! $academicYearId) {
                $activeYear = AcademicYear::where('branch_id', $data['branch_id'])
                    ->where('status', 'active')
                    ->first();
                $academicYearId = $activeYear?->id ?? AcademicYear::where('branch_id', $data['branch_id'])->first()?->id;
            }

            if ($academicYearId && ! empty($data['grade_id'])) {
                StudentEnrollment::create([
                    'student_id' => $student->id,
                    'academic_year_id' => $academicYearId,
                    'grade_id' => $data['grade_id'],
                    'section_id' => $data['section_id'] ?? null,
                    'roll_number' => $data['roll_number'] ?? null,
                    'enrolled_at' => $data['admission_date'],
                    'status' => 'active',
                ]);
            }

            return $student;
        });

        return redirect()->route('students.show', $student)
            ->with('success', "Student {$student->full_name} ({$student->admission_number}) registered successfully.");
    }

    public function show(Student $student): View
    {
        $this->authorize('view', $student);

        $student->load([
            'branch',
            'studentType',
            'groups',
            'guardians',
            'enrollments.grade',
            'enrollments.section',
            'enrollments.academicYear',
            'invoices.academicYear',
            'invoices.term',
            'invoices.payments',
        ]);

        return view('students.show', compact('student'));
    }

    public function edit(Student $student): View
    {
        $this->authorize('update', $student);

        $student->load(['branch', 'guardians', 'latestEnrollment']);

        $branches = collect([$student->branch]);
        $grades = Grade::where('branch_id', $student->branch_id)->orderBy('level')->orderBy('name')->get();
        $sections = Section::whereIn('grade_id', $grades->pluck('id'))->orderBy('name')->get();
        $academicYears = AcademicYear::where('branch_id', $student->branch_id)->orderByDesc('start_date')->get();
        $studentTypes = \App\Models\StudentType::where(fn ($sub) => $sub->whereNull('branch_id')->orWhere('branch_id', $student->branch_id))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('students.form', [
            'student' => $student,
            'branches' => $branches,
            'selectedBranchId' => $student->branch_id,
            'grades' => $grades,
            'sections' => $sections,
            'academicYears' => $academicYears,
            'studentTypes' => $studentTypes,
            'generatedAdmissionNumber' => $student->admission_number,
            'currentEnrollment' => $student->latestEnrollment,
            'primaryGuardian' => $student->primary_guardian,
        ]);
    }

    public function update(StudentRequest $request, Student $student): RedirectResponse
    {
        $this->authorize('update', $student);

        $data = $request->validated();

        DB::transaction(function () use ($student, $data) {
            $student->update([
                'student_type_id' => $data['student_type_id'] ?? null,
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'last_name' => $data['last_name'],
                'gender' => $data['gender'],
                'date_of_birth' => $data['date_of_birth'],
                'admission_date' => $data['admission_date'],
                'blood_group' => $data['blood_group'] ?? null,
                'religion' => $data['religion'] ?? null,
                'national_id' => $data['national_id'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
                'medical_notes' => $data['medical_notes'] ?? null,
                'status' => $data['status'],
            ]);

            // Update primary guardian if exists or create
            if (! empty($data['guardian_first_name']) && ! empty($data['guardian_phone'])) {
                $primaryGuardian = $student->primary_guardian;
                if ($primaryGuardian) {
                    $primaryGuardian->update([
                        'first_name' => $data['guardian_first_name'],
                        'last_name' => $data['guardian_last_name'] ?? '',
                        'phone' => $data['guardian_phone'],
                        'email' => $data['guardian_email'] ?? null,
                        'occupation' => $data['guardian_occupation'] ?? null,
                        'address' => $data['guardian_address'] ?? null,
                    ]);

                    if (! empty($data['guardian_relationship'])) {
                        $student->guardians()->updateExistingPivot($primaryGuardian->id, [
                            'relationship' => $data['guardian_relationship'],
                        ]);
                    }
                } else {
                    $guardian = Guardian::create([
                        'first_name' => $data['guardian_first_name'],
                        'last_name' => $data['guardian_last_name'] ?? '',
                        'phone' => $data['guardian_phone'],
                        'email' => $data['guardian_email'] ?? null,
                        'occupation' => $data['guardian_occupation'] ?? null,
                        'address' => $data['guardian_address'] ?? null,
                    ]);

                    $student->guardians()->attach($guardian->id, [
                        'relationship' => $data['guardian_relationship'] ?? 'guardian',
                        'is_primary' => true,
                        'is_emergency_contact' => true,
                    ]);
                }
            }

            // Update or create enrollment placement
            if (! empty($data['grade_id'])) {
                $academicYearId = $data['academic_year_id'] ?? null;
                if (! $academicYearId) {
                    $activeYear = AcademicYear::where('branch_id', $student->branch_id)
                        ->where('status', 'active')
                        ->first();
                    $academicYearId = $activeYear?->id ?? AcademicYear::where('branch_id', $student->branch_id)->first()?->id;
                }

                if ($academicYearId) {
                    StudentEnrollment::updateOrCreate(
                        [
                            'student_id' => $student->id,
                            'academic_year_id' => $academicYearId,
                        ],
                        [
                            'grade_id' => $data['grade_id'],
                            'section_id' => $data['section_id'] ?? null,
                            'roll_number' => $data['roll_number'] ?? null,
                            'enrolled_at' => $data['admission_date'],
                            'status' => 'active',
                        ]
                    );
                }
            }
        });

        return redirect()->route('students.show', $student)
            ->with('success', 'Student profile updated successfully.');
    }

    public function toggleStatus(Request $request, Student $student): RedirectResponse
    {
        $this->authorize('update', $student);

        $newStatus = $student->status === 'active' ? 'inactive' : 'active';
        $student->update(['status' => $newStatus]);

        $message = $newStatus === 'active'
            ? "Student {$student->full_name} has been activated."
            : "Student {$student->full_name} has been deactivated.";

        return back()->with('success', $message);
    }

    public function destroy(Student $student): RedirectResponse
    {
        $this->authorize('delete', $student);

        $student->delete();

        return redirect()->route('students.index')
            ->with('success', 'Student record removed.');
    }

    private function availableBranches(?int $branchId, bool $canViewAll): Collection
    {
        return Branch::query()
            ->when(! $canViewAll, fn (Builder $query) => $query->whereKey($branchId))
            ->orderBy('name')
            ->get();
    }
}
