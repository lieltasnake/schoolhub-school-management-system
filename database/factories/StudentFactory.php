<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'admission_number' => 'STD-' . fake()->unique()->numerify('#####'),
            'first_name' => fake()->firstName(),
            'middle_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'gender' => fake()->randomElement(['male', 'female']),
            'date_of_birth' => fake()->date('Y-m-d', '-7 years'),
            'admission_date' => fake()->date('Y-m-d', 'now'),
            'blood_group' => fake()->randomElement(['A+', 'B+', 'O+', 'AB+', 'A-', 'B-', 'O-', 'AB-']),
            'religion' => fake()->randomElement(['Christianity', 'Islam', 'Other']),
            'national_id' => fake()->optional()->numerify('ID-########'),
            'email' => fake()->optional()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'emergency_contact_name' => fake()->name(),
            'emergency_contact_phone' => fake()->phoneNumber(),
            'medical_notes' => null,
            'status' => 'active',
        ];
    }
}
