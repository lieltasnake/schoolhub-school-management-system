<?php

namespace App\Policies;

use App\Models\Teacher;
use App\Models\User;

class TeacherPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageStudents() || $user->canManageUsers();
    }

    public function view(User $user, Teacher $teacher): bool
    {
        return ($user->canManageStudents() || $user->canManageUsers())
            && $user->canAccessBranch($teacher->branch_id);
    }

    public function create(User $user): bool
    {
        return $user->canManageUsers();
    }

    public function update(User $user, Teacher $teacher): bool
    {
        return $user->canManageUsers()
            && $user->canAccessBranch($teacher->branch_id);
    }

    public function delete(User $user, Teacher $teacher): bool
    {
        return $user->canManageUsers()
            && $user->canAccessBranch($teacher->branch_id);
    }
}
