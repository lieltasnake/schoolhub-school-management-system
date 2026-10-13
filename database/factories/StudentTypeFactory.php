<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\StudentType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentType>
 */
class StudentTypeFactory extends Factory
{
    protected $model = StudentType::class;

    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'name' => fake()->unique()->words(2, true),
            'code' => strtoupper(fake()->unique()->lexify('ST-???')),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
