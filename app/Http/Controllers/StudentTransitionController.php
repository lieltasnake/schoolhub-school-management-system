<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Grade;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentTransition;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StudentTransitionController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->canManageStudentTransitions(), 403, 'Unauthorized to manage student transitions.');

        $canManageAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);
        $branches = $this->availableBranches($user->branch_id, $canManageAll);

        $selectedBranchId = $canManageAll
            ? ($request->filled('branch_id') ? $request->integer('branch_id') : $branches->first()?->id)
            : $user->branch_id;

        $tab = in_array($request->query('tab'), ['withdraw', 'graduate', 'repeat', 'history'])
            ? $request->query('tab')
            : 'withdraw';

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

        // Active students available for action in selected branch
        $activeStudents = Student::query()
            ->with(['latestEnrollment.grade', 'latestEnrollment.section'])
            ->when($selectedBranchId, fn ($q) => $q->where('branch_id', $selectedBranchId))
            ->where('status', 'active')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        // Tab-specific datasets
        $withdrawnStudents = collect();
        $graduatedStudents = collect();
        $repeatingTransitions = collect();
        $historyTransitions = collect();

        if ($tab === 'withdraw') {
            $withdrawnStudents = Student::query()
                ->with(['latestEnrollment.grade', 'transitions' => fn ($q) => $q->where('transition_type', 'withdrawal')->latest('transition_date')])
                ->when($selectedBranchId, fn ($q) => $q->where('branch_id', $selectedBranchId))
                ->where('status', 'withdrawn')
                ->orderByDesc('updated_at')
                ->paginate(15)
                ->withQueryString();
        } elseif ($tab === 'graduate') {
            $graduatedStudents = Student::query()
                ->with(['latestEnrollment.grade', 'transitions' => fn ($q) => $q->where('transition_type', 'graduation')->latest('transition_date')])
                ->when($selectedBranchId, fn ($q) => $q->where('branch_id', $selectedBranchId))
                ->where('status', 'graduated')
                ->orderByDesc('updated_at')
                ->paginate(15)
                ->withQueryString();
        } elseif ($tab === 'repeat') {
            $repeatingTransitions = StudentTransition::query()
                ->with(['student', 'fromAcademicYear', 'toAcademicYear', 'fromGrade', 'processor'])
                ->where('transition_type', 'repeat')
                ->when($selectedBranchId, fn ($q) => $q->where('branch_id', $selectedBranchId))
                ->orderByDesc('transition_date')
                ->paginate(15)
                ->withQueryString();
        } elseif ($tab === 'history') {
            $historyQuery = StudentTransition::query()
                ->with(['student', 'branch', 'fromGrade', 'toGrade', 'fromAcademicYear', 'toAcademicYear', 'processor'])
                ->when($selectedBranchId, fn ($q) => $q->where('branch_id', $selectedBranchId));

            if ($request->filled('type')) {
                $historyQuery->where('transition_type', $request->string('type'));
            }

            if ($request->filled('search')) {
                $term = $request->string('search')->toString();
                $historyQuery->whereHas('student', function ($sq) use ($term) {
                    $sq->where('first_name', 'like', "%{$term}%")
                        ->orWhere('last_name', 'like', "%{$term}%")
                        ->orWhere('admission_number', 'like', "%{$term}%");
                });
            }

            $historyTransitions = $historyQuery
                ->orderByDesc('transition_date')
                ->orderByDesc('id')
                ->paginate(20)
                ->withQueryString();
        }

        return view('school-setup.student-transitions.index', [
            'tab'                  => $tab,
            'branches'             => $branches,
            'selectedBranchId'     => $selectedBranchId,
            'grades'               => $grades,
            'sections'             => $sections,
            'academicYears'        => $academicYears,
            'activeStudents'       => $activeStudents,
            'withdrawnStudents'    => $withdrawnStudents,
            'graduatedStudents'    => $graduatedStudents,
            'repeatingTransitions' => $repeatingTransitions,
            'historyTransitions'   => $historyTransitions,
        ]);
    }

    public function withdraw(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->canManageStudentTransitions(), 403, 'Unauthorized.');

        $data = $request->validate([
            'branch_id'       => ['required', 'exists:branches,id'],
            'student_id'      => ['required', 'exists:students,id'],
            'transition_date' => ['required', 'date'],
            'reason'          => ['required', 'string', 'max:255'],
            'notes'           => ['nullable', 'string', 'max:1000'],
        ]);

        $student = Student::with('latestEnrollment')->findOrFail($data['student_id']);

        DB::transaction(function () use ($data, $student, $user) {
            $currentEnrollment = $student->latestEnrollment;

            // Mark active enrollment as completed or transferred
            if ($currentEnrollment && $currentEnrollment->status === 'active') {
                $currentEnrollment->update(['status' => 'completed']);
            }

            // Update student status to withdrawn
            $student->update(['status' => 'withdrawn']);

            // Create transition record
            StudentTransition::create([
                'branch_id'             => $data['branch_id'],
                'student_id'            => $student->id,
                'transition_type'       => 'withdrawal',
                'from_academic_year_id' => $currentEnrollment?->academic_year_id,
                'from_grade_id'         => $currentEnrollment?->grade_id,
                'from_section_id'       => $currentEnrollment?->section_id,
                'transition_date'       => $data['transition_date'],
                'reason'                => $data['reason'],
                'notes'                 => $data['notes'] ?? null,
                'processed_by'          => $user->id,
            ]);
        });

        return redirect()
            ->route('student-transitions.index', ['tab' => 'withdraw', 'branch_id' => $data['branch_id']])
            ->with('success', "Student {$student->full_name} ({$student->admission_number}) has been marked as withdrawn.");
    }

    public function readmit(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->canManageStudentTransitions(), 403, 'Unauthorized.');

        $data = $request->validate([
            'student_id'       => ['required', 'exists:students,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'grade_id'         => ['required', 'exists:grades,id'],
            'section_id'       => ['nullable', 'exists:sections,id'],
            'transition_date'  => ['required', 'date'],
            'notes'            => ['nullable', 'string', 'max:1000'],
        ]);

        $student = Student::findOrFail($data['student_id']);

        DB::transaction(function () use ($data, $student, $user) {
            $student->update(['status' => 'active']);

            StudentEnrollment::create([
                'student_id'       => $student->id,
                'academic_year_id' => $data['academic_year_id'],
                'grade_id'         => $data['grade_id'],
                'section_id'       => $data['section_id'] ?? null,
                'enrolled_at'      => $data['transition_date'],
                'status'           => 'active',
            ]);

            StudentTransition::create([
                'branch_id'           => $student->branch_id,
                'student_id'          => $student->id,
                'transition_type'     => 'readmission',
                'to_academic_year_id' => $data['academic_year_id'],
                'to_grade_id'         => $data['grade_id'],
                'to_section_id'       => $data['section_id'] ?? null,
                'transition_date'     => $data['transition_date'],
                'reason'              => 'Re-admission after withdrawal',
                'notes'               => $data['notes'] ?? null,
                'processed_by'        => $user->id,
            ]);
        });

        return redirect()
            ->route('student-transitions.index', ['tab' => 'withdraw', 'branch_id' => $student->branch_id])
            ->with('success', "Student {$student->full_name} has been successfully re-admitted.");
    }

    public function graduate(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->canManageStudentTransitions(), 403, 'Unauthorized.');

        $data = $request->validate([
            'branch_id'          => ['required', 'exists:branches,id'],
            'academic_year_id'   => ['required', 'exists:academic_years,id'],
            'grade_id'           => ['required', 'exists:grades,id'],
            'student_ids'        => ['required', 'array', 'min:1'],
            'student_ids.*'      => ['required', 'exists:students,id'],
            'transition_date'    => ['required', 'date'],
            'certificate_number' => ['nullable', 'string', 'max:100'],
            'notes'              => ['nullable', 'string', 'max:1000'],
        ]);

        $graduatedCount = 0;

        DB::transaction(function () use ($data, $user, &$graduatedCount) {
            foreach ($data['student_ids'] as $studentId) {
                $student = Student::with('latestEnrollment')->find($studentId);
                if (! $student) {
                    continue;
                }

                // Complete current enrollment
                if ($student->latestEnrollment && $student->latestEnrollment->status === 'active') {
                    $student->latestEnrollment->update(['status' => 'completed']);
                }

                // Mark student graduated
                $student->update(['status' => 'graduated']);

                StudentTransition::create([
                    'branch_id'             => $data['branch_id'],
                    'student_id'            => $student->id,
                    'transition_type'       => 'graduation',
                    'from_academic_year_id' => $data['academic_year_id'],
                    'from_grade_id'         => $data['grade_id'],
                    'transition_date'       => $data['transition_date'],
                    'certificate_number'    => $data['certificate_number'] ?? null,
                    'reason'                => 'Graduated from ' . ($student->latestEnrollment?->grade?->name ?? 'school'),
                    'notes'                 => $data['notes'] ?? null,
                    'processed_by'          => $user->id,
                ]);

                $graduatedCount++;
            }
        });

        return redirect()
            ->route('student-transitions.index', ['tab' => 'graduate', 'branch_id' => $data['branch_id']])
            ->with('success', "Successfully graduated {$graduatedCount} student(s).");
    }

    public function repeat(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->canManageStudentTransitions(), 403, 'Unauthorized.');

        $data = $request->validate([
            'branch_id'              => ['required', 'exists:branches,id'],
            'from_academic_year_id'  => ['required', 'exists:academic_years,id'],
            'to_academic_year_id'    => ['required', 'exists:academic_years,id', 'different:from_academic_year_id'],
            'grade_id'               => ['required', 'exists:grades,id'],
            'to_section_id'          => ['nullable', 'exists:sections,id'],
            'student_ids'            => ['required', 'array', 'min:1'],
            'student_ids.*'          => ['required', 'exists:students,id'],
            'transition_date'        => ['required', 'date'],
            'reason'                 => ['required', 'string', 'max:255'],
            'notes'                  => ['nullable', 'string', 'max:1000'],
        ]);

        $repeatedCount = 0;

        DB::transaction(function () use ($data, $user, &$repeatedCount) {
            foreach ($data['student_ids'] as $studentId) {
                $student = Student::with('latestEnrollment')->find($studentId);
                if (! $student) {
                    continue;
                }

                // Mark current enrollment as repeated
                $currentEnrollment = StudentEnrollment::where('student_id', $student->id)
                    ->where('academic_year_id', $data['from_academic_year_id'])
                    ->first();

                if ($currentEnrollment) {
                    $currentEnrollment->update(['status' => 'repeated']);
                }

                // Create new enrollment for same grade in the target academic year
                StudentEnrollment::updateOrCreate(
                    [
                        'student_id'       => $student->id,
                        'academic_year_id' => $data['to_academic_year_id'],
                    ],
                    [
                        'grade_id'    => $data['grade_id'],
                        'section_id'  => $data['to_section_id'] ?? null,
                        'enrolled_at' => $data['transition_date'],
                        'status'      => 'active',
                    ]
                );

                // Student status remains active
                $student->update(['status' => 'active']);

                StudentTransition::create([
                    'branch_id'             => $data['branch_id'],
                    'student_id'            => $student->id,
                    'transition_type'       => 'repeat',
                    'from_academic_year_id' => $data['from_academic_year_id'],
                    'to_academic_year_id'   => $data['to_academic_year_id'],
                    'from_grade_id'         => $data['grade_id'],
                    'to_grade_id'           => $data['grade_id'],
                    'to_section_id'         => $data['to_section_id'] ?? null,
                    'transition_date'       => $data['transition_date'],
                    'reason'                => $data['reason'],
                    'notes'                 => $data['notes'] ?? null,
                    'processed_by'          => $user->id,
                ]);

                $repeatedCount++;
            }
        });

        return redirect()
            ->route('student-transitions.index', ['tab' => 'repeat', 'branch_id' => $data['branch_id']])
            ->with('success', "Repeating status recorded for {$repeatedCount} student(s) in upcoming academic year.");
    }

    public function studentsByGrade(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->canManageStudentTransitions(), 403);

        $gradeId = $request->integer('grade_id');
        $academicYearId = $request->integer('academic_year_id');
        $branchId = $request->integer('branch_id');

        $studentsQuery = Student::query()
            ->with(['latestEnrollment.grade', 'latestEnrollment.section'])
            ->where('status', 'active');

        if ($branchId) {
            $studentsQuery->where('branch_id', $branchId);
        }

        if ($gradeId || $academicYearId) {
            $studentsQuery->whereHas('enrollments', function (Builder $q) use ($gradeId, $academicYearId) {
                if ($gradeId) {
                    $q->where('grade_id', $gradeId);
                }
                if ($academicYearId) {
                    $q->where('academic_year_id', $academicYearId);
                }
                $q->where('status', 'active');
            });
        }

        $students = $studentsQuery->orderBy('last_name')->orderBy('first_name')->get();

        return response()->json($students->map(fn ($s) => [
            'id'               => $s->id,
            'name'             => $s->full_name,
            'admission_number' => $s->admission_number,
            'grade'            => $s->latestEnrollment?->grade?->name,
            'section'          => $s->latestEnrollment?->section?->name,
        ]));
    }

    private function availableBranches(?int $branchId, bool $canViewAll): Collection
    {
        return Branch::query()
            ->when(! $canViewAll, fn (Builder $query) => $query->whereKey($branchId))
            ->orderBy('name')
            ->get();
    }
}
