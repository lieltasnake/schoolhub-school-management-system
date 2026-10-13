<?php

namespace App\Policies;

use App\Models\StaffAttendance;
use App\Models\User;

class StaffAttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageUsers();
    }

    public function create(User $user): bool
    {
        return $user->canManageUsers();
    }

    public function update(User $user, StaffAttendance $attendance): bool
    {
        return $user->canManageUsers();
    }

    public function delete(User $user, StaffAttendance $attendance): bool
    {
        return $user->canManageUsers();
    }
}
