<?php

namespace App\Http\Controllers;

use App\Http\Requests\GradeRequest;
use App\Models\Branch;
use App\Models\Grade;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GradeController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Grade::class);

        $user = $request->user();
        $query = Grade::query()->with('branch')->withCount('sections');

        if (! $user->hasAnyRole(['super-administrator', 'school-administrator'])) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        return view('grades.index', [
            'grades' => $query->orderBy('level')->orderBy('name')->paginate(10)->withQueryString(),
            'branches' => $this->availableBranches(
                $user->branch_id,
                $user->hasAnyRole(['super-administrator', 'school-administrator']),
            ),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Grade::class);

        return view('grades.form', [
            'grade' => null,
            'branches' => $this->availableBranches(
                $request->user()->branch_id,
                $request->user()->hasAnyRole(['super-administrator', 'school-administrator']),
            ),
        ]);
    }

    public function store(GradeRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $grade = Grade::create($validated);

        return redirect()->route('grades.show', $grade)
            ->with('success', 'Grade created successfully.');
    }

    public function show(Grade $grade): View
    {
        $this->authorize('view', $grade);
        $grade->load(['branch', 'sections.academicYear']);

        return view('grades.show', compact('grade'));
    }

    public function edit(Grade $grade): View
    {
        $this->authorize('update', $grade);

        return view('grades.form', [
            'grade' => $grade,
            'branches' => collect([$grade->branch]),
        ]);
    }

    public function update(GradeRequest $request, Grade $grade): RedirectResponse
    {
        $this->authorize('update', $grade);
        $grade->update($request->validated());

        return redirect()->route('grades.show', $grade)
            ->with('success', 'Grade updated successfully.');
    }

    public function destroy(Grade $grade): RedirectResponse
    {
        $this->authorize('delete', $grade);

        if ($grade->sections()->exists()) {
            return redirect()->route('grades.show', $grade)
                ->with('error', 'Cannot delete this grade while it has sections assigned to it.');
        }

        $grade->delete();

        return redirect()->route('grades.index')
            ->with('success', 'Grade deleted successfully.');
    }

    private function availableBranches(?int $branchId, bool $canViewAll): Collection
    {
        return Branch::query()
            ->when(! $canViewAll, fn (Builder $query) => $query->whereKey($branchId))
            ->orderBy('name')
            ->get();
    }
}
