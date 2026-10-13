<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Grade;
use App\Models\Guardian;
use App\Models\Role;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_administrator_can_view_students_list(): void
    {
        $admin = $this->makeUser('school-administrator');
        $branch = $this->makeBranch('Central Campus', 'CENTRAL');

        $student = Student::factory()->create([
            'branch_id' => $branch->id,
            'first_name' => 'Sara',
            'last_name' => 'Bekele',
            'admission_number' => 'ADM-2026-0001',
        ]);

        $this->actingAs($admin)
            ->get(route('students.index'))
            ->assertOk()
            ->assertSee($student->full_name)
            ->assertSee('ADM-2026-0001');
    }

    public function test_authorized_administrator_can_register_a_student_with_guardian_and_enrollment(): void
    {
        $admin = $this->makeUser('school-administrator');
        $branch = $this->makeBranch('Central Campus', 'CENTRAL');
        $year = $branch->academicYears()->create([
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
        $section = $grade->sections()->create([
            'academic_year_id' => $year->id,
            'name' => 'Section A',
            'capacity' => 30,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->post(route('students.store'), [
            'branch_id' => $branch->id,
            'first_name' => 'Dawit',
            'middle_name' => 'Haile',
            'last_name' => 'Tadesse',
            'gender' => 'male',
            'date_of_birth' => '2019-03-15',
            'admission_date' => '2026-09-01',
            'blood_group' => 'O+',
            'city' => 'Addis Ababa',
            'status' => 'active',
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'section_id' => $section->id,
            'guardian_first_name' => 'Haile',
            'guardian_last_name' => 'Tadesse',
            'guardian_phone' => '+251911223344',
            'guardian_relationship' => 'father',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('students', [
            'branch_id' => $branch->id,
            'first_name' => 'Dawit',
            'last_name' => 'Tadesse',
        ]);

        $this->assertDatabaseHas('guardians', [
            'first_name' => 'Haile',
            'last_name' => 'Tadesse',
            'phone' => '+251911223344',
        ]);

        $this->assertDatabaseHas('student_enrollments', [
            'grade_id' => $grade->id,
            'section_id' => $section->id,
            'academic_year_id' => $year->id,
        ]);
    }

    public function test_admission_number_must_be_unique(): void
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

        Student::factory()->create([
            'branch_id' => $branch->id,
            'admission_number' => 'STD-DUPLICATE',
        ]);

        $response = $this->actingAs($admin)->from(route('students.create'))->post(route('students.store'), [
            'branch_id' => $branch->id,
            'admission_number' => 'STD-DUPLICATE',
            'first_name' => 'Abebe',
            'last_name' => 'Bikila',
            'gender' => 'male',
            'date_of_birth' => '2019-01-01',
            'admission_date' => '2026-09-01',
            'status' => 'active',
            'grade_id' => $grade->id,
            'guardian_first_name' => 'Bikila',
            'guardian_last_name' => 'Father',
            'guardian_phone' => '0911000000',
        ]);

        $response->assertRedirect(route('students.create'))
            ->assertSessionHasErrors('admission_number');
    }

    public function test_student_grade_must_belong_to_selected_branch(): void
    {
        $admin = $this->makeUser('school-administrator');
        $branchA = $this->makeBranch('Campus A', 'CAMPA');
        $branchB = $this->makeBranch('Campus B', 'CAMPB');

        $gradeB = Grade::create([
            'branch_id' => $branchB->id,
            'name' => 'Grade 1',
            'code' => 'G1',
            'level' => 1,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->from(route('students.create'))->post(route('students.store'), [
            'branch_id' => $branchA->id,
            'first_name' => 'Abebe',
            'last_name' => 'Bikila',
            'gender' => 'male',
            'date_of_birth' => '2019-01-01',
            'admission_date' => '2026-09-01',
            'status' => 'active',
            'grade_id' => $gradeB->id,
            'guardian_first_name' => 'Bikila',
            'guardian_last_name' => 'Father',
            'guardian_phone' => '0911000000',
        ]);

        $response->assertRedirect(route('students.create'))
            ->assertSessionHasErrors('grade_id');
        $this->assertDatabaseMissing('students', ['first_name' => 'Abebe']);
    }

    public function test_branch_administrator_cannot_view_or_create_students_for_other_branches(): void
    {
        $branchA = $this->makeBranch('Campus A', 'CAMPA');
        $branchB = $this->makeBranch('Campus B', 'CAMPB');
        $branchAdmin = $this->makeUser('branch-administrator', $branchA);

        $studentB = Student::factory()->create([
            'branch_id' => $branchB->id,
            'first_name' => 'Student',
            'last_name' => 'Other',
        ]);

        $this->actingAs($branchAdmin)
            ->get(route('students.show', $studentB))
            ->assertForbidden();

        $gradeB = Grade::create([
            'branch_id' => $branchB->id,
            'name' => 'Grade 1',
            'code' => 'G1',
            'level' => 1,
            'status' => 'active',
        ]);

        $response = $this->actingAs($branchAdmin)->post(route('students.store'), [
            'branch_id' => $branchB->id,
            'first_name' => 'Forbidden',
            'last_name' => 'Student',
            'gender' => 'male',
            'date_of_birth' => '2019-01-01',
            'admission_date' => '2026-09-01',
            'status' => 'active',
            'grade_id' => $gradeB->id,
            'guardian_first_name' => 'Parent',
            'guardian_last_name' => 'One',
            'guardian_phone' => '0911000000',
        ]);

        $response->assertForbidden();
    }

    public function test_authorized_administrator_can_view_student_profile(): void
    {
        $admin = $this->makeUser('school-administrator');
        $branch = $this->makeBranch('Central Campus', 'CENTRAL');

        $student = Student::factory()->create([
            'branch_id' => $branch->id,
            'first_name' => 'Helen',
            'last_name' => 'Mekonnen',
            'admission_number' => 'ADM-2026-5555',
        ]);

        $this->actingAs($admin)
            ->get(route('students.show', $student))
            ->assertOk()
            ->assertSee($student->full_name)
            ->assertSee('ADM-2026-5555');
    }

    public function test_authorized_administrator_can_update_student_profile(): void
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

        $student = Student::factory()->create([
            'branch_id' => $branch->id,
            'first_name' => 'Helen',
            'last_name' => 'Mekonnen',
        ]);

        $response = $this->actingAs($admin)->put(route('students.update', $student), [
            'first_name' => 'Helena',
            'last_name' => 'Mekonnen Updated',
            'gender' => 'female',
            'date_of_birth' => '2019-05-10',
            'admission_date' => '2026-09-01',
            'status' => 'active',
            'grade_id' => $grade->id,
        ]);

        $response->assertRedirect(route('students.show', $student));

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'first_name' => 'Helena',
            'last_name' => 'Mekonnen Updated',
        ]);
    }

    public function test_authorized_administrator_can_toggle_student_status(): void
    {
        $admin = $this->makeUser('school-administrator');
        $branch = $this->makeBranch('Central Campus', 'CENTRAL');

        $student = Student::factory()->create([
            'branch_id' => $branch->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->post(route('students.toggle-status', $student));
        $response->assertRedirect();

        $this->assertEquals('inactive', $student->fresh()->status);

        $this->actingAs($admin)->post(route('students.toggle-status', $student));
        $this->assertEquals('active', $student->fresh()->status);
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
