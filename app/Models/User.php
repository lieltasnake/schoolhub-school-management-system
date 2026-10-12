<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role_id', 'branch_id', 'status', 'locale'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role_id' => 'integer',
            'branch_id' => 'integer',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function hasRole(string|array $roles): bool
    {
        $roleNames = is_array($roles) ? $roles : [$roles];

        $normalized = collect($roleNames)
            ->map(fn (string $role) => strtolower(trim($role)))
            ->filter()
            ->all();

        if ($normalized === []) {
            return false;
        }

        $currentRole = strtolower((string) ($this->role?->slug ?? ''));

        return in_array($currentRole, $normalized, true);
    }

    public function hasAnyRole(array $roles): bool
    {
        return collect($roles)->contains(fn (string $role) => $this->hasRole($role));
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->role?->permissions() ?? [], true);
    }

    public function canManageRoles(): bool
    {
        return $this->hasRole('super-administrator');
    }

    public function canManageBranches(): bool
    {
        return $this->hasPermission('manage_branches');
    }

    public function canManageAcademicCalendar(): bool
    {
        return $this->hasPermission('manage_academic_calendar')
            || $this->hasPermission('manage_branch_academic_calendar');
    }

    public function canManageSchoolSettings(): bool
    {
        return $this->hasPermission('manage_school_settings');
    }

    public function canManageUsers(): bool
    {
        return $this->hasPermission('manage_users') || $this->hasPermission('manage_branch_users');
    }

    public function canAccessBranch(?int $branchId = null): bool
    {
        if ($this->hasPermission('manage_all_branches')) {
            return true;
        }

        if ($branchId === null) {
            return true;
        }

        if (! $this->branch_id) {
            return false;
        }

        return (int) $this->branch_id === (int) $branchId;
    }

    public function canAssignRole(string $roleSlug): bool
    {
        if ($this->hasRole('super-administrator')) {
            return true;
        }

        if ($this->hasRole('school-administrator')) {
            return ! in_array($roleSlug, ['super-administrator', 'school-administrator'], true);
        }

        if ($this->hasRole('branch-administrator')) {
            return ! in_array($roleSlug, ['super-administrator', 'school-administrator', 'branch-administrator'], true);
        }

        if ($this->hasPermission('manage_users') || $this->hasPermission('manage_branch_users')) {
            return ! in_array($roleSlug, ['super-administrator', 'school-administrator', 'branch-administrator'], true);
        }

        return false;
    }
}
