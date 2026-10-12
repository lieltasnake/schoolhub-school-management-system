<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Term;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Term>
 */
class TermFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'academic_year_id' => AcademicYear::factory(),
            'name' => fake()->unique()->randomElement(['Term 1', 'Term 2', 'Term 3']),
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
            'status' => 'planned',
        ];
    }

    public function withinAcademicYear(AcademicYear $academicYear): static
    {
        $startDate = $academicYear->start_date->copy();
        $endDate = $startDate->copy()->addMonths(2);

        if ($endDate->greaterThan($academicYear->end_date)) {
            $endDate = $academicYear->end_date->copy();
        }

        return $this->for($academicYear)->state([
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
        ]);
    }
}
