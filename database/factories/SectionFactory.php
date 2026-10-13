<?php

namespace Database\Factories;

use App\Models\Grade;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Section>
 */
class SectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'grade_id' => Grade::factory(),
            'academic_year_id' => null,
            'name' => 'Section '.fake()->unique()->lexify('?'),
            'code' => fake()->unique()->lexify('SEC-??'),
            'capacity' => 40,
            'room_number' => 'Room '.fake()->numberBetween(101, 399),
            'status' => 'active',
        ];
    }
}
