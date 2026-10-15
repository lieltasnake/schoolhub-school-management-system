<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Grade;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentTransition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentTransitionsTest extends TestCase
{
    use RefreshDatabase;

    private function makeUserWithRole(string $roleSlug = 'school-administrator'): User
    {
        $branch = Branch::create([
            'name' => 'Main Campus',
            'code' => 'MAIN',
            'status' => 'active',
        ]);

        $role = \App\Models\Role::firstOrCreate(
            ['slug' => $roleSlug],
            ['name' => ucwords(str_replace('-', ' ', $roleSlug)), 'is_system' => true]
        );

        return User::factory()->create([
            'branch_id' => $branch->id,
            'role_id' => $role->id,
            'status' => 'active',
        ]);
    }

    public function test_admin_can_access_student_transitions_page(): void
    {
        $admin = $this->makeUserWithRole('school-administrator');

        $response = $this->actingAs($admin)->get(route('student-transitions.index'));

        $response->assertOk();
        $response->assertSee('Student Transitions');
        $response->assertSee('Withdraw Student');
        $response->assertSee('Graduate Students');
        $response->assertSee('Repeating Feature');
    }

    public function test_admin_can_withdraw_a_student(): void
    {
        $admin = $this->makeUserWithRole('school-administrator');
        $branch = $admin->branch;

        $year = AcademicYear::create([
            'branch_id' => $branch->id,
            'name' => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
            'status' => 'active',
        ]);

        $grade = Grade::create([
            'branch_id' => $branch->id,
            'name' => 'Grade 10',
            'code' => 'G10',
            'level' => 10,
            'status' => 'active',
        ]);

        $student = Student::factory()->create(['branch_id' => $branch->id, 'status' => 'active']);
        $enrollment = StudentEnrollment::create([
            'student_id' => $student->id,
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'enrolled_at' => '2025-09-01',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->post(route('student-transitions.withdraw'), [
            'branch_id' => $branch->id,
            'student_id' => $student->id,
            'transition_date' => '2026-03-15',
            'reason' => 'Transferred to another school',
            'notes' => 'Family moving overseas',
        ]);

        $response->assertRedirect(route('student-transitions.index', ['tab' => 'withdraw', 'branch_id' => $branch->id]));

        $this->assertEquals('withdrawn', $student->fresh()->status);
        $this->assertEquals('completed', $enrollment->fresh()->status);

        $this->assertDatabaseHas('student_transitions', [
            'student_id' => $student->id,
            'transition_type' => 'withdrawal',
            'reason' => 'Transferred to another school',
        ]);
    }

    public function test_admin_can_readmit_a_withdrawn_student(): void
    {
        $admin = $this->makeUserWithRole('school-administrator');
        $branch = $admin->branch;

        $year = AcademicYear::create([
            'branch_id' => $branch->id,
            'name' => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
            'status' => 'active',
        ]);

        $grade = Grade::create([
            'branch_id' => $branch->id,
            'name' => 'Grade 10',
            'code' => 'G10',
            'level' => 10,
            'status' => 'active',
        ]);

        $student = Student::factory()->create(['branch_id' => $branch->id, 'status' => 'withdrawn']);

        $response = $this->actingAs($admin)->post(route('student-transitions.readmit'), [
            'student_id' => $student->id,
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'transition_date' => '2026-04-01',
            'notes' => 'Returned and cleared all registration fees',
        ]);

        $response->assertRedirect(route('student-transitions.index', ['tab' => 'withdraw', 'branch_id' => $branch->id]));

        $this->assertEquals('active', $student->fresh()->status);
        $this->assertDatabaseHas('student_enrollments', [
            'student_id' => $student->id,
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('student_transitions', [
            'student_id' => $student->id,
            'transition_type' => 'readmission',
        ]);
    }

    public function test_admin_can_graduate_students(): void
    {
        $admin = $this->makeUserWithRole('school-administrator');
        $branch = $admin->branch;

        $year = AcademicYear::create([
            'branch_id' => $branch->id,
            'name' => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
            'status' => 'active',
        ]);

        $grade12 = Grade::create([
            'branch_id' => $branch->id,
            'name' => 'Grade 12',
            'code' => 'G12',
            'level' => 12,
            'status' => 'active',
        ]);

        $student1 = Student::factory()->create(['branch_id' => $branch->id, 'status' => 'active']);
        $student2 = Student::factory()->create(['branch_id' => $branch->id, 'status' => 'active']);

        $enrollment1 = StudentEnrollment::create([
            'student_id' => $student1->id,
            'academic_year_id' => $year->id,
            'grade_id' => $grade12->id,
            'enrolled_at' => '2025-09-01',
            'status' => 'active',
        ]);

        $enrollment2 = StudentEnrollment::create([
            'student_id' => $student2->id,
            'academic_year_id' => $year->id,
            'grade_id' => $grade12->id,
            'enrolled_at' => '2025-09-01',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->post(route('student-transitions.graduate'), [
            'branch_id' => $branch->id,
            'academic_year_id' => $year->id,
            'grade_id' => $grade12->id,
            'student_ids' => [$student1->id, $student2->id],
            'transition_date' => '2026-06-25',
            'certificate_number' => 'DIPLOMA-2026-BATCH',
            'notes' => 'Valedictory cohort',
        ]);

        $response->assertRedirect(route('student-transitions.index', ['tab' => 'graduate', 'branch_id' => $branch->id]));

        $this->assertEquals('graduated', $student1->fresh()->status);
        $this->assertEquals('graduated', $student2->fresh()->status);
        $this->assertEquals('completed', $enrollment1->fresh()->status);
        $this->assertEquals('completed', $enrollment2->fresh()->status);

        $this->assertDatabaseHas('student_transitions', [
            'student_id' => $student1->id,
            'transition_type' => 'graduation',
            'certificate_number' => 'DIPLOMA-2026-BATCH',
        ]);
    }

    public function test_admin_can_assign_students_to_repeat_grade(): void
    {
        $admin = $this->makeUserWithRole('school-administrator');
        $branch = $admin->branch;

        $fromYear = AcademicYear::create([
            'branch_id' => $branch->id,
            'name' => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
            'status' => 'inactive',
        ]);

        $toYear = AcademicYear::create([
            'branch_id' => $branch->id,
            'name' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
            'status' => 'active',
        ]);

        $grade = Grade::create([
            'branch_id' => $branch->id,
            'name' => 'Grade 8',
            'code' => 'G8',
            'level' => 8,
            'status' => 'active',
        ]);

        $student = Student::factory()->create(['branch_id' => $branch->id, 'status' => 'active']);
        $enrollment = StudentEnrollment::create([
            'student_id' => $student->id,
            'academic_year_id' => $fromYear->id,
            'grade_id' => $grade->id,
            'enrolled_at' => '2025-09-01',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->post(route('student-transitions.repeat'), [
            'branch_id' => $branch->id,
            'from_academic_year_id' => $fromYear->id,
            'to_academic_year_id' => $toYear->id,
            'grade_id' => $grade->id,
            'student_ids' => [$student->id],
            'transition_date' => '2026-09-01',
            'reason' => 'Academic retention / low GPA',
            'notes' => 'Assigned tailored remedial support',
        ]);

        $response->assertRedirect(route('student-transitions.index', ['tab' => 'repeat', 'branch_id' => $branch->id]));

        $this->assertEquals('repeated', $enrollment->fresh()->status);
        $this->assertEquals('active', $student->fresh()->status);

        $this->assertDatabaseHas('student_enrollments', [
            'student_id' => $student->id,
            'academic_year_id' => $toYear->id,
            'grade_id' => $grade->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('student_transitions', [
            'student_id' => $student->id,
            'transition_type' => 'repeat',
            'from_academic_year_id' => $fromYear->id,
            'to_academic_year_id' => $toYear->id,
            'reason' => 'Academic retention / low GPA',
        ]);
    }
}
