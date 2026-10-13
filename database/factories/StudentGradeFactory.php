<?php

namespace Database\Factories;

use App\Models\Assessment;
use App\Models\Student;
use App\Models\StudentGrade;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentGrade>
 */
class StudentGradeFactory extends Factory
{
    protected $model = StudentGrade::class;

    public function definition(): array
    {
        $score = fake()->randomFloat(2, 40, 98);
        return [
            'assessment_id' => Assessment::factory(),
            'student_id' => Student::factory(),
            'score' => $score,
            'percentage' => $score,
            'letter_grade' => $score >= 80 ? 'A' : ($score >= 65 ? 'B' : ($score >= 50 ? 'C' : 'F')),
            'grade_point' => $score >= 80 ? 4.0 : ($score >= 65 ? 3.0 : ($score >= 50 ? 2.0 : 0.0)),
            'status' => 'present',
            'remarks' => fake()->optional()->sentence(),
        ];
    }
}
