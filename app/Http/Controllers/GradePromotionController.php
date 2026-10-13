<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Grade;
use App\Models\GradePromotion;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GradePromotionController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', GradePromotion::class);

        $user = $request->user();
        $canManageAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);

        $query = GradePromotion::query()
            ->with(['branch', 'fromAcademicYear', 'toAcademicYear', 'fromGrade', 'toGrade', 'processor']);

        if (! $canManageAll) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        $branches = $this->availableBranches($user->branch_id, $canManageAll);
        $selectedBranchId = $canManageAll
            ? ($request->filled('branch_id') ? $request->integer('branch_id') : null)
            : $user->branch_id;

        $promotions = $query->orderByDesc('id')->paginate(15)->withQueryString();

        return view('grade-promotions.index', [
            'promotions' => $promotions,
            'branches' => $branches,
            'selectedBranchId' => $selectedBranchId,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', GradePromotion::class);

        $user = $request->user();
        $canManageAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);
        $branches = $this->availableBranches($user->branch_id, $canManageAll);

        $selectedBranchId = $canManageAll
            ? ($request->filled('branch_id') ? $request->integer('branch_id') : $branches->first()?->id)
            : $user->branch_id;

        $academicYears = AcademicYear::query()
            ->when($selectedBranchId, fn (Builder $q) => $q->where('branch_id', $selectedBranchId))
            ->orderByDesc('start_date')
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

        // If source criteria are selected, fetch enrolled students
        $students = collect();
        if ($request->filled('source_academic_year_id') && $request->filled('source_grade_id')) {
            $studentsQuery = StudentEnrollment::query()
                ->with(['student'])
                ->where('academic_year_id', $request->integer('source_academic_year_id'))
                ->where('grade_id', $request->integer('source_grade_id'))
                ->where('status', 'active');

            if ($request->filled('source_section_id')) {
                $studentsQuery->where('section_id', $request->integer('source_section_id'));
            }

            $students = $studentsQuery->get();
        }

        return view('grade-promotions.create', [
            'branches' => $branches,
            'selectedBranchId' => $selectedBranchId,
            'academicYears' => $academicYears,
            'grades' => $grades,
            'sections' => $sections,
            'students' => $students,
            'sourceAcademicYearId' => $request->integer('source_academic_year_id'),
            'sourceGradeId' => $request->integer('source_grade_id'),
            'sourceSectionId' => $request->integer('source_section_id'),
            'targetAcademicYearId' => $request->integer('target_academic_year_id'),
            'targetGradeId' => $request->integer('target_grade_id'),
            'targetSectionId' => $request->integer('target_section_id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', GradePromotion::class);

        $data = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'from_academic_year_id' => ['required', 'exists:academic_years,id'],
            'to_academic_year_id' => ['required', 'exists:academic_years,id', 'different:from_academic_year_id'],
            'from_grade_id' => ['required', 'exists:grades,id'],
            'to_grade_id' => ['required', 'exists:grades,id'],
            'to_section_id' => ['nullable', 'exists:sections,id'],
            'students' => ['required', 'array', 'min:1'],
            'students.*.student_id' => ['required', 'exists:students,id'],
            'students.*.enrollment_id' => ['required', 'exists:student_enrollments,id'],
            'students.*.action' => ['required', 'in:promote,repeat,graduate,leave'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $promotedCount = 0;
        $repeatedCount = 0;
        $graduatedCount = 0;

        DB::transaction(function () use ($data, $request, &$promotedCount, &$repeatedCount, &$graduatedCount) {
            foreach ($data['students'] as $item) {
                $enrollment = StudentEnrollment::find($item['enrollment_id']);
                $student = Student::find($item['student_id']);

                if (! $enrollment || ! $student) {
                    continue;
                }

                if ($item['action'] === 'promote') {
                    $enrollment->update(['status' => 'promoted']);

                    // Create new enrollment in target year & grade
                    StudentEnrollment::updateOrCreate(
                        [
                            'student_id' => $student->id,
                            'academic_year_id' => $data['to_academic_year_id'],
                        ],
                        [
                            'grade_id' => $data['to_grade_id'],
                            'section_id' => $data['to_section_id'] ?? null,
                            'enrolled_at' => now()->toDateString(),
                            'status' => 'active',
                        ]
                    );
                    $promotedCount++;
                } elseif ($item['action'] === 'repeat') {
                    $enrollment->update(['status' => 'repeated']);

                    // Repeat in same grade in the new academic year
                    StudentEnrollment::updateOrCreate(
                        [
                            'student_id' => $student->id,
                            'academic_year_id' => $data['to_academic_year_id'],
                        ],
                        [
                            'grade_id' => $data['from_grade_id'],
                            'section_id' => $data['to_section_id'] ?? null,
                            'enrolled_at' => now()->toDateString(),
                            'status' => 'active',
                        ]
                    );
                    $repeatedCount++;
                } elseif ($item['action'] === 'graduate') {
                    $enrollment->update(['status' => 'completed']);
                    $student->update(['status' => 'graduated']);
                    $graduatedCount++;
                }
            }

            GradePromotion::create([
                'branch_id' => $data['branch_id'],
                'from_academic_year_id' => $data['from_academic_year_id'],
                'to_academic_year_id' => $data['to_academic_year_id'],
                'from_grade_id' => $data['from_grade_id'],
                'to_grade_id' => $data['to_grade_id'],
                'processed_by' => $request->user()->id,
                'total_students' => count($data['students']),
                'promoted_count' => $promotedCount,
                'repeated_count' => $repeatedCount,
                'graduated_count' => $graduatedCount,
                'notes' => $data['notes'] ?? null,
            ]);
        });

        return redirect()->route('grade-promotions.index')
            ->with('success', "Grade promotion completed: {$promotedCount} promoted, {$repeatedCount} repeated, {$graduatedCount} graduated.");
    }

    private function availableBranches(?int $branchId, bool $canViewAll): Collection
    {
        return Branch::query()
            ->when(! $canViewAll, fn (Builder $query) => $query->whereKey($branchId))
            ->orderBy('name')
            ->get();
    }
}
