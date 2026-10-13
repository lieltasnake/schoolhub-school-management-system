<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'receipt_number' => 'REC-' . strtoupper(fake()->unique()->bothify('###-????')),
            'amount' => fake()->randomFloat(2, 50, 500),
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
            'reference_number' => fake()->bothify('REF-#####'),
            'notes' => fake()->sentence(),
            'status' => 'confirmed',
        ];
    }
}
