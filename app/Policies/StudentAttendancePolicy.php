<?php

namespace App\Policies;

use App\Models\StudentAttendance;
use App\Models\User;

class StudentAttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageStudents() || $user->canViewStudents();
    }

    public function create(User $user): bool
    {
        return $user->canManageStudents();
    }

    public function update(User $user, StudentAttendance $attendance): bool
    {
        return $user->canManageStudents();
    }

    public function delete(User $user, StudentAttendance $attendance): bool
    {
        return $user->canManageStudents();
    }
}
