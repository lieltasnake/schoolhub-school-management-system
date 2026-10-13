<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Period;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PeriodController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Period::class);

        $user = $request->user();
        $query = Period::query()->with('branch');

        if (! $user->hasAnyRole(['super-administrator', 'school-administrator'])) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(fn (Builder $b) => $b->where('name', 'like', "%{$search}%")->orWhere('period_number', 'like', "%{$search}%"));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        return view('periods.index', [
            'periods' => $query->orderBy('period_number')->paginate(15)->withQueryString(),
            'branches' => Branch::query()
                ->when(! $user->hasAnyRole(['super-administrator', 'school-administrator']), fn (Builder $b) => $b->whereKey($user->branch_id))
                ->orderBy('name')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Period::class);

        $user = $request->user();

        return view('periods.form', [
            'period' => null,
            'branches' => Branch::query()
                ->when(! $user->hasAnyRole(['super-administrator', 'school-administrator']), fn (Builder $b) => $b->whereKey($user->branch_id))
                ->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Period::class);

        $user = $request->user();
        $canManageAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);

        $validated = $request->validate([
            'branch_id' => [
                'required',
                'integer',
                'exists:branches,id',
                Rule::when(! $canManageAll, Rule::in([$user->branch_id])),
            ],
            'period_number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('periods', 'period_number')->where('branch_id', $request->input('branch_id')),
            ],
            'name' => ['required', 'string', 'max:255'],
            'start_time' => ['required', 'string'],
            'end_time' => ['required', 'string'],
            'is_break' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $validated['is_break'] = $request->boolean('is_break');

        Period::create($validated);

        return redirect()->route('periods.index')->with('success', 'Period created successfully.');
    }

    public function edit(Request $request, Period $period): View
    {
        $this->authorize('update', $period);

        $user = $request->user();

        return view('periods.form', [
            'period' => $period,
            'branches' => Branch::query()
                ->when(! $user->hasAnyRole(['super-administrator', 'school-administrator']), fn (Builder $b) => $b->whereKey($user->branch_id))
                ->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Period $period): RedirectResponse
    {
        $this->authorize('update', $period);

        $user = $request->user();
        $canManageAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);

        $validated = $request->validate([
            'branch_id' => [
                'required',
                'integer',
                'exists:branches,id',
                Rule::when(! $canManageAll, Rule::in([$user->branch_id])),
            ],
            'period_number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('periods', 'period_number')
                    ->where('branch_id', $request->input('branch_id'))
                    ->ignore($period->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'start_time' => ['required', 'string'],
            'end_time' => ['required', 'string'],
            'is_break' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $validated['is_break'] = $request->boolean('is_break');

        $period->update($validated);

        return redirect()->route('periods.index')->with('success', 'Period updated successfully.');
    }

    public function destroy(Period $period): RedirectResponse
    {
        $this->authorize('delete', $period);

        if ($period->timetables()->exists()) {
            return back()->with('error', 'Cannot delete period because it is assigned to timetable entries.');
        }

        $period->delete();

        return redirect()->route('periods.index')->with('success', 'Period deleted successfully.');
    }
}
