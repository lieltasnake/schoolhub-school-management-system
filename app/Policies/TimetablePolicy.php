<?php

namespace App\Policies;

use App\Models\Timetable;
use App\Models\User;

class TimetablePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageAcademicCalendar() || $user->canViewStudents();
    }

    public function create(User $user): bool
    {
        return $user->canManageAcademicCalendar();
    }

    public function delete(User $user, Timetable $timetable): bool
    {
        return $user->canManageAcademicCalendar()
            && $user->canAccessBranch($timetable->branch_id);
    }
}
