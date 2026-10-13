<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentEnrollment>
 */
class StudentEnrollmentFactory extends Factory
{
    protected $model = StudentEnrollment::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'grade_id' => Grade::factory(),
            'section_id' => Section::factory(),
            'roll_number' => (string) fake()->numberBetween(1, 60),
            'enrolled_at' => now()->toDateString(),
            'status' => 'active',
        ];
    }
}
