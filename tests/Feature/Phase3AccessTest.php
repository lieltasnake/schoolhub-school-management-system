<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase3AccessTest extends TestCase
{
    use RefreshDatabase;

    protected function makeRole(string $slug, string $name): Role
    {
        return Role::create([
            'slug' => $slug,
            'name' => $name,
            'description' => $name,
        ]);
    }

    public function test_super_admin_can_view_branch_index(): void
    {
        $role = $this->makeRole('super-administrator', 'Super Administrator');
        $user = User::factory()->create([
            'role_id' => $role->id,
            'branch_id' => null,
        ]);

        $this->actingAs($user)
            ->get('/branches')
            ->assertOk();
    }

    public function test_branch_admin_can_view_own_branch_only(): void
    {
        $branchAdminRole = $this->makeRole('branch-administrator', 'Branch Administrator');
        $branchOne = Branch::create([
            'name' => 'Main Campus',
            'code' => 'MC-001',
            'address' => 'Main Street',
            'status' => 'active',
        ]);
        $branchTwo = Branch::create([
            'name' => 'North Campus',
            'code' => 'NC-002',
            'address' => 'North Avenue',
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'role_id' => $branchAdminRole->id,
            'branch_id' => $branchOne->id,
        ]);

        $this->actingAs($user)
            ->get('/branches/'.$branchOne->id)
            ->assertOk();

        $this->actingAs($user)
            ->get('/branches/'.$branchTwo->id)
            ->assertForbidden();
    }

    public function test_user_without_authorized_role_cannot_view_user_management(): void
    {
        $currentRole = $this->makeRole('teacher', 'Teacher');
        $user = User::factory()->create([
            'role_id' => $currentRole->id,
            'branch_id' => null,
        ]);

        $this->actingAs($user)
            ->get('/users')
            ->assertForbidden();
    }

    public function test_school_administrator_user_list_includes_users_from_all_branches(): void
    {
        $schoolAdministratorRole = $this->makeRole('school-administrator', 'School Administrator');
        $teacherRole = $this->makeRole('teacher', 'Teacher');
        $mainBranch = Branch::create([
            'name' => 'Main Campus',
            'code' => 'MAIN-001',
            'status' => 'active',
        ]);
        $northBranch = Branch::create([
            'name' => 'North Campus',
            'code' => 'NORTH-001',
            'status' => 'active',
        ]);
        $administrator = User::factory()->create([
            'role_id' => $schoolAdministratorRole->id,
            'branch_id' => $mainBranch->id,
        ]);
        $northTeacher = User::factory()->create([
            'name' => 'North Campus Teacher',
            'role_id' => $teacherRole->id,
            'branch_id' => $northBranch->id,
        ]);

        $this->actingAs($administrator)
            ->get('/users')
            ->assertOk()
            ->assertSee($northTeacher->name);
    }

    public function test_super_administrator_can_filter_users_by_role_and_branch(): void
    {
        $superAdministratorRole = $this->makeRole('super-administrator', 'Super Administrator');
        $teacherRole = $this->makeRole('teacher', 'Teacher');
        $accountantRole = $this->makeRole('accountant-cashier', 'Accountant / Cashier');
        $northBranch = Branch::create([
            'name' => 'North Campus',
            'code' => 'NORTH-002',
            'status' => 'active',
        ]);
        $southBranch = Branch::create([
            'name' => 'South Campus',
            'code' => 'SOUTH-002',
            'status' => 'active',
        ]);
        $administrator = User::factory()->create(['role_id' => $superAdministratorRole->id]);
        $northTeacher = User::factory()->create([
            'name' => 'Filtered North Teacher',
            'role_id' => $teacherRole->id,
            'branch_id' => $northBranch->id,
        ]);
        $southTeacher = User::factory()->create([
            'name' => 'Unfiltered South Teacher',
            'role_id' => $teacherRole->id,
            'branch_id' => $southBranch->id,
        ]);
        User::factory()->create([
            'name' => 'North Cashier',
            'role_id' => $accountantRole->id,
            'branch_id' => $northBranch->id,
        ]);

        $this->actingAs($administrator)
            ->get(route('users.index', ['role_id' => $teacherRole->id, 'branch_id' => $northBranch->id]))
            ->assertOk()
            ->assertSee($northTeacher->name)
            ->assertDontSee($southTeacher->name)
            ->assertDontSee('North Cashier');
    }

    public function test_branch_administrator_cannot_override_user_list_branch_scope_with_query_parameters(): void
    {
        $branchAdministratorRole = $this->makeRole('branch-administrator', 'Branch Administrator');
        $teacherRole = $this->makeRole('teacher', 'Teacher');
        $assignedBranch = Branch::create([
            'name' => 'Assigned Campus',
            'code' => 'ASSIGNED-003',
            'status' => 'active',
        ]);
        $otherBranch = Branch::create([
            'name' => 'Other Campus',
            'code' => 'OTHER-003',
            'status' => 'active',
        ]);
        $administrator = User::factory()->create([
            'role_id' => $branchAdministratorRole->id,
            'branch_id' => $assignedBranch->id,
        ]);
        $assignedTeacher = User::factory()->create([
            'name' => 'Assigned Teacher',
            'role_id' => $teacherRole->id,
            'branch_id' => $assignedBranch->id,
        ]);
        $otherTeacher = User::factory()->create([
            'name' => 'Other Campus Teacher',
            'role_id' => $teacherRole->id,
            'branch_id' => $otherBranch->id,
        ]);

        $this->actingAs($administrator)
            ->get(route('users.index', ['branch_id' => $otherBranch->id, 'role_id' => $teacherRole->id]))
            ->assertOk()
            ->assertSee($assignedTeacher->name)
            ->assertDontSee($otherTeacher->name);
    }

    public function test_last_active_super_administrator_cannot_be_demoted(): void
    {
        $superAdministratorRole = $this->makeRole('super-administrator', 'Super Administrator');
        $teacherRole = $this->makeRole('teacher', 'Teacher');
        $administrator = User::factory()->create([
            'role_id' => $superAdministratorRole->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($administrator)->put(route('users.update', $administrator), [
            'name' => $administrator->name,
            'email' => $administrator->email,
            'role_id' => $teacherRole->id,
            'branch_id' => '',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('role_id');
        $this->assertDatabaseHas('users', [
            'id' => $administrator->id,
            'role_id' => $superAdministratorRole->id,
            'status' => 'active',
        ]);
    }

    public function test_last_active_super_administrator_cannot_be_deactivated(): void
    {
        $superAdministratorRole = $this->makeRole('super-administrator', 'Super Administrator');
        $administrator = User::factory()->create([
            'role_id' => $superAdministratorRole->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($administrator)->put(route('users.update', $administrator), [
            'name' => $administrator->name,
            'email' => $administrator->email,
            'role_id' => $superAdministratorRole->id,
            'branch_id' => '',
            'status' => 'inactive',
        ]);

        $response->assertSessionHasErrors(['role_id', 'status']);
        $this->assertDatabaseHas('users', [
            'id' => $administrator->id,
            'role_id' => $superAdministratorRole->id,
            'status' => 'active',
        ]);
    }
}
