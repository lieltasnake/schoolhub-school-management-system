<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Grade;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Grade>
 */
class GradeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $level = fake()->numberBetween(1, 12);

        return [
            'branch_id' => Branch::factory(),
            'name' => 'Grade '.$level.' '.fake()->unique()->word(),
            'code' => 'G'.$level.'-'.fake()->unique()->lexify('???'),
            'level' => $level,
            'description' => fake()->sentence(),
            'status' => 'active',
        ];
    }
}
