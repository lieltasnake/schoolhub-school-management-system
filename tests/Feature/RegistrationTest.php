<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Grade;
use App\Models\RegistrationSetup;
use App\Models\Role;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentRegistration;
use App\Models\StudentType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_registrations_list(): void
    {
        $admin = $this->makeUser('school-administrator');
        $branch = $this->makeBranch('Main Campus', 'MAIN');
        $year = $this->makeAcademicYear($branch);
        $grade = $this->makeGrade($branch);

        $reg = StudentRegistration::factory()->create([
            'branch_id' => $branch->id,
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'first_name' => 'John',
            'middle_name' => null,
            'last_name' => 'Doe',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->get(route('registrations.index'))
            ->assertOk()
            ->assertSee('John Doe')
            ->assertSee($reg->registration_number);
    }

    public function test_admin_can_create_a_registration_application(): void
    {
        $admin = $this->makeUser('school-administrator');
        $branch = $this->makeBranch('Main Campus', 'MAIN');
        $year = $this->makeAcademicYear($branch);
        $grade = $this->makeGrade($branch);
        $type = StudentType::factory()->create(['branch_id' => $branch->id]);

        $response = $this->actingAs($admin)->post(route('registrations.store'), [
            'branch_id' => $branch->id,
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'student_type_id' => $type->id,
            'first_name' => 'Almaz',
            'last_name' => 'Tesfaye',
            'gender' => 'female',
            'date_of_birth' => '2018-04-12',
            'registration_date' => '2026-09-01',
            'guardian_name' => 'Tesfaye Alemu',
            'guardian_phone' => '+251911223344',
            'guardian_relationship' => 'Father',
            'status' => 'pending',
            'application_fee_paid' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('student_registrations', [
            'first_name' => 'Almaz',
            'last_name' => 'Tesfaye',
            'guardian_name' => 'Tesfaye Alemu',
            'status' => 'pending',
        ]);
    }

    public function test_admin_can_approve_application(): void
    {
        $admin = $this->makeUser('school-administrator');
        $branch = $this->makeBranch('Main Campus', 'MAIN');
        $year = $this->makeAcademicYear($branch);
        $grade = $this->makeGrade($branch);

        $reg = StudentRegistration::factory()->create([
            'branch_id' => $branch->id,
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->post(route('registrations.approve', $reg))
            ->assertRedirect();

        $this->assertEquals('approved', $reg->fresh()->status);
    }

    public function test_admin_can_promote_approved_registrations_to_students(): void
    {
        $admin = $this->makeUser('school-administrator');
        $branch = $this->makeBranch('Main Campus', 'MAIN');
        $year = $this->makeAcademicYear($branch);
        $grade = $this->makeGrade($branch);
        $section = $grade->sections()->create([
            'academic_year_id' => $year->id,
            'name' => 'Section A',
            'capacity' => 30,
        ]);

        $reg = StudentRegistration::factory()->create([
            'branch_id' => $branch->id,
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'first_name' => 'Dawit',
            'last_name' => 'Haile',
            'guardian_name' => 'Haile Selassie',
            'guardian_phone' => '+251922334455',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->post(route('registrations.promotion.promote'), [
            'registration_ids' => [$reg->id],
            'section_id' => $section->id,
        ]);

        $response->assertRedirect(route('students.index'));

        $this->assertEquals('enrolled', $reg->fresh()->status);
        $this->assertNotNull($reg->fresh()->converted_student_id);

        $this->assertDatabaseHas('students', [
            'first_name' => 'Dawit',
            'last_name' => 'Haile',
            'branch_id' => $branch->id,
        ]);

        $student = $reg->fresh()->convertedStudent;
        $this->assertDatabaseHas('student_enrollments', [
            'student_id' => $student->id,
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'section_id' => $section->id,
            'status' => 'active',
        ]);
    }

    public function test_admin_can_manage_registration_setup(): void
    {
        $admin = $this->makeUser('school-administrator');
        $branch = $this->makeBranch('Main Campus', 'MAIN');
        $year = $this->makeAcademicYear($branch);

        $response = $this->actingAs($admin)->post(route('registrations.setup.store'), [
            'branch_id' => $branch->id,
            'academic_year_id' => $year->id,
            'title' => 'Fall 2026 Admissions',
            'start_date' => '2026-06-01',
            'end_date' => '2026-08-31',
            'application_fee' => 75.00,
            'capacity' => 120,
            'status' => 'open',
            'description' => 'Open admission campaign',
        ]);

        $response->assertRedirect(route('registrations.setup'));

        $this->assertDatabaseHas('registration_setups', [
            'title' => 'Fall 2026 Admissions',
            'status' => 'open',
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

    private function makeAcademicYear(Branch $branch): AcademicYear
    {
        return $branch->academicYears()->create([
            'name' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
            'status' => 'active',
        ]);
    }

    private function makeGrade(Branch $branch): Grade
    {
        return Grade::create([
            'branch_id' => $branch->id,
            'name' => 'Grade 1',
            'code' => 'G1',
            'level' => 1,
            'status' => 'active',
        ]);
    }
}
