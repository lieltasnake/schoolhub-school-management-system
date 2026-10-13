<?php

namespace Database\Factories;

use App\Models\AssessmentType;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssessmentType>
 */
class AssessmentTypeFactory extends Factory
{
    protected $model = AssessmentType::class;

    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'name' => fake()->unique()->words(2, true),
            'code' => strtoupper(fake()->unique()->lexify('AT-???')),
            'weight_percentage' => 25.00,
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
