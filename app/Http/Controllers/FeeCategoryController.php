<?php

namespace App\Http\Controllers;

use App\Http\Requests\FeeCategoryRequest;
use App\Models\Branch;
use App\Models\FeeCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class FeeCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', FeeCategory::class);

        $user = $request->user();
        $query = FeeCategory::query()->with('branch');

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

        return view('fee-categories.index', [
            'feeCategories' => $query->orderBy('name')->paginate(10)->withQueryString(),
            'branches' => $this->availableBranches(
                $user->branch_id,
                $user->hasAnyRole(['super-administrator', 'school-administrator']),
            ),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', FeeCategory::class);

        return view('fee-categories.form', [
            'feeCategory' => null,
            'branches' => $this->availableBranches(
                $request->user()->branch_id,
                $request->user()->hasAnyRole(['super-administrator', 'school-administrator']),
            ),
        ]);
    }

    public function store(FeeCategoryRequest $request): RedirectResponse
    {
        FeeCategory::create($request->validated());

        return redirect()->route('fee-categories.index')
            ->with('success', 'Fee category created successfully.');
    }

    public function edit(FeeCategory $feeCategory): View
    {
        $this->authorize('update', $feeCategory);

        return view('fee-categories.form', [
            'feeCategory' => $feeCategory,
            'branches' => collect([$feeCategory->branch]),
        ]);
    }

    public function update(FeeCategoryRequest $request, FeeCategory $feeCategory): RedirectResponse
    {
        $this->authorize('update', $feeCategory);
        $feeCategory->update($request->validated());

        return redirect()->route('fee-categories.index')
            ->with('success', 'Fee category updated successfully.');
    }

    public function destroy(FeeCategory $feeCategory): RedirectResponse
    {
        $this->authorize('delete', $feeCategory);

        $feeCategory->delete();

        return redirect()->route('fee-categories.index')
            ->with('success', 'Fee category deleted successfully.');
    }

    private function availableBranches(?int $branchId, bool $canViewAll): Collection
    {
        return Branch::query()
            ->when(! $canViewAll, fn (Builder $query) => $query->whereKey($branchId))
            ->orderBy('name')
            ->get();
    }
}
