<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\FeeCategory;
use App\Models\User;

class FeeCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canViewFinance();
    }

    public function view(User $user, FeeCategory $feeCategory): bool
    {
        return $user->canViewFinance()
            && $user->canAccessBranch($feeCategory->branch_id);
    }

    public function create(User $user, ?Branch $branch = null): bool
    {
        return $user->canManageFinance()
            && ($branch === null || $user->canAccessBranch($branch->id));
    }

    public function update(User $user, FeeCategory $feeCategory): bool
    {
        return $user->canManageFinance()
            && $user->canAccessBranch($feeCategory->branch_id);
    }

    public function delete(User $user, FeeCategory $feeCategory): bool
    {
        return $user->canManageFinance()
            && $user->canAccessBranch($feeCategory->branch_id);
    }
}
