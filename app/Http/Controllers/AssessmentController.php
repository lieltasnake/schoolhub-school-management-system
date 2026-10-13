<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\AssessmentType;
use App\Models\Branch;
use App\Models\Grade;
use App\Models\GradeScale;
use App\Models\Section;
use App\Models\Term;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AssessmentController extends Controller
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

        if ($request->filled('term_id')) {
            $query->where('term_id', $request->integer('term_id'));
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

        $academicYears = AcademicYear::query()
            ->when($selectedBranchId, fn (Builder $q) => $q->where('branch_id', $selectedBranchId))
            ->orderByDesc('start_date')
            ->get();

        $terms = Term::query()
            ->when($request->filled('academic_year_id'), fn (Builder $q) => $q->where('academic_year_id', $request->integer('academic_year_id')))
            ->orderBy('start_date')
            ->get();

        $baseCountQuery = Assessment::query()
            ->when(! $canManageAll, fn ($q) => $q->where('branch_id', $user->branch_id))
            ->when($canManageAll && $selectedBranchId, fn ($q) => $q->where('branch_id', $selectedBranchId));

        $totalAssessments = (clone $baseCountQuery)->count();
        $completedAssessments = (clone $baseCountQuery)->where('status', 'completed')->count();
        $scheduledAssessments = (clone $baseCountQuery)->where('status', 'scheduled')->count();

        $assessments = $query->orderByDesc('assessment_date')->orderByDesc('id')->paginate(15)->withQueryString();

        return view('assessments.index', [
            'assessments' => $assessments,
            'branches' => $branches,
            'grades' => $grades,
            'academicYears' => $academicYears,
            'terms' => $terms,
            'selectedBranchId' => $selectedBranchId,
            'totalAssessments' => $totalAssessments,
            'completedAssessments' => $completedAssessments,
            'scheduledAssessments' => $scheduledAssessments,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Assessment::class);

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

        $terms = Term::query()
            ->whereIn('academic_year_id', $academicYears->pluck('id'))
            ->orderBy('start_date')
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

        $assessmentTypes = AssessmentType::query()
            ->when($selectedBranchId, fn (Builder $q) => $q->where('branch_id', $selectedBranchId))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $gradeScales = GradeScale::query()
            ->when($selectedBranchId, fn (Builder $q) => $q->where('branch_id', $selectedBranchId))
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('assessments.form', [
            'assessment' => null,
            'branches' => $branches,
            'selectedBranchId' => $selectedBranchId,
            'academicYears' => $academicYears,
            'terms' => $terms,
            'grades' => $grades,
            'sections' => $sections,
            'assessmentTypes' => $assessmentTypes,
            'gradeScales' => $gradeScales,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Assessment::class);

        $data = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'term_id' => ['nullable', 'exists:terms,id'],
            'grade_id' => ['required', 'exists:grades,id'],
            'section_id' => ['nullable', 'exists:sections,id'],
            'assessment_type_id' => ['required', 'exists:assessment_types,id'],
            'grade_scale_id' => ['nullable', 'exists:grade_scales,id'],
            'title' => ['required', 'string', 'max:150'],
            'subject_name' => ['required', 'string', 'max:100'],
            'max_score' => ['required', 'numeric', 'min:1', 'max:1000'],
            'pass_mark' => ['required', 'numeric', 'min:0', 'lte:max_score'],
            'weight_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'assessment_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:draft,scheduled,completed,cancelled'],
        ]);

        $assessment = Assessment::create($data);

        return redirect()->route('assessments.show', $assessment)
            ->with('success', "Assessment '{$assessment->title}' created successfully.");
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

        $gradesCount = $assessment->grades->count();
        $averageScore = $gradesCount > 0 ? $assessment->grades->whereNotNull('score')->avg('score') : 0;
        $highestScore = $gradesCount > 0 ? $assessment->grades->whereNotNull('score')->max('score') : 0;
        $lowestScore = $gradesCount > 0 ? $assessment->grades->whereNotNull('score')->min('score') : 0;
        $passedCount = $gradesCount > 0 ? $assessment->grades->filter(fn ($g) => $g->score >= $assessment->pass_mark)->count() : 0;
        $passRate = $gradesCount > 0 ? round(($passedCount / $gradesCount) * 100, 1) : 0;

        return view('assessments.show', [
            'assessment' => $assessment,
            'gradesCount' => $gradesCount,
            'averageScore' => round($averageScore, 2),
            'highestScore' => $highestScore,
            'lowestScore' => $lowestScore,
            'passRate' => $passRate,
        ]);
    }

    public function edit(Assessment $assessment): View
    {
        $this->authorize('update', $assessment);

        $assessment->load(['branch', 'academicYear', 'term', 'grade', 'section', 'assessmentType', 'gradeScale']);

        $branches = collect([$assessment->branch]);
        $academicYears = AcademicYear::where('branch_id', $assessment->branch_id)->orderByDesc('start_date')->get();
        $terms = Term::where('academic_year_id', $assessment->academic_year_id)->orderBy('start_date')->get();
        $grades = Grade::where('branch_id', $assessment->branch_id)->orderBy('level')->orderBy('name')->get();
        $sections = Section::where('grade_id', $assessment->grade_id)->orderBy('name')->get();
        $assessmentTypes = AssessmentType::where('branch_id', $assessment->branch_id)->where('is_active', true)->get();
        $gradeScales = GradeScale::where('branch_id', $assessment->branch_id)->where('status', 'active')->get();

        return view('assessments.form', [
            'assessment' => $assessment,
            'branches' => $branches,
            'selectedBranchId' => $assessment->branch_id,
            'academicYears' => $academicYears,
            'terms' => $terms,
            'grades' => $grades,
            'sections' => $sections,
            'assessmentTypes' => $assessmentTypes,
            'gradeScales' => $gradeScales,
        ]);
    }

    public function update(Request $request, Assessment $assessment): RedirectResponse
    {
        $this->authorize('update', $assessment);

        $data = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'term_id' => ['nullable', 'exists:terms,id'],
            'grade_id' => ['required', 'exists:grades,id'],
            'section_id' => ['nullable', 'exists:sections,id'],
            'assessment_type_id' => ['required', 'exists:assessment_types,id'],
            'grade_scale_id' => ['nullable', 'exists:grade_scales,id'],
            'title' => ['required', 'string', 'max:150'],
            'subject_name' => ['required', 'string', 'max:100'],
            'max_score' => ['required', 'numeric', 'min:1', 'max:1000'],
            'pass_mark' => ['required', 'numeric', 'min:0', 'lte:max_score'],
            'weight_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'assessment_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:draft,scheduled,completed,cancelled'],
        ]);

        $assessment->update($data);

        return redirect()->route('assessments.show', $assessment)
            ->with('success', "Assessment '{$assessment->title}' updated successfully.");
    }

    public function destroy(Assessment $assessment): RedirectResponse
    {
        $this->authorize('delete', $assessment);

        $assessment->delete();

        return redirect()->route('assessments.index')
            ->with('success', 'Assessment removed successfully.');
    }

    private function availableBranches(?int $branchId, bool $canViewAll): Collection
    {
        return Branch::query()
            ->when(! $canViewAll, fn (Builder $query) => $query->whereKey($branchId))
            ->orderBy('name')
            ->get();
    }
}
