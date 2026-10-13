<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Grade;
use App\Models\Role;
use App\Models\Section;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradeSectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_administrator_can_view_grades_list(): void
    {
        $admin = $this->makeUser('school-administrator');
        $branch = $this->makeBranch('Central Campus', 'CENTRAL');
        $grade = Grade::create([
            'branch_id' => $branch->id,
            'name' => 'Grade 1',
            'code' => 'G1',
            'level' => 1,
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->get(route('grades.index'))
            ->assertOk()
            ->assertSee('Grade 1')
            ->assertSee('G1');
    }

    public function test_authorized_administrator_can_create_a_grade(): void
    {
        $admin = $this->makeUser('school-administrator');
        $branch = $this->makeBranch('Central Campus', 'CENTRAL');

        $response = $this->actingAs($admin)->post(route('grades.store'), [
            'branch_id' => $branch->id,
            'name' => 'Grade 1',
            'code' => 'G1',
            'level' => 1,
            'description' => 'First grade primary',
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('grades', [
            'branch_id' => $branch->id,
            'name' => 'Grade 1',
            'code' => 'G1',
            'level' => 1,
        ]);
    }

    public function test_grade_name_and_code_must_be_unique_within_branch(): void
    {
        $admin = $this->makeUser('school-administrator');
        $branch = $this->makeBranch('Central Campus', 'CENTRAL');
        Grade::create([
            'branch_id' => $branch->id,
            'name' => 'Grade 1',
            'code' => 'G1',
            'level' => 1,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->from(route('grades.create'))->post(route('grades.store'), [
            'branch_id' => $branch->id,
            'name' => 'Grade 1',
            'code' => 'G1',
            'level' => 1,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('grades.create'))
            ->assertSessionHasErrors(['name', 'code']);
        $this->assertDatabaseCount('grades', 1);
    }

    public function test_different_branches_can_have_the_same_grade_name_and_code(): void
    {
        $admin = $this->makeUser('school-administrator');
        $branchA = $this->makeBranch('Campus A', 'CAMPA');
        $branchB = $this->makeBranch('Campus B', 'CAMPB');

        Grade::create([
            'branch_id' => $branchA->id,
            'name' => 'Grade 1',
            'code' => 'G1',
            'level' => 1,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->post(route('grades.store'), [
            'branch_id' => $branchB->id,
            'name' => 'Grade 1',
            'code' => 'G1',
            'level' => 1,
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('grades', 2);
    }

    public function test_branch_administrator_cannot_view_another_branches_grade(): void
    {
        $branchA = $this->makeBranch('Campus A', 'CAMPA');
        $branchB = $this->makeBranch('Campus B', 'CAMPB');
        $branchAdmin = $this->makeUser('branch-administrator', $branchA);
        $gradeB = Grade::create([
            'branch_id' => $branchB->id,
            'name' => 'Grade 1',
            'code' => 'G1',
            'level' => 1,
            'status' => 'active',
        ]);

        $this->actingAs($branchAdmin)
            ->get(route('grades.show', $gradeB))
            ->assertForbidden();

        $this->actingAs($branchAdmin)
            ->get(route('grades.edit', $gradeB))
            ->assertForbidden();
    }

    public function test_branch_administrator_cannot_create_grade_for_another_branch(): void
    {
        $branchA = $this->makeBranch('Campus A', 'CAMPA');
        $branchB = $this->makeBranch('Campus B', 'CAMPB');
        $branchAdmin = $this->makeUser('branch-administrator', $branchA);

        $response = $this->actingAs($branchAdmin)->post(route('grades.store'), [
            'branch_id' => $branchB->id,
            'name' => 'Grade 1',
            'code' => 'G1',
            'level' => 1,
            'status' => 'active',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('grades', ['branch_id' => $branchB->id]);
    }

    public function test_authorized_administrator_can_update_a_grade(): void
    {
        $admin = $this->makeUser('school-administrator');
        $branch = $this->makeBranch('Central Campus', 'CENTRAL');
        $grade = Grade::create([
            'branch_id' => $branch->id,
            'name' => 'Grade 1',
            'code' => 'G1',
            'level' => 1,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->put(route('grades.update', $grade), [
            'name' => 'First Grade Primary',
            'code' => 'G1-P',
            'level' => 2,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('grades.show', $grade));
        $this->assertDatabaseHas('grades', [
            'id' => $grade->id,
            'name' => 'First Grade Primary',
            'code' => 'G1-P',
            'level' => 2,
        ]);
    }

    public function test_cannot_delete_grade_with_assigned_sections(): void
    {
        $admin = $this->makeUser('school-administrator');
        $branch = $this->makeBranch('Central Campus', 'CENTRAL');
        $grade = Grade::create([
            'branch_id' => $branch->id,
            'name' => 'Grade 1',
            'code' => 'G1',
            'level' => 1,
            'status' => 'active',
        ]);
        $grade->sections()->create([
            'name' => 'Section A',
            'capacity' => 35,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->delete(route('grades.destroy', $grade));

        $response->assertRedirect(route('grades.show', $grade))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('grades', ['id' => $grade->id]);
    }

    public function test_authorized_administrator_can_delete_empty_grade(): void
    {
        $admin = $this->makeUser('school-administrator');
        $branch = $this->makeBranch('Central Campus', 'CENTRAL');
        $grade = Grade::create([
            'branch_id' => $branch->id,
            'name' => 'Grade 1',
            'code' => 'G1',
            'level' => 1,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->delete(route('grades.destroy', $grade));

        $response->assertRedirect(route('grades.index'))
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('grades', ['id' => $grade->id]);
    }

    public function test_authorized_administrator_can_create_a_section_under_a_grade(): void
    {
        $admin = $this->makeUser('school-administrator');
        $branch = $this->makeBranch('Central Campus', 'CENTRAL');
        $grade = Grade::create([
            'branch_id' => $branch->id,
            'name' => 'Grade 1',
            'code' => 'G1',
            'level' => 1,
            'status' => 'active',
        ]);
        $academicYear = $branch->academicYears()->create([
            'name' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->post(route('sections.store'), [
            'grade_id' => $grade->id,
            'academic_year_id' => $academicYear->id,
            'name' => 'Section A',
            'code' => 'G1-A',
            'capacity' => 45,
            'room_number' => 'Room 101',
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('sections', [
            'grade_id' => $grade->id,
            'academic_year_id' => $academicYear->id,
            'name' => 'Section A',
            'code' => 'G1-A',
            'capacity' => 45,
            'room_number' => 'Room 101',
        ]);
    }

    public function test_section_academic_year_must_belong_to_the_same_branch_as_the_grade(): void
    {
        $admin = $this->makeUser('school-administrator');
        $branchA = $this->makeBranch('Campus A', 'CAMPA');
        $branchB = $this->makeBranch('Campus B', 'CAMPB');
        $gradeA = Grade::create([
            'branch_id' => $branchA->id,
            'name' => 'Grade 1',
            'code' => 'G1',
            'level' => 1,
            'status' => 'active',
        ]);
        $yearB = $branchB->academicYears()->create([
            'name' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->from(route('sections.create'))->post(route('sections.store'), [
            'grade_id' => $gradeA->id,
            'academic_year_id' => $yearB->id,
            'name' => 'Section A',
            'capacity' => 30,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('sections.create'))
            ->assertSessionHasErrors('academic_year_id');
        $this->assertDatabaseMissing('sections', ['name' => 'Section A']);
    }

    public function test_section_name_must_be_unique_within_the_same_grade_and_academic_year(): void
    {
        $admin = $this->makeUser('school-administrator');
        $branch = $this->makeBranch('Central Campus', 'CENTRAL');
        $grade = Grade::create([
            'branch_id' => $branch->id,
            'name' => 'Grade 1',
            'code' => 'G1',
            'level' => 1,
            'status' => 'active',
        ]);
        $grade->sections()->create([
            'name' => 'Section A',
            'academic_year_id' => null,
            'capacity' => 40,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->from(route('sections.create'))->post(route('sections.store'), [
            'grade_id' => $grade->id,
            'academic_year_id' => '',
            'name' => 'Section A',
            'capacity' => 40,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('sections.create'))
            ->assertSessionHasErrors('name');
        $this->assertDatabaseCount('sections', 1);
    }

    public function test_teacher_cannot_manage_grades_or_sections(): void
    {
        $teacher = $this->makeUser('teacher');

        $this->actingAs($teacher)->get(route('grades.index'))->assertForbidden();
        $this->actingAs($teacher)->get(route('grades.create'))->assertForbidden();
        $this->actingAs($teacher)->get(route('sections.index'))->assertForbidden();
        $this->actingAs($teacher)->get(route('sections.create'))->assertForbidden();
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
