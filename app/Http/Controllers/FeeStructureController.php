<?php

namespace App\Http\Controllers;

use App\Http\Requests\FeeStructureRequest;
use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use App\Models\Grade;
use App\Models\Term;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class FeeStructureController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', FeeStructure::class);

        $user = $request->user();
        $query = FeeStructure::query()
            ->with(['branch', 'feeCategory', 'academicYear', 'grade', 'term']);

        $selectedBranchId = $user->branch_id;
        if (! $user->hasAnyRole(['super-administrator', 'school-administrator'])) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($request->filled('branch_id')) {
            $selectedBranchId = $request->integer('branch_id');
            $query->where('branch_id', $selectedBranchId);
        }

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->integer('academic_year_id'));
        }

        if ($request->filled('grade_id')) {
            $query->where('grade_id', $request->integer('grade_id'));
        }

        if ($request->filled('fee_category_id')) {
            $query->where('fee_category_id', $request->integer('fee_category_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $branches = $this->availableBranches(
            $user->branch_id,
            $user->hasAnyRole(['super-administrator', 'school-administrator']),
        );

        $contextBranchId = $selectedBranchId ?: $branches->first()?->id;

        return view('fee-structures.index', [
            'feeStructures' => $query->orderByDesc('id')->paginate(10)->withQueryString(),
            'branches' => $branches,
            'academicYears' => AcademicYear::query()
                ->when($contextBranchId, fn (Builder $q) => $q->where('branch_id', $contextBranchId))
                ->orderBy('name')
                ->get(),
            'grades' => Grade::query()
                ->when($contextBranchId, fn (Builder $q) => $q->where('branch_id', $contextBranchId))
                ->orderBy('level')
                ->get(),
            'feeCategories' => FeeCategory::query()
                ->when($contextBranchId, fn (Builder $q) => $q->where('branch_id', $contextBranchId))
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', FeeStructure::class);

        $user = $request->user();
        $branches = $this->availableBranches(
            $user->branch_id,
            $user->hasAnyRole(['super-administrator', 'school-administrator']),
        );

        $branchId = $request->integer('branch_id') ?: ($user->branch_id ?: $branches->first()?->id);

        return view('fee-structures.form', [
            'feeStructure' => null,
            'branches' => $branches,
            'selectedBranchId' => $branchId,
            'feeCategories' => FeeCategory::query()
                ->when($branchId, fn (Builder $q) => $q->where('branch_id', $branchId))
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
            'academicYears' => AcademicYear::query()
                ->when($branchId, fn (Builder $q) => $q->where('branch_id', $branchId))
                ->orderBy('name')
                ->get(),
            'grades' => Grade::query()
                ->when($branchId, fn (Builder $q) => $q->where('branch_id', $branchId))
                ->where('status', 'active')
                ->orderBy('level')
                ->get(),
            'terms' => Term::query()
                ->whereHas('academicYear', fn (Builder $q) => $q->when($branchId, fn (Builder $sub) => $sub->where('branch_id', $branchId)))
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(FeeStructureRequest $request): RedirectResponse
    {
        $feeStructure = FeeStructure::create($request->validated());

        return redirect()->route('fee-structures.show', $feeStructure)
            ->with('success', 'Fee structure created successfully.');
    }

    public function show(FeeStructure $feeStructure): View
    {
        $this->authorize('view', $feeStructure);

        $feeStructure->load(['branch', 'feeCategory', 'academicYear', 'grade', 'term']);

        return view('fee-structures.show', compact('feeStructure'));
    }

    public function edit(FeeStructure $feeStructure): View
    {
        $this->authorize('update', $feeStructure);

        $feeStructure->load(['branch', 'feeCategory', 'academicYear', 'grade', 'term']);
        $branchId = $feeStructure->branch_id;

        return view('fee-structures.form', [
            'feeStructure' => $feeStructure,
            'branches' => collect([$feeStructure->branch]),
            'selectedBranchId' => $branchId,
            'feeCategories' => FeeCategory::query()
                ->where('branch_id', $branchId)
                ->orderBy('name')
                ->get(),
            'academicYears' => AcademicYear::query()
                ->where('branch_id', $branchId)
                ->orderBy('name')
                ->get(),
            'grades' => Grade::query()
                ->where('branch_id', $branchId)
                ->orderBy('level')
                ->get(),
            'terms' => Term::query()
                ->where('academic_year_id', $feeStructure->academic_year_id)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(FeeStructureRequest $request, FeeStructure $feeStructure): RedirectResponse
    {
        $this->authorize('update', $feeStructure);

        $feeStructure->update($request->validated());

        return redirect()->route('fee-structures.show', $feeStructure)
            ->with('success', 'Fee structure updated successfully.');
    }

    public function destroy(FeeStructure $feeStructure): RedirectResponse
    {
        $this->authorize('delete', $feeStructure);

        $feeStructure->delete();

        return redirect()->route('fee-structures.index')
            ->with('success', 'Fee structure deleted successfully.');
    }

    private function availableBranches(?int $branchId, bool $canViewAll): Collection
    {
        return Branch::query()
            ->when(! $canViewAll, fn (Builder $query) => $query->whereKey($branchId))
            ->orderBy('name')
            ->get();
    }
}
