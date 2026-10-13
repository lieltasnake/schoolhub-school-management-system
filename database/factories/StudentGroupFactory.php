<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\StudentGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentGroup>
 */
class StudentGroupFactory extends Factory
{
    protected $model = StudentGroup::class;

    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'name' => fake()->unique()->words(2, true),
            'code' => strtoupper(fake()->unique()->lexify('GRP-???')),
            'type' => fake()->randomElement(['club', 'house', 'sports', 'transport']),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
