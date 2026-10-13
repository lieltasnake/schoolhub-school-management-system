<?php

namespace App\Policies;

use App\Models\Library;
use App\Models\User;

class LibraryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageStudents() || $user->canManageUsers() || $user->canViewStudents();
    }

    public function view(User $user, Library $library): bool
    {
        return ($user->canManageStudents() || $user->canManageUsers() || $user->canViewStudents())
            && $user->canAccessBranch($library->branch_id);
    }

    public function create(User $user): bool
    {
        return $user->canManageBranches() || $user->hasRole('super-administrator') || $user->hasRole('school-administrator');
    }

    public function update(User $user, Library $library): bool
    {
        return $this->create($user) && $user->canAccessBranch($library->branch_id);
    }

    public function delete(User $user, Library $library): bool
    {
        return $this->create($user) && $user->canAccessBranch($library->branch_id);
    }
}
