<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'permission_keys',
    ];

    public const DEFAULT_ROLES = [
        'super-administrator' => 'Super Administrator',
        'school-administrator' => 'School Administrator',
        'branch-administrator' => 'Branch Administrator',
        'accountant-cashier' => 'Accountant / Cashier',
        'teacher' => 'Teacher',
        'registrar' => 'Registrar',
        'auditor' => 'Auditor',
    ];

    public const CUSTOM_PERMISSION_LABELS = [
        'manage_users' => 'Manage users in the assigned scope',
        'manage_branch_users' => 'Manage users in the assigned branch',
        'manage_academic_calendar' => 'Manage academic years and terms',
        'manage_branch_academic_calendar' => 'Manage academic years and terms in the assigned branch',
        'manage_students' => 'Manage student records',
        'manage_branch_students' => 'Manage students in the assigned branch',
        'view_assigned_students' => 'View assigned students',
        'manage_attendance' => 'Manage attendance',
        'manage_exams' => 'Manage examinations and results',
        'view_academics' => 'View academic records',
        'manage_enrollment' => 'Manage student enrollment',
        'manage_finance' => 'Manage finance records',
        'manage_branch_finance' => 'Manage finance in the assigned branch',
        'view_branch_finance' => 'View finance in the assigned branch',
        'manage_receipts' => 'Manage payment receipts',
        'view_finance' => 'View finance reports',
        'manage_reports' => 'Manage reports',
        'manage_branch_reports' => 'Manage reports in the assigned branch',
        'view_reports' => 'View reports',
        'view_branch_data' => 'View authorized branch data',
    ];

    protected function casts(): array
    {
        return [
            'permission_keys' => 'array',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function isSystemRole(): bool
    {
        return array_key_exists($this->slug, self::DEFAULT_ROLES);
    }

    public static function ensureDefaults(): void
    {
        foreach (self::DEFAULT_ROLES as $slug => $name) {
            static::query()->firstOrCreate([
                'slug' => $slug,
            ], [
                'name' => $name,
                'description' => $name,
            ]);
        }
    }

    public function permissions(): array
    {
        if (is_array($this->permission_keys)) {
            return $this->permission_keys;
        }

        return match ($this->slug) {
            'super-administrator' => ['manage_system', 'manage_roles', 'manage_all_branches', 'manage_branches', 'manage_users', 'manage_school_settings', 'manage_academic_calendar', 'manage_finance', 'manage_students', 'manage_reports'],
            'school-administrator' => ['manage_all_branches', 'manage_branches', 'manage_users', 'manage_school_settings', 'manage_academic_calendar', 'manage_finance', 'manage_students', 'manage_reports'],
            'branch-administrator' => ['manage_branch_users', 'manage_branch_students', 'manage_branch_academic_calendar', 'manage_branch_finance', 'manage_branch_reports'],
            'accountant-cashier' => ['manage_finance', 'view_branch_finance', 'manage_receipts'],
            'teacher' => ['view_assigned_students', 'manage_attendance', 'view_reports'],
            'registrar' => ['manage_students', 'view_academics', 'manage_enrollment'],
            'auditor' => ['view_reports', 'view_finance', 'view_branch_data'],
            default => [],
        };
    }
}
