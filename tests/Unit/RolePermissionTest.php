<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Models\User;
use App\Policies\RolePolicy;
use PHPUnit\Framework\TestCase;

class RolePermissionTest extends TestCase
{
    public function test_legacy_super_administrator_role_retains_role_management_access_without_database_permissions(): void
    {
        $role = new Role;
        $role->slug = 'super-administrator';
        $user = new User;
        $user->setRelation('role', $role);

        $this->assertContains('manage_roles', $role->permissions());
        $this->assertTrue($user->canManageRoles());
        $this->assertTrue((new RolePolicy)->viewAny($user));
    }

    public function test_legacy_school_administrator_role_retains_all_branch_access_without_database_permissions(): void
    {
        $role = new Role;
        $role->slug = 'school-administrator';
        $user = new User;
        $user->branch_id = 12;
        $user->setRelation('role', $role);

        $this->assertContains('manage_all_branches', $role->permissions());
        $this->assertTrue($user->canAccessBranch(99));
    }
}
