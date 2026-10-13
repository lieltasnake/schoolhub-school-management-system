<?php

namespace App\Policies;

use App\Models\Subject;
use App\Models\User;

class SubjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageAcademicCalendar() || $user->canManageStudents();
    }

    public function view(User $user, Subject $subject): bool
    {
        return ($user->canManageAcademicCalendar() || $user->canManageStudents())
            && $user->canAccessBranch($subject->branch_id);
    }

    public function create(User $user): bool
    {
        return $user->canManageAcademicCalendar();
    }

    public function update(User $user, Subject $subject): bool
    {
        return $user->canManageAcademicCalendar()
            && $user->canAccessBranch($subject->branch_id);
    }

    public function delete(User $user, Subject $subject): bool
    {
        return $user->canManageAcademicCalendar()
            && $user->canAccessBranch($subject->branch_id);
    }
}
