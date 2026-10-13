<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\GradeScale;
use App\Models\User;

class GradeScalePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageExams();
    }

    public function view(User $user, GradeScale $scale): bool
    {
        return $user->canManageExams()
            && $user->canAccessBranch($scale->branch_id);
    }

    public function create(User $user, ?Branch $branch = null): bool
    {
        return $user->canManageExams()
            && ($branch === null || $user->canAccessBranch($branch->id));
    }

    public function update(User $user, GradeScale $scale): bool
    {
        return $user->canManageExams()
            && $user->canAccessBranch($scale->branch_id);
    }

    public function delete(User $user, GradeScale $scale): bool
    {
        return $user->canManageExams()
            && $user->canAccessBranch($scale->branch_id);
    }
}
