<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\FeeCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FeeCategory>
 */
class FeeCategoryFactory extends Factory
{
    protected $model = FeeCategory::class;

    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'name' => fake()->unique()->words(2, true),
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'description' => fake()->sentence(),
            'is_mandatory' => true,
            'status' => 'active',
        ];
    }
}
