<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Grade;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class StudentEnrollmentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', StudentEnrollment::class);

        $user = $request->user();
        $canManageAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);

        $query = StudentEnrollment::query()
            ->with(['student.branch', 'academicYear', 'grade', 'section']);

        if (! $canManageAll) {
            $query->whereHas('student', fn (Builder $q) => $q->where('branch_id', $user->branch_id));
        } elseif ($request->filled('branch_id')) {
            $query->whereHas('student', fn (Builder $q) => $q->where('branch_id', $request->integer('branch_id')));
        }

        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->whereHas('student', function (Builder $q) use ($term) {
                $q->where('first_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%")
                    ->orWhere('admission_number', 'like', "%{$term}%");
            });
        }

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->integer('academic_year_id'));
        }

        if ($request->filled('grade_id')) {
            $query->where('grade_id', $request->integer('grade_id'));
        }

        if ($request->filled('section_id')) {
            $query->where('section_id', $request->integer('section_id'));
        }

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

        $baseCountQuery = StudentEnrollment::query()
            ->when(! $canManageAll, fn ($q) => $q->whereHas('student', fn ($sq) => $sq->where('branch_id', $user->branch_id)))
            ->when($canManageAll && $selectedBranchId, fn ($q) => $q->whereHas('student', fn ($sq) => $sq->where('branch_id', $selectedBranchId)));

        $totalEnrollments = (clone $baseCountQuery)->count();
        $activeEnrollments = (clone $baseCountQuery)->where('status', 'active')->count();
        $promotedEnrollments = (clone $baseCountQuery)->where('status', 'promoted')->count();

        $enrollments = $query->orderByDesc('enrolled_at')->orderByDesc('id')->paginate(15)->withQueryString();

        return view('enrollments.index', [
            'enrollments' => $enrollments,
            'branches' => $branches,
            'grades' => $grades,
            'sections' => $sections,
            'academicYears' => $academicYears,
            'selectedBranchId' => $selectedBranchId,
            'totalEnrollments' => $totalEnrollments,
            'activeEnrollments' => $activeEnrollments,
            'promotedEnrollments' => $promotedEnrollments,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', StudentEnrollment::class);

        $user = $request->user();
        $canManageAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);
        $branches = $this->availableBranches($user->branch_id, $canManageAll);

        $selectedBranchId = $canManageAll
            ? ($request->filled('branch_id') ? $request->integer('branch_id') : $branches->first()?->id)
            : $user->branch_id;

        $students = Student::query()
            ->when($selectedBranchId, fn (Builder $q) => $q->where('branch_id', $selectedBranchId))
            ->where('status', 'active')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

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

        return view('enrollments.form', [
            'enrollment' => null,
            'students' => $students,
            'branches' => $branches,
            'selectedBranchId' => $selectedBranchId,
            'grades' => $grades,
            'sections' => $sections,
            'academicYears' => $academicYears,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', StudentEnrollment::class);

        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'grade_id' => ['required', 'exists:grades,id'],
            'section_id' => ['nullable', 'exists:sections,id'],
            'roll_number' => ['nullable', 'string', 'max:50'],
            'enrolled_at' => ['required', 'date'],
            'status' => ['required', 'in:active,promoted,repeated,transferred,completed'],
        ]);

        $exists = StudentEnrollment::where('student_id', $data['student_id'])
            ->where('academic_year_id', $data['academic_year_id'])
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Student is already enrolled in this academic year.');
        }

        $enrollment = StudentEnrollment::create($data);

        return redirect()->route('enrollments.index')
            ->with('success', "Enrollment created for {$enrollment->student->full_name}.");
    }

    public function edit(StudentEnrollment $enrollment): View
    {
        $this->authorize('update', $enrollment);

        $enrollment->load(['student.branch', 'grade', 'section', 'academicYear']);
        $branchId = $enrollment->student->branch_id;

        $grades = Grade::where('branch_id', $branchId)->orderBy('level')->orderBy('name')->get();
        $sections = Section::whereIn('grade_id', $grades->pluck('id'))->orderBy('name')->get();
        $academicYears = AcademicYear::where('branch_id', $branchId)->orderByDesc('start_date')->get();

        return view('enrollments.form', [
            'enrollment' => $enrollment,
            'students' => collect([$enrollment->student]),
            'branches' => collect([$enrollment->student->branch]),
            'selectedBranchId' => $branchId,
            'grades' => $grades,
            'sections' => $sections,
            'academicYears' => $academicYears,
        ]);
    }

    public function update(Request $request, StudentEnrollment $enrollment): RedirectResponse
    {
        $this->authorize('update', $enrollment);

        $data = $request->validate([
            'grade_id' => ['required', 'exists:grades,id'],
            'section_id' => ['nullable', 'exists:sections,id'],
            'roll_number' => ['nullable', 'string', 'max:50'],
            'enrolled_at' => ['required', 'date'],
            'status' => ['required', 'in:active,promoted,repeated,transferred,completed'],
        ]);

        $enrollment->update($data);

        return redirect()->route('enrollments.index')
            ->with('success', "Enrollment updated for {$enrollment->student->full_name}.");
    }

    public function destroy(StudentEnrollment $enrollment): RedirectResponse
    {
        $this->authorize('delete', $enrollment);

        $enrollment->delete();

        return redirect()->route('enrollments.index')
            ->with('success', 'Enrollment record removed.');
    }

    private function availableBranches(?int $branchId, bool $canViewAll): Collection
    {
        return Branch::query()
            ->when(! $canViewAll, fn (Builder $query) => $query->whereKey($branchId))
            ->orderBy('name')
            ->get();
    }
}
