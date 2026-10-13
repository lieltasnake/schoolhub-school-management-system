<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\FeeCategory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeeCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_fee_categories(): void
    {
        $this->get(route('fee-categories.index'))->assertRedirect(route('login'));
    }

    public function test_authorized_administrator_can_view_fee_categories_list(): void
    {
        $admin = $this->makeUser('super-administrator');
        $branch = $this->makeBranch('Central Campus', 'CENTRAL');

        $category = FeeCategory::create([
            'branch_id' => $branch->id,
            'name' => 'Tuition Fee',
            'code' => 'TUI',
            'description' => 'Standard tuition',
            'is_mandatory' => true,
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->get(route('fee-categories.index'))
            ->assertOk()
            ->assertSee('Tuition Fee')
            ->assertSee('TUI');
    }

    public function test_authorized_accountant_can_create_fee_category(): void
    {
        $branch = $this->makeBranch('North Campus', 'NORTH');
        $accountant = $this->makeUser('accountant-cashier', $branch);

        $response = $this->actingAs($accountant)->post(route('fee-categories.store'), [
            'branch_id' => $branch->id,
            'name' => 'Transport Fee',
            'code' => 'TRN',
            'description' => 'Bus shuttle service',
            'is_mandatory' => false,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('fee-categories.index'));
        $this->assertDatabaseHas('fee_categories', [
            'branch_id' => $branch->id,
            'name' => 'Transport Fee',
            'code' => 'TRN',
            'is_mandatory' => 0,
        ]);
    }

    public function test_fee_category_name_and_code_must_be_unique_within_branch(): void
    {
        $branch = $this->makeBranch('East Campus', 'EAST');
        $admin = $this->makeUser('school-administrator', $branch);

        FeeCategory::create([
            'branch_id' => $branch->id,
            'name' => 'Exam Fee',
            'code' => 'EXM',
            'is_mandatory' => true,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->post(route('fee-categories.store'), [
            'branch_id' => $branch->id,
            'name' => 'Exam Fee',
            'code' => 'EXM2',
            'is_mandatory' => true,
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('name');

        $responseCode = $this->actingAs($admin)->post(route('fee-categories.store'), [
            'branch_id' => $branch->id,
            'name' => 'Different Exam Fee',
            'code' => 'EXM',
            'is_mandatory' => true,
            'status' => 'active',
        ]);

        $responseCode->assertSessionHasErrors('code');
    }

    public function test_authorized_user_can_update_fee_category(): void
    {
        $branch = $this->makeBranch('South Campus', 'SOUTH');
        $admin = $this->makeUser('school-administrator', $branch);

        $category = FeeCategory::create([
            'branch_id' => $branch->id,
            'name' => 'Library Fee',
            'code' => 'LIB',
            'is_mandatory' => false,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->put(route('fee-categories.update', $category), [
            'name' => 'Updated Library Fee',
            'code' => 'LIB-NEW',
            'is_mandatory' => true,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('fee-categories.index'));
        $this->assertDatabaseHas('fee_categories', [
            'id' => $category->id,
            'name' => 'Updated Library Fee',
            'code' => 'LIB-NEW',
            'is_mandatory' => 1,
        ]);
    }

    public function test_authorized_user_can_delete_fee_category(): void
    {
        $branch = $this->makeBranch('West Campus', 'WEST');
        $admin = $this->makeUser('school-administrator', $branch);

        $category = FeeCategory::create([
            'branch_id' => $branch->id,
            'name' => 'Sports Fee',
            'code' => 'SPT',
            'is_mandatory' => false,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->delete(route('fee-categories.destroy', $category));

        $response->assertRedirect(route('fee-categories.index'));
        $this->assertDatabaseMissing('fee_categories', [
            'id' => $category->id,
        ]);
    }

    public function test_teacher_cannot_manage_fee_categories(): void
    {
        $branch = $this->makeBranch('Main Campus', 'MAIN');
        $teacher = $this->makeUser('teacher', $branch);

        $this->actingAs($teacher)->get(route('fee-categories.index'))->assertForbidden();
        $this->actingAs($teacher)->get(route('fee-categories.create'))->assertForbidden();
    }

    private function makeUser(string $roleSlug, ?Branch $branch = null): User
    {
        $role = Role::firstOrCreate(['slug' => $roleSlug], [
            'name' => str($roleSlug)->replace('-', ' ')->title()->toString(),
        ]);

        return User::factory()->create([
            'role_id' => $role->id,
            'branch_id' => $branch?->id,
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
