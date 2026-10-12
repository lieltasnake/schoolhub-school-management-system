<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_uses_real_totals_and_explains_unconfigured_modules(): void
    {
        $superAdministratorRole = $this->makeRole('super-administrator', 'Super Administrator');
        $teacherRole = $this->makeRole('teacher', 'Teacher');
        $mainCampus = $this->makeBranch('Main Campus', 'MAIN');
        $westCampus = $this->makeBranch('West Campus', 'WEST');

        $administrator = User::factory()->create(['role_id' => $superAdministratorRole->id]);
        User::factory()->create(['role_id' => $teacherRole->id, 'branch_id' => $mainCampus->id]);
        User::factory()->create(['role_id' => $teacherRole->id, 'branch_id' => $westCampus->id]);

        $response = $this->actingAs($administrator)->get(route('dashboard'));

        $response->assertOk()
            ->assertViewHas('totalBranches', 2)
            ->assertViewHas('totalStaff', 3)
            ->assertViewHas('studentCount', null)
            ->assertSee('SchoolHub | Dashboard')
            ->assertSee($administrator->name)
            ->assertSee('Main Campus')
            ->assertSee('West Campus')
            ->assertSee('Not configured');
    }

    public function test_branch_administrator_only_sees_own_branch_and_staff_totals(): void
    {
        $branchAdministratorRole = $this->makeRole('branch-administrator', 'Branch Administrator');
        $teacherRole = $this->makeRole('teacher', 'Teacher');
        $allowedBranch = $this->makeBranch('Allowed Campus', 'ALLOW');
        $restrictedBranch = $this->makeBranch('Restricted Campus', 'RESTRICT');

        $administrator = User::factory()->create([
            'role_id' => $branchAdministratorRole->id,
            'branch_id' => $allowedBranch->id,
        ]);
        User::factory()->create(['role_id' => $teacherRole->id, 'branch_id' => $allowedBranch->id]);
        User::factory()->create(['role_id' => $teacherRole->id, 'branch_id' => $restrictedBranch->id]);

        $response = $this->actingAs($administrator)->get(route('dashboard'));

        $response->assertOk()
            ->assertViewHas('totalBranches', 1)
            ->assertViewHas('totalStaff', 2)
            ->assertSee('Allowed Campus')
            ->assertDontSee('Restricted Campus');
    }

    public function test_dashboard_displays_the_active_academic_year_and_term(): void
    {
        $superAdministratorRole = $this->makeRole('super-administrator', 'Super Administrator');
        $branch = $this->makeBranch('Main Campus', 'MAIN');
        $administrator = User::factory()->create(['role_id' => $superAdministratorRole->id]);
        $academicYear = $branch->academicYears()->create([
            'name' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
            'status' => 'active',
        ]);
        $academicYear->terms()->create([
            'name' => 'Term 1',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
            'status' => 'active',
        ]);

        $this->actingAs($administrator)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('2026-2027')
            ->assertSee('Term 1');
    }

    public function test_dashboard_branch_filter_scopes_branch_panels_and_staff_count(): void
    {
        $superAdministratorRole = $this->makeRole('super-administrator', 'Super Administrator');
        $teacherRole = $this->makeRole('teacher', 'Teacher');
        $mainCampus = $this->makeBranch('Main Campus', 'MAIN');
        $westCampus = $this->makeBranch('West Campus', 'WEST');
        $administrator = User::factory()->create(['role_id' => $superAdministratorRole->id]);
        User::factory()->create(['role_id' => $teacherRole->id, 'branch_id' => $mainCampus->id]);
        User::factory()->create(['role_id' => $teacherRole->id, 'branch_id' => $westCampus->id]);

        $this->actingAs($administrator)
            ->get(route('dashboard', ['branch_id' => $mainCampus->id]))
            ->assertOk()
            ->assertViewHas('totalBranches', 2)
            ->assertViewHas('totalStaff', 1)
            ->assertViewHas('branches', fn ($branches) => $branches->pluck('name')->all() === ['Main Campus']);
    }

    public function test_branch_administrator_branch_list_does_not_reveal_other_campuses(): void
    {
        $branchAdministratorRole = $this->makeRole('branch-administrator', 'Branch Administrator');
        $allowedBranch = $this->makeBranch('Allowed Campus', 'ALLOW');
        $this->makeBranch('Restricted Campus', 'RESTRICT');
        $administrator = User::factory()->create([
            'role_id' => $branchAdministratorRole->id,
            'branch_id' => $allowedBranch->id,
        ]);

        $response = $this->actingAs($administrator)->get(route('branches.index'));

        $response->assertOk()
            ->assertSee('Allowed Campus')
            ->assertDontSee('Restricted Campus');
    }

    private function makeRole(string $slug, string $name): Role
    {
        return Role::firstOrCreate(['slug' => $slug], [
            'name' => $name,
            'description' => $name,
        ]);
    }

    private function makeBranch(string $name, string $code): Branch
    {
        return Branch::create([
            'name' => $name,
            'code' => $code,
            'status' => 'active',
        ]);
    }
}
