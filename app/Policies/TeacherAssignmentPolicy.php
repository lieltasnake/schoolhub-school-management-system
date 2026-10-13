<?php

namespace App\Policies;

use App\Models\TeacherAssignment;
use App\Models\User;

class TeacherAssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageStudents() || $user->canManageUsers();
    }

    public function create(User $user): bool
    {
        return $user->canManageStudents() || $user->canManageUsers();
    }

    public function delete(User $user, TeacherAssignment $assignment): bool
    {
        return ($user->canManageStudents() || $user->canManageUsers())
            && $user->canAccessBranch($assignment->teacher?->branch_id);
    }
}
