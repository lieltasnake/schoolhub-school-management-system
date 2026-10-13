<?php

namespace App\Providers;

use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\FeeCategory;
use App\Models\FeeDiscount;
use App\Models\FeeStructure;
use App\Models\Grade;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Role;
use App\Models\SchoolSetting;
use App\Models\Section;
use App\Models\Student;
use App\Models\Term;
use App\Models\User;
use App\Policies\AcademicYearPolicy;
use App\Policies\BranchPolicy;
use App\Policies\FeeCategoryPolicy;
use App\Policies\FeeDiscountPolicy;
use App\Policies\FeeStructurePolicy;
use App\Policies\GradePolicy;
use App\Policies\InvoicePolicy;
use App\Policies\PaymentPolicy;
use App\Policies\RolePolicy;
use App\Policies\SchoolSettingPolicy;
use App\Policies\SectionPolicy;
use App\Policies\StudentPolicy;
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
        FeeCategory::class => FeeCategoryPolicy::class,
        FeeDiscount::class => FeeDiscountPolicy::class,
        FeeStructure::class => FeeStructurePolicy::class,
        Grade::class => GradePolicy::class,
        Invoice::class => InvoicePolicy::class,
        Payment::class => PaymentPolicy::class,
        SchoolSetting::class => SchoolSettingPolicy::class,
        Role::class => RolePolicy::class,
        Section::class => SectionPolicy::class,
        Student::class => StudentPolicy::class,
        Term::class => TermPolicy::class,
        User::class => UserPolicy::class,
    ];
}
