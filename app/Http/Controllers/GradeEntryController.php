<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\Branch;
use App\Models\Grade;
use App\Models\StudentEnrollment;
use App\Models\StudentGrade;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GradeEntryController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Assessment::class);

        $user = $request->user();
        $canManageAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);

        $query = Assessment::query()
            ->with(['branch', 'academicYear', 'term', 'grade', 'section', 'assessmentType', 'gradeScale'])
            ->withCount('grades');

        if (! $canManageAll) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        if ($request->filled('search')) {
            $query->search($request->string('search')->toString());
        }

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->integer('academic_year_id'));
        }

        if ($request->filled('grade_id')) {
            $query->where('grade_id', $request->integer('grade_id'));
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

        $academicYears = AcademicYear::query()
            ->when($selectedBranchId, fn (Builder $q) => $q->where('branch_id', $selectedBranchId))
            ->orderByDesc('start_date')
            ->get();

        $assessments = $query->orderByDesc('assessment_date')->orderByDesc('id')->paginate(15)->withQueryString();

        return view('grade-entries.index', [
            'assessments' => $assessments,
            'branches' => $branches,
            'grades' => $grades,
            'academicYears' => $academicYears,
            'selectedBranchId' => $selectedBranchId,
        ]);
    }

    public function edit(Assessment $assessment): View
    {
        $this->authorize('enterGrades', $assessment);

        $assessment->load(['branch', 'academicYear', 'term', 'grade', 'section', 'gradeScale.items', 'grades']);

        // Fetch enrolled students
        $enrollmentQuery = StudentEnrollment::query()
            ->with('student')
            ->where('academic_year_id', $assessment->academic_year_id)
            ->where('grade_id', $assessment->grade_id)
            ->where('status', 'active');

        if ($assessment->section_id) {
            $enrollmentQuery->where('section_id', $assessment->section_id);
        }

        $enrollments = $enrollmentQuery->get()->sortBy(fn ($e) => $e->student->last_name.' '.$e->student->first_name);

        $existingGrades = $assessment->grades->keyBy('student_id');

        return view('grade-entries.edit', [
            'assessment' => $assessment,
            'enrollments' => $enrollments,
            'existingGrades' => $existingGrades,
        ]);
    }

    public function update(Request $request, Assessment $assessment): RedirectResponse
    {
        $this->authorize('enterGrades', $assessment);

        $data = $request->validate([
            'grades' => ['required', 'array'],
            'grades.*.student_id' => ['required', 'exists:students,id'],
            'grades.*.score' => ['nullable', 'numeric', 'min:0', "max:{$assessment->max_score}"],
            'grades.*.status' => ['required', 'in:present,absent,exempt'],
            'grades.*.remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $scale = $assessment->gradeScale;
        $allEntered = true;

        DB::transaction(function () use ($assessment, $data, $scale, &$allEntered) {
            foreach ($data['grades'] as $item) {
                $score = $item['score'] !== null && $item['score'] !== '' ? (float) $item['score'] : null;
                $status = $item['status'];
                $percentage = null;
                $letterGrade = null;
                $gradePoint = null;

                if ($status === 'present' && $score !== null) {
                    $percentage = round(($score / $assessment->max_score) * 100, 2);

                    if ($scale) {
                        $tier = $scale->calculateGrade($percentage);
                        if ($tier) {
                            $letterGrade = $tier->letter_grade;
                            $gradePoint = $tier->grade_point;
                        }
                    }
                } elseif ($status === 'present' && $score === null) {
                    $allEntered = false;
                }

                StudentGrade::updateOrCreate(
                    [
                        'assessment_id' => $assessment->id,
                        'student_id' => $item['student_id'],
                    ],
                    [
                        'score' => $score,
                        'percentage' => $percentage,
                        'letter_grade' => $letterGrade,
                        'grade_point' => $gradePoint,
                        'status' => $status,
                        'remarks' => $item['remarks'] ?? null,
                    ]
                );
            }

            if ($allEntered && $assessment->status === 'scheduled') {
                $assessment->update(['status' => 'completed']);
            }
        });

        return redirect()->route('grade-entries.show', $assessment)
            ->with('success', 'Student grades saved successfully.');
    }

    public function show(Assessment $assessment): View
    {
        $this->authorize('view', $assessment);

        $assessment->load([
            'branch',
            'academicYear',
            'term',
            'grade',
            'section',
            'assessmentType',
            'gradeScale.items',
            'grades.student',
        ]);

        $grades = $assessment->grades->sortByDesc('score');
        $gradesCount = $grades->count();
        $presentCount = $grades->where('status', 'present')->count();
        $absentCount = $grades->where('status', 'absent')->count();
        $averageScore = $presentCount > 0 ? $grades->where('status', 'present')->whereNotNull('score')->avg('score') : 0;
        $highestScore = $presentCount > 0 ? $grades->where('status', 'present')->whereNotNull('score')->max('score') : 0;
        $lowestScore = $presentCount > 0 ? $grades->where('status', 'present')->whereNotNull('score')->min('score') : 0;
        $passedCount = $presentCount > 0 ? $grades->where('status', 'present')->filter(fn ($g) => $g->score >= $assessment->pass_mark)->count() : 0;
        $passRate = $presentCount > 0 ? round(($passedCount / $presentCount) * 100, 1) : 0;

        return view('grade-entries.show', [
            'assessment' => $assessment,
            'grades' => $grades,
            'gradesCount' => $gradesCount,
            'presentCount' => $presentCount,
            'absentCount' => $absentCount,
            'averageScore' => round($averageScore, 2),
            'highestScore' => $highestScore,
            'lowestScore' => $lowestScore,
            'passRate' => $passRate,
        ]);
    }

    private function availableBranches(?int $branchId, bool $canViewAll): Collection
    {
        return Branch::query()
            ->when(! $canViewAll, fn (Builder $query) => $query->whereKey($branchId))
            ->orderBy('name')
            ->get();
    }
}
