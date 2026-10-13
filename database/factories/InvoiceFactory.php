<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Invoice;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $total = fake()->randomFloat(2, 200, 1500);

        return [
            'branch_id' => Branch::factory(),
            'student_id' => Student::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'term_id' => null,
            'invoice_number' => 'INV-' . strtoupper(fake()->unique()->bothify('###-????')),
            'total_amount' => $total,
            'discount_amount' => 0.00,
            'paid_amount' => 0.00,
            'balance' => $total,
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => 'issued',
            'notes' => fake()->sentence(),
            'issued_at' => now(),
        ];
    }
}
