<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\StudentGroup;
use App\Models\User;

class StudentGroupPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canViewStudents();
    }

    public function view(User $user, StudentGroup $studentGroup): bool
    {
        return $user->canViewStudents()
            && $user->canAccessBranch($studentGroup->branch_id);
    }

    public function create(User $user, ?Branch $branch = null): bool
    {
        return $user->canManageStudents()
            && ($branch === null || $user->canAccessBranch($branch->id));
    }

    public function update(User $user, StudentGroup $studentGroup): bool
    {
        return $user->canManageStudents()
            && $user->canAccessBranch($studentGroup->branch_id);
    }

    public function delete(User $user, StudentGroup $studentGroup): bool
    {
        return $user->canManageStudents()
            && $user->canAccessBranch($studentGroup->branch_id);
    }
}
