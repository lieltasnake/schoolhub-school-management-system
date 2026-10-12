<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Role;
use App\Models\SchoolSetting;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $canViewAllBranches = $user->hasAnyRole(['super-administrator', 'school-administrator']);
        $accessibleBranches = Branch::query()
            ->when(! $canViewAllBranches, fn (Builder $query) => $query->whereKey($user->branch_id))
            ->orderBy('name')
            ->get(['id', 'name']);
        $selectedBranchId = null;

        if ($canViewAllBranches && $request->filled('branch_id')) {
            $requestedBranchId = $request->integer('branch_id');
            $selectedBranchId = $accessibleBranches->contains('id', $requestedBranchId)
                ? $requestedBranchId
                : null;
        }

        $branchQuery = Branch::query()->orderBy('name');
        $staffQuery = User::query()->whereHas('role', static function (Builder $query): void {
            $query->whereIn('slug', array_keys(Role::DEFAULT_ROLES));
        })->where('status', 'active');
        $academicYearQuery = AcademicYear::query()
            ->with(['terms' => fn (HasMany $query) => $query->where('status', 'active')])
            ->where('status', 'active');

        $scopeBranchId = $canViewAllBranches ? $selectedBranchId : $user->branch_id;
        if ($scopeBranchId !== null) {
            $branchQuery->whereKey($scopeBranchId);
            $staffQuery->where('branch_id', $scopeBranchId);
            $academicYearQuery->where('branch_id', $scopeBranchId);
        } elseif (! $canViewAllBranches) {
            if ($user->branch_id === null) {
                $branchQuery->whereRaw('1 = 0');
                $staffQuery->whereRaw('1 = 0');
                $academicYearQuery->whereRaw('1 = 0');
            } else {
                $branchQuery->whereKey($user->branch_id);
                $staffQuery->where('branch_id', $user->branch_id);
                $academicYearQuery->where('branch_id', $user->branch_id);
            }
        }

        $activeYears = $academicYearQuery->orderBy('name')->get();
        $activeTerms = $activeYears->flatMap(fn (AcademicYear $academicYear) => $academicYear->terms);

        return view('dashboard', [
            'user' => $user,
            'totalBranches' => $accessibleBranches->count(),
            'totalStaff' => $staffQuery->count(),
            'branches' => $branchQuery->limit(5)->get(),
            'accessibleBranches' => $accessibleBranches,
            'selectedBranchId' => $selectedBranchId,
            'schoolSettings' => SchoolSetting::current(),
            'studentCount' => null,
            'activeAcademicYear' => $activeYears->count() === 1
                ? $activeYears->first()->name
                : ($activeYears->isNotEmpty() ? 'Varies by campus' : null),
            'activeTerm' => $activeTerms->count() === 1
                ? $activeTerms->first()->name
                : ($activeTerms->isNotEmpty() ? 'Varies by campus' : null),
            'feesCollected' => null,
            'outstandingBalance' => null,
        ]);
    }
}
