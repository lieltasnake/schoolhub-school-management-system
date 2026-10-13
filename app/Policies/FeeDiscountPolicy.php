<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\FeeDiscount;
use App\Models\User;

class FeeDiscountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canViewFinance();
    }

    public function view(User $user, FeeDiscount $feeDiscount): bool
    {
        return $user->canViewFinance()
            && $user->canAccessBranch($feeDiscount->branch_id);
    }

    public function create(User $user, ?Branch $branch = null): bool
    {
        return $user->canManageFinance()
            && ($branch === null || $user->canAccessBranch($branch->id));
    }

    public function update(User $user, FeeDiscount $feeDiscount): bool
    {
        return $user->canManageFinance()
            && $user->canAccessBranch($feeDiscount->branch_id);
    }

    public function delete(User $user, FeeDiscount $feeDiscount): bool
    {
        return $user->canManageFinance()
            && $user->canAccessBranch($feeDiscount->branch_id);
    }
}
