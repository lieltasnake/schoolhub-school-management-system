<?php

namespace App\Policies;

use App\Models\LibraryMember;
use App\Models\User;

class LibraryMemberPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canViewStudents() || $user->canManageUsers();
    }

    public function view(User $user, LibraryMember $member): bool
    {
        return ($user->canViewStudents() || $user->canManageUsers())
            && $user->canAccessBranch($member->branch_id);
    }

    public function create(User $user): bool
    {
        return $user->canManageStudents() || $user->canManageUsers();
    }

    public function update(User $user, LibraryMember $member): bool
    {
        return ($user->canManageStudents() || $user->canManageUsers())
            && $user->canAccessBranch($member->branch_id);
    }

    public function delete(User $user, LibraryMember $member): bool
    {
        return ($user->canManageStudents() || $user->canManageUsers())
            && $user->canAccessBranch($member->branch_id);
    }
}
