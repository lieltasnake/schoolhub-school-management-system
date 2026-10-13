<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\GradePromotion;
use App\Models\User;

class GradePromotionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageStudents();
    }

    public function view(User $user, GradePromotion $promotion): bool
    {
        return $user->canManageStudents()
            && $user->canAccessBranch($promotion->branch_id);
    }

    public function create(User $user, ?Branch $branch = null): bool
    {
        return $user->canManageStudents()
            && ($branch === null || $user->canAccessBranch($branch->id));
    }
}
