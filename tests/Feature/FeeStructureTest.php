<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use App\Models\Grade;
use App\Models\Role;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeeStructureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_fee_structures(): void
    {
        $this->get(route('fee-structures.index'))->assertRedirect(route('login'));
    }

    public function test_authorized_user_can_view_fee_structures(): void
    {
        $admin = $this->makeUser('super-administrator');
        $branch = $this->makeBranch('Central Campus', 'CENTRAL');
        $category = FeeCategory::create([
            'branch_id' => $branch->id,
            'name' => 'Tuition Fee',
            'code' => 'TUI',
            'status' => 'active',
        ]);
        $year = AcademicYear::create([
            'branch_id' => $branch->id,
            'name' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
            'status' => 'active',
        ]);
        $grade = Grade::create([
            'branch_id' => $branch->id,
            'name' => 'Grade 1',
            'code' => 'G1',
            'level' => 1,
            'status' => 'active',
        ]);

        FeeStructure::create([
            'branch_id' => $branch->id,
            'fee_category_id' => $category->id,
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'amount' => '1250.00',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->get(route('fee-structures.index'))
            ->assertOk()
            ->assertSee('Tuition Fee')
            ->assertSee('$1,250.00')
            ->assertSee('Grade 1');
    }

    public function test_authorized_accountant_can_create_fee_structure(): void
    {
        $branch = $this->makeBranch('North Campus', 'NORTH');
        $accountant = $this->makeUser('accountant-cashier', $branch);
        $category = FeeCategory::create([
            'branch_id' => $branch->id,
            'name' => 'Library Fee',
            'code' => 'LIB',
            'status' => 'active',
        ]);
        $year = AcademicYear::create([
            'branch_id' => $branch->id,
            'name' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
            'status' => 'active',
        ]);
        $grade = Grade::create([
            'branch_id' => $branch->id,
            'name' => 'Grade 2',
            'code' => 'G2',
            'level' => 2,
            'status' => 'active',
        ]);

        $response = $this->actingAs($accountant)->post(route('fee-structures.store'), [
            'branch_id' => $branch->id,
            'fee_category_id' => $category->id,
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'amount' => '150.50',
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('fee_structures', [
            'branch_id' => $branch->id,
            'fee_category_id' => $category->id,
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'amount' => '150.50',
        ]);
    }

    public function test_duplicate_fee_structure_combination_is_rejected(): void
    {
        $branch = $this->makeBranch('East Campus', 'EAST');
        $admin = $this->makeUser('school-administrator', $branch);
        $category = FeeCategory::create([
            'branch_id' => $branch->id,
            'name' => 'Lab Fee',
            'code' => 'LAB',
            'status' => 'active',
        ]);
        $year = AcademicYear::create([
            'branch_id' => $branch->id,
            'name' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
            'status' => 'active',
        ]);
        $grade = Grade::create([
            'branch_id' => $branch->id,
            'name' => 'Grade 3',
            'code' => 'G3',
            'level' => 3,
            'status' => 'active',
        ]);

        FeeStructure::create([
            'branch_id' => $branch->id,
            'fee_category_id' => $category->id,
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'amount' => '300.00',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->post(route('fee-structures.store'), [
            'branch_id' => $branch->id,
            'fee_category_id' => $category->id,
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'amount' => '400.00',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('fee_category_id');
    }

    public function test_authorized_user_can_update_fee_structure(): void
    {
        $branch = $this->makeBranch('South Campus', 'SOUTH');
        $admin = $this->makeUser('school-administrator', $branch);
        $category = FeeCategory::create([
            'branch_id' => $branch->id,
            'name' => 'Tuition Fee',
            'code' => 'TUI',
            'status' => 'active',
        ]);
        $year = AcademicYear::create([
            'branch_id' => $branch->id,
            'name' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
            'status' => 'active',
        ]);
        $grade = Grade::create([
            'branch_id' => $branch->id,
            'name' => 'Grade 4',
            'code' => 'G4',
            'level' => 4,
            'status' => 'active',
        ]);

        $structure = FeeStructure::create([
            'branch_id' => $branch->id,
            'fee_category_id' => $category->id,
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'amount' => '800.00',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->put(route('fee-structures.update', $structure), [
            'fee_category_id' => $category->id,
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'amount' => '850.00',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('fee-structures.show', $structure));
        $this->assertDatabaseHas('fee_structures', [
            'id' => $structure->id,
            'amount' => '850.00',
        ]);
    }

    public function test_authorized_user_can_delete_fee_structure(): void
    {
        $branch = $this->makeBranch('West Campus', 'WEST');
        $admin = $this->makeUser('school-administrator', $branch);
        $category = FeeCategory::create([
            'branch_id' => $branch->id,
            'name' => 'Music Fee',
            'code' => 'MUS',
            'status' => 'active',
        ]);
        $year = AcademicYear::create([
            'branch_id' => $branch->id,
            'name' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
            'status' => 'active',
        ]);
        $grade = Grade::create([
            'branch_id' => $branch->id,
            'name' => 'Grade 5',
            'code' => 'G5',
            'level' => 5,
            'status' => 'active',
        ]);

        $structure = FeeStructure::create([
            'branch_id' => $branch->id,
            'fee_category_id' => $category->id,
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'amount' => '200.00',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->delete(route('fee-structures.destroy', $structure));

        $response->assertRedirect(route('fee-structures.index'));
        $this->assertDatabaseMissing('fee_structures', [
            'id' => $structure->id,
        ]);
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
