<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\AssessmentType;
use App\Models\Branch;
use App\Models\Grade;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Assessment>
 */
class AssessmentFactory extends Factory
{
    protected $model = Assessment::class;

    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'grade_id' => Grade::factory(),
            'assessment_type_id' => AssessmentType::factory(),
            'title' => fake()->words(3, true) . ' Assessment',
            'subject_name' => fake()->randomElement(['Mathematics', 'English', 'Science', 'History', 'Geography']),
            'max_score' => 100.00,
            'pass_mark' => 50.00,
            'weight_percentage' => 100.00,
            'assessment_date' => now()->toDateString(),
            'status' => 'scheduled',
        ];
    }
}
