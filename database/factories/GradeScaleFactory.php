<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\GradeScale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GradeScale>
 */
class GradeScaleFactory extends Factory
{
    protected $model = GradeScale::class;

    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'name' => fake()->unique()->words(2, true) . ' Scale',
            'code' => strtoupper(fake()->unique()->lexify('SCALE-???')),
            'description' => fake()->sentence(),
            'is_default' => false,
            'status' => 'active',
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (GradeScale $scale) {
            $tiers = [
                ['letter_grade' => 'A', 'min_percentage' => 80.00, 'max_percentage' => 100.00, 'grade_point' => 4.0, 'remarks' => 'Excellent', 'is_pass' => true],
                ['letter_grade' => 'B', 'min_percentage' => 65.00, 'max_percentage' => 79.99, 'grade_point' => 3.0, 'remarks' => 'Good', 'is_pass' => true],
                ['letter_grade' => 'C', 'min_percentage' => 50.00, 'max_percentage' => 64.99, 'grade_point' => 2.0, 'remarks' => 'Pass', 'is_pass' => true],
                ['letter_grade' => 'F', 'min_percentage' => 0.00, 'max_percentage' => 49.99, 'grade_point' => 0.0, 'remarks' => 'Fail', 'is_pass' => false],
            ];

            foreach ($tiers as $tier) {
                $scale->items()->create($tier);
            }
        });
    }
}
