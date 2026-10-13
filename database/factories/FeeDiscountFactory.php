<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\FeeDiscount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FeeDiscount>
 */
class FeeDiscountFactory extends Factory
{
    protected $model = FeeDiscount::class;

    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'name' => fake()->unique()->words(2, true) . ' Discount',
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'type' => 'percentage',
            'value' => fake()->randomFloat(2, 5, 25),
            'description' => fake()->sentence(),
            'status' => 'active',
        ];
    }
}
