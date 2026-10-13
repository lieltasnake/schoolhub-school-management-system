<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\SchoolHour;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SchoolHourController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', SchoolHour::class);

        $user = $request->user();
        $query = SchoolHour::query()->with('branch');

        if (! $user->hasAnyRole(['super-administrator', 'school-administrator'])) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where('shift_name', 'like', "%{$search}%");
        }

        return view('school-hours.index', [
            'schoolHours' => $query->orderBy('shift_name')->paginate(15)->withQueryString(),
            'branches' => Branch::query()
                ->when(! $user->hasAnyRole(['super-administrator', 'school-administrator']), fn (Builder $b) => $b->whereKey($user->branch_id))
                ->orderBy('name')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', SchoolHour::class);

        $user = $request->user();

        return view('school-hours.form', [
            'schoolHour' => null,
            'branches' => Branch::query()
                ->when(! $user->hasAnyRole(['super-administrator', 'school-administrator']), fn (Builder $b) => $b->whereKey($user->branch_id))
                ->orderBy('name')->get(),
            'availableDays' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', SchoolHour::class);

        $user = $request->user();
        $canManageAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);

        $validated = $request->validate([
            'branch_id' => [
                'required',
                'integer',
                'exists:branches,id',
                Rule::when(! $canManageAll, Rule::in([$user->branch_id])),
            ],
            'shift_name' => ['required', 'string', 'max:255'],
            'opening_time' => ['required', 'string'],
            'closing_time' => ['required', 'string'],
            'operating_days' => ['required', 'array', 'min:1'],
            'operating_days.*' => ['string', Rule::in(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        SchoolHour::create($validated);

        return redirect()->route('school-hours.index')->with('success', 'School hours shift created successfully.');
    }

    public function edit(Request $request, SchoolHour $schoolHour): View
    {
        $this->authorize('update', $schoolHour);

        $user = $request->user();

        return view('school-hours.form', [
            'schoolHour' => $schoolHour,
            'branches' => Branch::query()
                ->when(! $user->hasAnyRole(['super-administrator', 'school-administrator']), fn (Builder $b) => $b->whereKey($user->branch_id))
                ->orderBy('name')->get(),
            'availableDays' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
        ]);
    }

    public function update(Request $request, SchoolHour $schoolHour): RedirectResponse
    {
        $this->authorize('update', $schoolHour);

        $user = $request->user();
        $canManageAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);

        $validated = $request->validate([
            'branch_id' => [
                'required',
                'integer',
                'exists:branches,id',
                Rule::when(! $canManageAll, Rule::in([$user->branch_id])),
            ],
            'shift_name' => ['required', 'string', 'max:255'],
            'opening_time' => ['required', 'string'],
            'closing_time' => ['required', 'string'],
            'operating_days' => ['required', 'array', 'min:1'],
            'operating_days.*' => ['string', Rule::in(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $schoolHour->update($validated);

        return redirect()->route('school-hours.index')->with('success', 'School hours shift updated successfully.');
    }

    public function destroy(SchoolHour $schoolHour): RedirectResponse
    {
        $this->authorize('delete', $schoolHour);

        $schoolHour->delete();

        return redirect()->route('school-hours.index')->with('success', 'School hours shift deleted successfully.');
    }
}
