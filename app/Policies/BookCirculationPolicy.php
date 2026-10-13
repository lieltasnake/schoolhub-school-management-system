<?php

namespace App\Policies;

use App\Models\BookCirculation;
use App\Models\User;

class BookCirculationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canViewStudents() || $user->canManageUsers();
    }

    public function create(User $user): bool
    {
        return $user->canManageStudents() || $user->canManageUsers();
    }

    public function update(User $user, BookCirculation $circulation): bool
    {
        return $user->canManageStudents() || $user->canManageUsers();
    }

    public function delete(User $user, BookCirculation $circulation): bool
    {
        return $user->hasAnyRole(['super-administrator', 'school-administrator']);
    }
}
