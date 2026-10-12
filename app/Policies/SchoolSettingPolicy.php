<?php

namespace App\Policies;

use App\Models\SchoolSetting;
use App\Models\User;

class SchoolSettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageSchoolSettings();
    }

    public function update(User $user, SchoolSetting $schoolSetting): bool
    {
        return $user->canManageSchoolSettings();
    }
}
