<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageRoles();
    }

    public function view(User $user, Role $role): bool
    {
        return $user->canManageRoles();
    }

    public function create(User $user): bool
    {
        return $user->canManageRoles();
    }

    public function update(User $user, Role $role): bool
    {
        return $user->canManageRoles() && ! $role->isSystemRole();
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->canManageRoles()
            && ! $role->isSystemRole()
            && ! $role->users()->exists();
    }
}
