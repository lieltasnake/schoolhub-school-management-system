<?php

namespace App\Policies;

use App\Models\BookType;
use App\Models\User;

class BookTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canViewStudents() || $user->canManageUsers();
    }

    public function create(User $user): bool
    {
        return $user->canManageStudents() || $user->canManageUsers();
    }

    public function update(User $user, BookType $bookType): bool
    {
        return $user->canManageStudents() || $user->canManageUsers();
    }

    public function delete(User $user, BookType $bookType): bool
    {
        return $user->canManageStudents() || $user->canManageUsers();
    }
}
