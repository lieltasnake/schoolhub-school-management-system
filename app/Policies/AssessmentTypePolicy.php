<?php

namespace App\Policies;

use App\Models\AssessmentType;
use App\Models\Branch;
use App\Models\User;

class AssessmentTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageExams();
    }

    public function view(User $user, AssessmentType $type): bool
    {
        return $user->canManageExams()
            && $user->canAccessBranch($type->branch_id);
    }

    public function create(User $user, ?Branch $branch = null): bool
    {
        return $user->canManageExams()
            && ($branch === null || $user->canAccessBranch($branch->id));
    }

    public function update(User $user, AssessmentType $type): bool
    {
        return $user->canManageExams()
            && $user->canAccessBranch($type->branch_id);
    }

    public function delete(User $user, AssessmentType $type): bool
    {
        return $user->canManageExams()
            && $user->canAccessBranch($type->branch_id);
    }
}
