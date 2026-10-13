<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\FeeStructure;
use App\Models\User;

class FeeStructurePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canViewFinance();
    }

    public function view(User $user, FeeStructure $feeStructure): bool
    {
        return $user->canViewFinance()
            && $user->canAccessBranch($feeStructure->branch_id);
    }

    public function create(User $user, ?Branch $branch = null): bool
    {
        return $user->canManageFinance()
            && ($branch === null || $user->canAccessBranch($branch->id));
    }

    public function update(User $user, FeeStructure $feeStructure): bool
    {
        return $user->canManageFinance()
            && $user->canAccessBranch($feeStructure->branch_id);
    }

    public function delete(User $user, FeeStructure $feeStructure): bool
    {
        return $user->canManageFinance()
            && $user->canAccessBranch($feeStructure->branch_id);
    }
}
