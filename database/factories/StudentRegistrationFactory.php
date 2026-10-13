<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Grade;
use App\Models\StudentRegistration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentRegistration>
 */
class StudentRegistrationFactory extends Factory
{
    protected $model = StudentRegistration::class;

    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'grade_id' => Grade::factory(),
            'registration_number' => 'REG-' . fake()->unique()->numerify('#####'),
            'first_name' => fake()->firstName(),
            'middle_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'gender' => fake()->randomElement(['male', 'female']),
            'date_of_birth' => fake()->date('Y-m-d', '-7 years'),
            'registration_date' => now()->toDateString(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'guardian_name' => fake()->name(),
            'guardian_phone' => fake()->phoneNumber(),
            'guardian_email' => fake()->safeEmail(),
            'guardian_relationship' => 'Parent',
            'status' => 'pending',
            'application_fee_paid' => true,
        ];
    }
}
