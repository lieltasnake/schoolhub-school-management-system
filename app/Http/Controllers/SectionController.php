<?php

namespace App\Http\Controllers;

use App\Http\Requests\SectionRequest;
use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Grade;
use App\Models\Section;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SectionController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Section::class);

        $user = $request->user();
        $query = Section::query()->with(['grade.branch', 'academicYear']);

        if (! $user->hasAnyRole(['super-administrator', 'school-administrator'])) {
            $query->whereHas('grade', fn (Builder $builder) => $builder->where('branch_id', $user->branch_id));
        } elseif ($request->filled('branch_id')) {
            $query->whereHas('grade', fn (Builder $builder) => $builder->where('branch_id', $request->integer('branch_id')));
        }

        if ($request->filled('grade_id')) {
            $query->where('grade_id', $request->integer('grade_id'));
        }

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->integer('academic_year_id'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('room_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        return view('sections.index', [
            'sections' => $query->orderBy('name')->paginate(10)->withQueryString(),
            'branches' => Branch::query()
                ->when(! $user->hasAnyRole(['super-administrator', 'school-administrator']), fn (Builder $b) => $b->whereKey($user->branch_id))
                ->orderBy('name')->get(),
            'grades' => Grade::query()
                ->with('branch')
                ->when(! $user->hasAnyRole(['super-administrator', 'school-administrator']), fn (Builder $b) => $b->where('branch_id', $user->branch_id))
                ->orderBy('level')->orderBy('name')->get(),
            'academicYears' => AcademicYear::query()
                ->with('branch')
                ->when(! $user->hasAnyRole(['super-administrator', 'school-administrator']), fn (Builder $b) => $b->where('branch_id', $user->branch_id))
                ->orderByDesc('start_date')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Section::class);

        $user = $request->user();
        $selectedGradeId = $request->integer('grade_id');
        $selectedGrade = $selectedGradeId ? Grade::find($selectedGradeId) : null;
        $branchId = $selectedGrade ? $selectedGrade->branch_id : $user->branch_id;

        return view('sections.form', [
            'section' => null,
            'grades' => $this->availableGrades($user->branch_id, $user->hasAnyRole(['super-administrator', 'school-administrator'])),
            'academicYears' => $this->availableAcademicYears($branchId, $user->hasAnyRole(['super-administrator', 'school-administrator'])),
            'selectedGradeId' => $selectedGradeId,
        ]);
    }

    public function store(SectionRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $section = Section::create($validated);

        return redirect()->route('sections.show', $section)
            ->with('success', 'Section created successfully.');
    }

    public function show(Section $section): View
    {
        $this->authorize('view', $section);
        $section->load(['grade.branch', 'academicYear']);

        return view('sections.show', compact('section'));
    }

    public function edit(Section $section): View
    {
        $this->authorize('update', $section);
        $section->load('grade.branch');

        $user = auth()->user();

        return view('sections.form', [
            'section' => $section,
            'grades' => collect([$section->grade]),
            'academicYears' => $this->availableAcademicYears($section->grade->branch_id, true),
            'selectedGradeId' => $section->grade_id,
        ]);
    }

    public function update(SectionRequest $request, Section $section): RedirectResponse
    {
        $this->authorize('update', $section);
        $section->update($request->validated());

        return redirect()->route('sections.show', $section)
            ->with('success', 'Section updated successfully.');
    }

    public function destroy(Section $section): RedirectResponse
    {
        $this->authorize('delete', $section);
        $grade = $section->grade;
        $section->delete();

        return redirect()->route('grades.show', $grade)
            ->with('success', 'Section deleted successfully.');
    }

    private function availableGrades(?int $branchId, bool $canViewAll): Collection
    {
        return Grade::query()
            ->with('branch')
            ->when(! $canViewAll, fn (Builder $query) => $query->where('branch_id', $branchId))
            ->orderBy('level')
            ->orderBy('name')
            ->get();
    }

    private function availableAcademicYears(?int $branchId, bool $canViewAll): Collection
    {
        return AcademicYear::query()
            ->with('branch')
            ->when(! $canViewAll && $branchId, fn (Builder $query) => $query->where('branch_id', $branchId))
            ->orderByDesc('start_date')
            ->get();
    }
}
