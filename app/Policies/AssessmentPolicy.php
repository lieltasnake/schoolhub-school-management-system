<?php

namespace App\Policies;

use App\Models\Assessment;
use App\Models\Branch;
use App\Models\User;

class AssessmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageExams();
    }

    public function view(User $user, Assessment $assessment): bool
    {
        return $user->canManageExams()
            && $user->canAccessBranch($assessment->branch_id);
    }

    public function create(User $user, ?Branch $branch = null): bool
    {
        return $user->canManageExams()
            && ($branch === null || $user->canAccessBranch($branch->id));
    }

    public function update(User $user, Assessment $assessment): bool
    {
        return $user->canManageExams()
            && $user->canAccessBranch($assessment->branch_id);
    }

    public function delete(User $user, Assessment $assessment): bool
    {
        return $user->canManageExams()
            && $user->canAccessBranch($assessment->branch_id);
    }

    public function enterGrades(User $user, Assessment $assessment): bool
    {
        return $user->canEnterGrades()
            && $user->canAccessBranch($assessment->branch_id);
    }
}
