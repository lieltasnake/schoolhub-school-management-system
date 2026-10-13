<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;

class BookPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canViewStudents() || $user->canManageUsers();
    }

    public function view(User $user, Book $book): bool
    {
        return $user->canViewStudents() || $user->canManageUsers();
    }

    public function create(User $user): bool
    {
        return $user->canManageStudents() || $user->canManageUsers();
    }

    public function update(User $user, Book $book): bool
    {
        return $user->canManageStudents() || $user->canManageUsers();
    }

    public function delete(User $user, Book $book): bool
    {
        return $user->canManageStudents() || $user->canManageUsers();
    }
}
