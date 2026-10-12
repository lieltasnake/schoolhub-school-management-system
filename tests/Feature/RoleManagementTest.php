<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_administrator_can_create_and_update_a_custom_role(): void
    {
        $administrator = $this->makeUser('super-administrator');

        $this->actingAs($administrator)
            ->get(route('roles.index'))
            ->assertOk()
            ->assertSee('Super Administrator');
        $this->get(route('roles.create'))->assertOk();

        $response = $this->actingAs($administrator)->post(route('roles.store'), [
            'name' => 'Campus Calendar Manager',
            'slug' => 'campus-calendar-manager',
            'description' => 'Manage the academic calendar for one campus.',
            'permission_keys' => ['manage_branch_academic_calendar'],
        ]);

        $response->assertRedirect(route('roles.index'));
        $role = Role::query()->where('slug', 'campus-calendar-manager')->firstOrFail();
        $this->assertSame(['manage_branch_academic_calendar'], $role->permissions());

        $branch = Branch::factory()->create();
        $assignedUser = User::factory()->create([
            'role_id' => $role->id,
            'branch_id' => $branch->id,
        ]);
        $this->assertTrue($assignedUser->canManageAcademicCalendar());

        $this->put(route('roles.update', $role), [
            'name' => 'Campus Setup Manager',
            'slug' => 'campus-calendar-manager',
            'description' => 'Manage branch academic setup.',
            'permission_keys' => ['manage_branch_academic_calendar', 'manage_branch_users'],
        ])->assertRedirect(route('roles.index'));

        $this->get(route('roles.edit', $role))->assertOk();

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => 'Campus Setup Manager',
        ]);
        $this->assertSame(
            ['manage_branch_academic_calendar', 'manage_branch_users'],
            $role->fresh()->permissions(),
        );
    }

    public function test_only_super_administrators_can_access_role_management(): void
    {
        $schoolAdministrator = $this->makeUser('school-administrator');

        $this->actingAs($schoolAdministrator)
            ->get(route('roles.index'))
            ->assertForbidden();
    }

    public function test_custom_roles_cannot_receive_system_wide_privileges(): void
    {
        $administrator = $this->makeUser('super-administrator');

        $response = $this->actingAs($administrator)->from(route('roles.create'))->post(route('roles.store'), [
            'name' => 'Unsafe Role',
            'slug' => 'unsafe-role',
            'permission_keys' => ['manage_all_branches'],
        ]);

        $response->assertRedirect(route('roles.create'))
            ->assertSessionHasErrors('permission_keys.0');
        $this->assertDatabaseMissing('roles', ['slug' => 'unsafe-role']);
    }

    public function test_builtin_roles_cannot_be_edited_or_deleted(): void
    {
        $administrator = $this->makeUser('super-administrator');
        $builtinRole = $this->makeUser('teacher')->role;

        $this->actingAs($administrator)
            ->get(route('roles.edit', $builtinRole))
            ->assertForbidden();

        $this->delete(route('roles.destroy', $builtinRole))->assertForbidden();
        $this->assertModelExists($builtinRole);
    }

    public function test_custom_role_cannot_be_deleted_while_users_are_assigned(): void
    {
        $administrator = $this->makeUser('super-administrator');
        $customRole = Role::create([
            'name' => 'Campus Reviewer',
            'slug' => 'campus-reviewer',
            'permission_keys' => ['view_reports'],
        ]);
        User::factory()->create(['role_id' => $customRole->id]);

        $this->actingAs($administrator)
            ->delete(route('roles.destroy', $customRole))
            ->assertForbidden();
        $this->assertModelExists($customRole);
    }

    public function test_super_administrator_can_delete_an_unused_custom_role(): void
    {
        $administrator = $this->makeUser('super-administrator');
        $customRole = Role::create([
            'name' => 'Temporary Reviewer',
            'slug' => 'temporary-reviewer',
            'permission_keys' => ['view_reports'],
        ]);

        $this->actingAs($administrator)
            ->delete(route('roles.destroy', $customRole))
            ->assertRedirect(route('roles.index'));
        $this->assertDatabaseMissing('roles', ['id' => $customRole->id]);
    }

    private function makeUser(string $roleSlug): User
    {
        $role = Role::firstOrCreate(['slug' => $roleSlug], [
            'name' => str($roleSlug)->replace('-', ' ')->title()->toString(),
            'description' => $roleSlug,
        ]);

        return User::factory()->create(['role_id' => $role->id]);
    }
}
