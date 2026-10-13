<?php

namespace App\Policies;

use App\Models\Period;
use App\Models\User;

class PeriodPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageAcademicCalendar();
    }

    public function view(User $user, Period $period): bool
    {
        return $user->canManageAcademicCalendar()
            && $user->canAccessBranch($period->branch_id);
    }

    public function create(User $user): bool
    {
        return $user->canManageAcademicCalendar();
    }

    public function update(User $user, Period $period): bool
    {
        return $user->canManageAcademicCalendar()
            && $user->canAccessBranch($period->branch_id);
    }

    public function delete(User $user, Period $period): bool
    {
        return $user->canManageAcademicCalendar()
            && $user->canAccessBranch($period->branch_id);
    }
}
