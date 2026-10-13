<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\AssessmentType;
use App\Models\Branch;
use App\Models\Grade;
use App\Models\GradeScale;
use App\Models\Role;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentGrade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentExamsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_assessment_types(): void
    {
        $admin = $this->makeUser('school-administrator');
        $branch = $this->makeBranch('North Campus', 'NORTH');

        // Create
        $response = $this->actingAs($admin)->post(route('assessment-types.store'), [
            'branch_id' => $branch->id,
            'name' => 'Midterm Exam',
            'code' => 'MIDTERM',
            'weight_percentage' => 30.00,
            'description' => 'First semester midterm exam',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('assessment-types.index'));
        $this->assertDatabaseHas('assessment_types', ['code' => 'MIDTERM']);

        $type = AssessmentType::where('code', 'MIDTERM')->first();

        // Update
        $this->actingAs($admin)->put(route('assessment-types.update', $type), [
            'name' => 'Midterm Exam Updated',
            'code' => 'MIDTERM',
            'weight_percentage' => 35.00,
            'description' => 'Updated weight',
            'is_active' => true,
        ])->assertRedirect();

        $this->assertEquals(35.00, (float) $type->fresh()->weight_percentage);
    }

    public function test_admin_can_manage_grade_scales_with_tiers(): void
    {
        $admin = $this->makeUser('school-administrator');
        $branch = $this->makeBranch('North Campus', 'NORTH');

        $response = $this->actingAs($admin)->post(route('grade-scales.store'), [
            'branch_id' => $branch->id,
            'name' => 'Letter Grade Scale',
            'code' => 'LETTER-STD',
            'description' => 'Standard A-F Scale',
            'is_default' => true,
            'status' => 'active',
            'items' => [
                ['letter_grade' => 'A', 'min_percentage' => 80, 'max_percentage' => 100, 'grade_point' => 4.0, 'remarks' => 'Excellent', 'is_pass' => true],
                ['letter_grade' => 'B', 'min_percentage' => 60, 'max_percentage' => 79.99, 'grade_point' => 3.0, 'remarks' => 'Good', 'is_pass' => true],
                ['letter_grade' => 'F', 'min_percentage' => 0, 'max_percentage' => 59.99, 'grade_point' => 0.0, 'remarks' => 'Fail', 'is_pass' => false],
            ],
        ]);

        $response->assertRedirect(route('grade-scales.index'));
        $this->assertDatabaseHas('grade_scales', ['code' => 'LETTER-STD']);
        $this->assertDatabaseHas('grade_scale_items', ['letter_grade' => 'A', 'grade_point' => 4.0]);
    }

    public function test_admin_can_create_and_view_assessment_setup(): void
    {
        $admin = $this->makeUser('school-administrator');
        $branch = $this->makeBranch('North Campus', 'NORTH');
        $year = $this->makeAcademicYear($branch);
        $grade = $this->makeGrade($branch);
        $type = AssessmentType::factory()->create(['branch_id' => $branch->id]);

        $response = $this->actingAs($admin)->post(route('assessments.store'), [
            'branch_id' => $branch->id,
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'assessment_type_id' => $type->id,
            'title' => 'Math Midterm Exam',
            'subject_name' => 'Mathematics',
            'max_score' => 100,
            'pass_mark' => 50,
            'weight_percentage' => 25,
            'assessment_date' => '2026-10-20',
            'status' => 'scheduled',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('assessments', [
            'title' => 'Math Midterm Exam',
            'subject_name' => 'Mathematics',
        ]);

        $assessment = Assessment::where('title', 'Math Midterm Exam')->first();
        $this->actingAs($admin)->get(route('assessments.show', $assessment))
            ->assertOk()
            ->assertSee('Math Midterm Exam');
    }

    public function test_teacher_can_enter_student_grades_and_auto_calculate_results(): void
    {
        $branch = $this->makeBranch('North Campus', 'NORTH');
        $teacher = $this->makeUser('teacher', $branch);
        $year = $this->makeAcademicYear($branch);
        $grade = $this->makeGrade($branch);
        $type = AssessmentType::factory()->create(['branch_id' => $branch->id]);
        $scale = GradeScale::factory()->create(['branch_id' => $branch->id]);

        $assessment = Assessment::create([
            'branch_id' => $branch->id,
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'assessment_type_id' => $type->id,
            'grade_scale_id' => $scale->id,
            'title' => 'Biology Quiz 1',
            'subject_name' => 'Biology',
            'max_score' => 100,
            'pass_mark' => 50,
            'weight_percentage' => 10,
            'assessment_date' => '2026-10-15',
            'status' => 'scheduled',
        ]);

        $student1 = Student::factory()->create(['branch_id' => $branch->id]);
        StudentEnrollment::create([
            'student_id' => $student1->id,
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'enrolled_at' => '2026-09-01',
            'status' => 'active',
        ]);

        $student2 = Student::factory()->create(['branch_id' => $branch->id]);
        StudentEnrollment::create([
            'student_id' => $student2->id,
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'enrolled_at' => '2026-09-01',
            'status' => 'active',
        ]);

        // Access Grade Entry sheet
        $this->actingAs($teacher)
            ->get(route('grade-entries.edit', $assessment))
            ->assertOk()
            ->assertSee($student1->full_name)
            ->assertSee($student2->full_name);

        // Submit grades
        $response = $this->actingAs($teacher)->put(route('grade-entries.update', $assessment), [
            'grades' => [
                [
                    'student_id' => $student1->id,
                    'score' => 88.5,
                    'status' => 'present',
                    'remarks' => 'Great performance',
                ],
                [
                    'student_id' => $student2->id,
                    'score' => 45.0,
                    'status' => 'present',
                    'remarks' => 'Needs extra support',
                ],
            ],
        ]);

        $response->assertRedirect(route('grade-entries.show', $assessment));

        // Check student 1 grade: 88.5% -> Letter Grade A
        $grade1 = StudentGrade::where('assessment_id', $assessment->id)->where('student_id', $student1->id)->first();
        $this->assertNotNull($grade1);
        $this->assertEquals(88.5, (float) $grade1->score);
        $this->assertEquals(88.5, (float) $grade1->percentage);
        $this->assertEquals('A', $grade1->letter_grade);
        $this->assertEquals(4.0, (float) $grade1->grade_point);

        // Check student 2 grade: 45.0% -> Letter Grade F
        $grade2 = StudentGrade::where('assessment_id', $assessment->id)->where('student_id', $student2->id)->first();
        $this->assertNotNull($grade2);
        $this->assertEquals(45.0, (float) $grade2->score);
        $this->assertEquals('F', $grade2->letter_grade);

        // View Marksheet report
        $this->actingAs($teacher)
            ->get(route('grade-entries.show', $assessment))
            ->assertOk()
            ->assertSee('Class marksheet')
            ->assertSee('88.50')
            ->assertSee('45.00');
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
