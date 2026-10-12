<?php

namespace App\Providers;

use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Role;
use App\Models\SchoolSetting;
use App\Models\Term;
use App\Models\User;
use App\Policies\AcademicYearPolicy;
use App\Policies\BranchPolicy;
use App\Policies\RolePolicy;
use App\Policies\SchoolSettingPolicy;
use App\Policies\TermPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as BaseAuthServiceProvider;

class AuthServiceProvider extends BaseAuthServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        AcademicYear::class => AcademicYearPolicy::class,
        Branch::class => BranchPolicy::class,
        SchoolSetting::class => SchoolSettingPolicy::class,
        Role::class => RolePolicy::class,
        Term::class => TermPolicy::class,
        User::class => UserPolicy::class,
    ];
}
