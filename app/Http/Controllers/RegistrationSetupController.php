<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\RegistrationSetup;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class RegistrationSetupController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', RegistrationSetup::class);

        $user = $request->user();
        $canManageAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);

        $query = RegistrationSetup::query()
            ->with(['branch', 'academicYear'])
            ->withCount('registrations');

        if (! $canManageAll) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        $branches = $this->availableBranches($user->branch_id, $canManageAll);
        $selectedBranchId = $canManageAll
            ? ($request->filled('branch_id') ? $request->integer('branch_id') : null)
            : $user->branch_id;

        $setups = $query->orderByDesc('start_date')->paginate(15)->withQueryString();

        return view('registration-setups.index', [
            'setups' => $setups,
            'branches' => $branches,
            'selectedBranchId' => $selectedBranchId,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', RegistrationSetup::class);

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

        return view('registration-setups.form', [
            'setup' => null,
            'branches' => $branches,
            'selectedBranchId' => $selectedBranchId,
            'academicYears' => $academicYears,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', RegistrationSetup::class);

        $data = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'title' => ['required', 'string', 'max:150'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'application_fee' => ['nullable', 'numeric', 'min:0'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', 'in:open,closed,upcoming'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['application_fee'] = $data['application_fee'] ?? 0;

        $setup = RegistrationSetup::create($data);

        return redirect()->route('registrations.setup')
            ->with('success', "Registration campaign '{$setup->title}' configured successfully.");
    }

    public function edit(RegistrationSetup $setup): View
    {
        $this->authorize('update', $setup);

        $setup->load(['branch', 'academicYear']);
        $branches = collect([$setup->branch]);
        $academicYears = AcademicYear::where('branch_id', $setup->branch_id)->orderByDesc('start_date')->get();

        return view('registration-setups.form', [
            'setup' => $setup,
            'branches' => $branches,
            'selectedBranchId' => $setup->branch_id,
            'academicYears' => $academicYears,
        ]);
    }

    public function update(Request $request, RegistrationSetup $setup): RedirectResponse
    {
        $this->authorize('update', $setup);

        $data = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'title' => ['required', 'string', 'max:150'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'application_fee' => ['nullable', 'numeric', 'min:0'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', 'in:open,closed,upcoming'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['application_fee'] = $data['application_fee'] ?? 0;

        $setup->update($data);

        return redirect()->route('registrations.setup')
            ->with('success', 'Registration campaign updated successfully.');
    }

    public function destroy(RegistrationSetup $setup): RedirectResponse
    {
        $this->authorize('delete', $setup);

        $setup->delete();

        return redirect()->route('registrations.setup')
            ->with('success', 'Registration setup removed.');
    }

    private function availableBranches(?int $branchId, bool $canViewAll): Collection
    {
        return Branch::query()
            ->when(! $canViewAll, fn (Builder $query) => $query->whereKey($branchId))
            ->orderBy('name')
            ->get();
    }
}
