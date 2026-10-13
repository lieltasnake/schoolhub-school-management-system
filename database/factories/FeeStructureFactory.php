<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use App\Models\Grade;
use App\Models\Term;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FeeStructure>
 */
class FeeStructureFactory extends Factory
{
    protected $model = FeeStructure::class;

    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'fee_category_id' => FeeCategory::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'grade_id' => Grade::factory(),
            'term_id' => null,
            'amount' => fake()->randomFloat(2, 50, 2500),
            'description' => fake()->sentence(),
            'status' => 'active',
        ];
    }
}
