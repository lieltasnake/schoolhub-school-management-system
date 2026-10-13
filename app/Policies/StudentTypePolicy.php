<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\StudentType;
use App\Models\User;

class StudentTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canViewStudents();
    }

    public function view(User $user, StudentType $studentType): bool
    {
        return $user->canViewStudents()
            && ($studentType->branch_id === null || $user->canAccessBranch($studentType->branch_id));
    }

    public function create(User $user, ?Branch $branch = null): bool
    {
        return $user->canManageStudents()
            && ($branch === null || $user->canAccessBranch($branch->id));
    }

    public function update(User $user, StudentType $studentType): bool
    {
        return $user->canManageStudents()
            && ($studentType->branch_id === null || $user->canAccessBranch($studentType->branch_id));
    }

    public function delete(User $user, StudentType $studentType): bool
    {
        return $user->canManageStudents()
            && ($studentType->branch_id === null || $user->canAccessBranch($studentType->branch_id));
    }
}
