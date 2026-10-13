<?php

namespace App\Policies;

use App\Models\Classroom;
use App\Models\User;

class ClassroomPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageAcademicCalendar();
    }

    public function view(User $user, Classroom $classroom): bool
    {
        return $user->canManageAcademicCalendar()
            && $user->canAccessBranch($classroom->branch_id);
    }

    public function create(User $user): bool
    {
        return $user->canManageAcademicCalendar();
    }

    public function update(User $user, Classroom $classroom): bool
    {
        return $user->canManageAcademicCalendar()
            && $user->canAccessBranch($classroom->branch_id);
    }

    public function delete(User $user, Classroom $classroom): bool
    {
        return $user->canManageAcademicCalendar()
            && $user->canAccessBranch($classroom->branch_id);
    }
}
