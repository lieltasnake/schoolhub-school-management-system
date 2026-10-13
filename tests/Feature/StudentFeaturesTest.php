<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Grade;
use App\Models\Role;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentGroup;
use App\Models\StudentType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_student_enrollments(): void
    {
        $admin = $this->makeUser('school-administrator');
        $branch = $this->makeBranch('Central Campus', 'CENTRAL');
        $year = $this->makeAcademicYear($branch);
        $grade = $this->makeGrade($branch);
        $section = $grade->sections()->create([
            'academic_year_id' => $year->id,
            'name' => 'Section Blue',
            'capacity' => 25,
        ]);
        $student = Student::factory()->create(['branch_id' => $branch->id]);

        // Create enrollment
        $response = $this->actingAs($admin)->post(route('enrollments.store'), [
            'student_id' => $student->id,
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'section_id' => $section->id,
            'roll_number' => '10',
            'enrolled_at' => '2026-09-01',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('enrollments.index'));
        $this->assertDatabaseHas('student_enrollments', [
            'student_id' => $student->id,
            'roll_number' => '10',
            'status' => 'active',
        ]);
    }

    public function test_admin_can_crud_student_types(): void
    {
        $admin = $this->makeUser('school-administrator');
        $branch = $this->makeBranch('Central Campus', 'CENTRAL');

        // Store
        $response = $this->actingAs($admin)->post(route('student-types.store'), [
            'branch_id' => $branch->id,
            'name' => 'Boarding Student',
            'code' => 'BOARD',
            'description' => 'Full-time residential',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('student-types.index'));
        $this->assertDatabaseHas('student_types', ['code' => 'BOARD']);

        $type = StudentType::where('code', 'BOARD')->first();

        // Update
        $this->actingAs($admin)->put(route('student-types.update', $type), [
            'name' => 'Boarding Student Updated',
            'code' => 'BOARD',
            'description' => 'Updated description',
            'is_active' => true,
        ])->assertRedirect(route('student-types.index'));

        $this->assertEquals('Boarding Student Updated', $type->fresh()->name);

        // Delete
        $this->actingAs($admin)->delete(route('student-types.destroy', $type))
            ->assertRedirect(route('student-types.index'));

        $this->assertDatabaseMissing('student_types', ['id' => $type->id]);
    }

    public function test_admin_can_manage_student_groups_and_members(): void
    {
        $admin = $this->makeUser('school-administrator');
        $branch = $this->makeBranch('Central Campus', 'CENTRAL');
        $student = Student::factory()->create(['branch_id' => $branch->id]);

        // Create Group
        $this->actingAs($admin)->post(route('student-groups.store'), [
            'branch_id' => $branch->id,
            'name' => 'Red Lion House',
            'code' => 'RED-LION',
            'type' => 'house',
            'description' => 'House Red',
            'is_active' => true,
        ])->assertRedirect();

        $group = StudentGroup::where('code', 'RED-LION')->first();

        // Add Member
        $this->actingAs($admin)->post(route('student-groups.members.add', $group), [
            'student_id' => $student->id,
            'role' => 'captain',
        ])->assertRedirect();

        $this->assertTrue($group->students()->where('student_id', $student->id)->exists());
        $this->assertEquals('captain', $group->students()->first()->pivot->role);

        // Remove Member
        $this->actingAs($admin)->delete(route('student-groups.members.remove', [$group, $student]))
            ->assertRedirect();

        $this->assertFalse($group->students()->where('student_id', $student->id)->exists());
    }

    public function test_admin_can_execute_batch_grade_promotion(): void
    {
        $admin = $this->makeUser('school-administrator');
        $branch = $this->makeBranch('Central Campus', 'CENTRAL');

        $fromYear = $branch->academicYears()->create([
            'name' => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
            'status' => 'inactive',
        ]);

        $toYear = $branch->academicYears()->create([
            'name' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
            'status' => 'active',
        ]);

        $grade1 = Grade::create([
            'branch_id' => $branch->id,
            'name' => 'Grade 1',
            'code' => 'G1',
            'level' => 1,
            'status' => 'active',
        ]);

        $grade2 = Grade::create([
            'branch_id' => $branch->id,
            'name' => 'Grade 2',
            'code' => 'G2',
            'level' => 2,
            'status' => 'active',
        ]);

        $student1 = Student::factory()->create(['branch_id' => $branch->id]);
        $enrollment1 = StudentEnrollment::create([
            'student_id' => $student1->id,
            'academic_year_id' => $fromYear->id,
            'grade_id' => $grade1->id,
            'enrolled_at' => '2025-09-01',
            'status' => 'active',
        ]);

        $student2 = Student::factory()->create(['branch_id' => $branch->id]);
        $enrollment2 = StudentEnrollment::create([
            'student_id' => $student2->id,
            'academic_year_id' => $fromYear->id,
            'grade_id' => $grade1->id,
            'enrolled_at' => '2025-09-01',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->post(route('grade-promotions.store'), [
            'branch_id' => $branch->id,
            'from_academic_year_id' => $fromYear->id,
            'to_academic_year_id' => $toYear->id,
            'from_grade_id' => $grade1->id,
            'to_grade_id' => $grade2->id,
            'students' => [
                [
                    'student_id' => $student1->id,
                    'enrollment_id' => $enrollment1->id,
                    'action' => 'promote',
                ],
                [
                    'student_id' => $student2->id,
                    'enrollment_id' => $enrollment2->id,
                    'action' => 'repeat',
                ],
            ],
            'notes' => 'Annual batch promotion',
        ]);

        $response->assertRedirect(route('grade-promotions.index'));

        // Check student 1 promoted to grade 2
        $this->assertEquals('promoted', $enrollment1->fresh()->status);
        $this->assertDatabaseHas('student_enrollments', [
            'student_id' => $student1->id,
            'academic_year_id' => $toYear->id,
            'grade_id' => $grade2->id,
            'status' => 'active',
        ]);

        // Check student 2 repeated in grade 1
        $this->assertEquals('repeated', $enrollment2->fresh()->status);
        $this->assertDatabaseHas('student_enrollments', [
            'student_id' => $student2->id,
            'academic_year_id' => $toYear->id,
            'grade_id' => $grade1->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('grade_promotions', [
            'from_academic_year_id' => $fromYear->id,
            'to_academic_year_id' => $toYear->id,
            'promoted_count' => 1,
            'repeated_count' => 1,
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
