<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\RegistrationSetup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RegistrationSetup>
 */
class RegistrationSetupFactory extends Factory
{
    protected $model = RegistrationSetup::class;

    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'title' => 'Fall ' . now()->year . ' Admissions',
            'start_date' => now()->startOfYear()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
            'application_fee' => 50.00,
            'capacity' => 100,
            'status' => 'open',
            'description' => fake()->paragraph(),
        ];
    }
}
