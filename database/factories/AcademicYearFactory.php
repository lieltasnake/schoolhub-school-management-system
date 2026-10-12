<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcademicYear>
 */
class AcademicYearFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startYear = fake()->numberBetween(2020, 2035);

        return [
            'branch_id' => Branch::factory(),
            'name' => $startYear.'-'.($startYear + 1),
            'start_date' => $startYear.'-09-01',
            'end_date' => ($startYear + 1).'-06-30',
            'status' => 'planned',
        ];
    }
}
