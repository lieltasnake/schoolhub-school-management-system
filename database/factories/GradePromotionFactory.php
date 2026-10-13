<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Grade;
use App\Models\GradePromotion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GradePromotion>
 */
class GradePromotionFactory extends Factory
{
    protected $model = GradePromotion::class;

    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'from_academic_year_id' => AcademicYear::factory(),
            'to_academic_year_id' => AcademicYear::factory(),
            'from_grade_id' => Grade::factory(),
            'to_grade_id' => Grade::factory(),
            'processed_by' => User::factory(),
            'total_students' => 20,
            'promoted_count' => 18,
            'repeated_count' => 2,
            'graduated_count' => 0,
        ];
    }
}
