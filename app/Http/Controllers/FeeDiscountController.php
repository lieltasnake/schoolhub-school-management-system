<?php

namespace App\Http\Controllers;

use App\Http\Requests\FeeDiscountRequest;
use App\Models\Branch;
use App\Models\FeeDiscount;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class FeeDiscountController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', FeeDiscount::class);

        $user = $request->user();
        $query = FeeDiscount::query()->with('branch');

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

        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->toString());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        return view('fee-discounts.index', [
            'feeDiscounts' => $query->orderBy('name')->paginate(10)->withQueryString(),
            'branches' => $this->availableBranches(
                $user->branch_id,
                $user->hasAnyRole(['super-administrator', 'school-administrator']),
            ),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', FeeDiscount::class);

        return view('fee-discounts.form', [
            'feeDiscount' => null,
            'branches' => $this->availableBranches(
                $request->user()->branch_id,
                $request->user()->hasAnyRole(['super-administrator', 'school-administrator']),
            ),
        ]);
    }

    public function store(FeeDiscountRequest $request): RedirectResponse
    {
        FeeDiscount::create($request->validated());

        return redirect()->route('fee-discounts.index')
            ->with('success', 'Fee discount created successfully.');
    }

    public function edit(FeeDiscount $feeDiscount): View
    {
        $this->authorize('update', $feeDiscount);

        return view('fee-discounts.form', [
            'feeDiscount' => $feeDiscount,
            'branches' => collect([$feeDiscount->branch]),
        ]);
    }

    public function update(FeeDiscountRequest $request, FeeDiscount $feeDiscount): RedirectResponse
    {
        $this->authorize('update', $feeDiscount);
        $feeDiscount->update($request->validated());

        return redirect()->route('fee-discounts.index')
            ->with('success', 'Fee discount updated successfully.');
    }

    public function destroy(FeeDiscount $feeDiscount): RedirectResponse
    {
        $this->authorize('delete', $feeDiscount);

        $feeDiscount->delete();

        return redirect()->route('fee-discounts.index')
            ->with('success', 'Fee discount deleted successfully.');
    }

    private function availableBranches(?int $branchId, bool $canViewAll): Collection
    {
        return Branch::query()
            ->when(! $canViewAll, fn (Builder $query) => $query->whereKey($branchId))
            ->orderBy('name')
            ->get();
    }
}
