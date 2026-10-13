<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_payments(): void
    {
        $this->get(route('payments.index'))->assertRedirect(route('login'));
    }

    public function test_authorized_user_can_view_payments_list(): void
    {
        $branch = $this->makeBranch('Central Campus', 'CENTRAL');
        $admin = $this->makeUser('super-administrator');
        $student = $this->makeStudent($branch);
        $year = $this->makeAcademicYear($branch);
        $invoice = $this->makeInvoice($branch, $student, $year, '600.00');

        Payment::create([
            'invoice_id' => $invoice->id,
            'receipt_number' => 'REC-CENTRAL-2026-0001',
            'amount' => '300.00',
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
            'status' => 'confirmed',
        ]);

        $this->actingAs($admin)
            ->get(route('payments.index'))
            ->assertOk()
            ->assertSee('REC-CENTRAL-2026-0001')
            ->assertSee('$300.00')
            ->assertSee($student->first_name);
    }

    public function test_accountant_can_record_payment_and_update_invoice_balance(): void
    {
        $branch = $this->makeBranch('North Campus', 'NORTH');
        $accountant = $this->makeUser('accountant-cashier', $branch);
        $student = $this->makeStudent($branch);
        $year = $this->makeAcademicYear($branch);
        $invoice = $this->makeInvoice($branch, $student, $year, '500.00');

        $response = $this->actingAs($accountant)->post(route('payments.store'), [
            'invoice_id' => $invoice->id,
            'amount' => '200.00',
            'payment_method' => 'bank_transfer',
            'payment_date' => now()->toDateString(),
            'reference_number' => 'TXN-987654',
            'notes' => 'Received via wire transfer',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'amount' => '200.00',
            'payment_method' => 'bank_transfer',
            'reference_number' => 'TXN-987654',
            'status' => 'confirmed',
        ]);

        $invoice->refresh();
        $this->assertEquals('200.00', $invoice->paid_amount);
        $this->assertEquals('300.00', $invoice->balance);
        $this->assertEquals('partially_paid', $invoice->status);
    }

    public function test_full_payment_updates_invoice_status_to_paid(): void
    {
        $branch = $this->makeBranch('East Campus', 'EAST');
        $accountant = $this->makeUser('accountant-cashier', $branch);
        $student = $this->makeStudent($branch);
        $year = $this->makeAcademicYear($branch);
        $invoice = $this->makeInvoice($branch, $student, $year, '400.00');

        $response = $this->actingAs($accountant)->post(route('payments.store'), [
            'invoice_id' => $invoice->id,
            'amount' => '400.00',
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
        ]);

        $response->assertRedirect();
        $invoice->refresh();
        $this->assertEquals('400.00', $invoice->paid_amount);
        $this->assertEquals('0.00', $invoice->balance);
        $this->assertEquals('paid', $invoice->status);
    }

    public function test_overpayment_is_prevented(): void
    {
        $branch = $this->makeBranch('South Campus', 'SOUTH');
        $accountant = $this->makeUser('accountant-cashier', $branch);
        $student = $this->makeStudent($branch);
        $year = $this->makeAcademicYear($branch);
        $invoice = $this->makeInvoice($branch, $student, $year, '100.00');

        $response = $this->actingAs($accountant)->post(route('payments.store'), [
            'invoice_id' => $invoice->id,
            'amount' => '150.00',
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_authorized_user_can_reverse_payment(): void
    {
        $branch = $this->makeBranch('West Campus', 'WEST');
        $admin = $this->makeUser('school-administrator', $branch);
        $student = $this->makeStudent($branch);
        $year = $this->makeAcademicYear($branch);
        $invoice = $this->makeInvoice($branch, $student, $year, '500.00');

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'receipt_number' => 'REC-WEST-2026-0001',
            'amount' => '250.00',
            'payment_method' => 'cheque',
            'payment_date' => now()->toDateString(),
            'status' => 'confirmed',
        ]);
        $invoice->update([
            'paid_amount' => '250.00',
            'balance' => '250.00',
            'status' => 'partially_paid',
        ]);

        $response = $this->actingAs($admin)->post(route('payments.reverse', $payment), [
            'reason' => 'Cheque bounced',
        ]);

        $response->assertRedirect(route('payments.show', $payment));

        $payment->refresh();
        $invoice->refresh();

        $this->assertEquals('reversed', $payment->status);
        $this->assertStringContainsString('Cheque bounced', $payment->notes);
        $this->assertEquals('0.00', $invoice->paid_amount);
        $this->assertEquals('500.00', $invoice->balance);
        $this->assertEquals('issued', $invoice->status);
    }

    private function makeUser(string $roleSlug, ?Branch $branch = null): User
    {
        $role = Role::firstOrCreate(['slug' => $roleSlug], [
            'name' => str($roleSlug)->replace('-', ' ')->title()->toString(),
        ]);

        return User::factory()->create([
            'role_id' => $role->id,
            'branch_id' => $branch?->id,
        ]);
    }

    private function makeBranch(string $name, string $code): Branch
    {
        return Branch::create([
            'name' => $name,
            'code' => $code,
            'status' => 'active',
        ]);
    }

    private function makeStudent(Branch $branch): Student
    {
        return Student::create([
            'branch_id' => $branch->id,
            'admission_number' => 'ADM-' . fake()->unique()->numerify('#####'),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'gender' => 'female',
            'date_of_birth' => '2016-03-12',
            'admission_date' => '2026-09-01',
            'status' => 'active',
        ]);
    }

    private function makeAcademicYear(Branch $branch): AcademicYear
    {
        return AcademicYear::create([
            'branch_id' => $branch->id,
            'name' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
            'status' => 'active',
        ]);
    }

    private function makeInvoice(Branch $branch, Student $student, AcademicYear $year, string $amount): Invoice
    {
        return Invoice::create([
            'branch_id' => $branch->id,
            'student_id' => $student->id,
            'academic_year_id' => $year->id,
            'invoice_number' => 'INV-' . strtoupper(fake()->unique()->bothify('###-????')),
            'total_amount' => $amount,
            'discount_amount' => '0.00',
            'paid_amount' => '0.00',
            'balance' => $amount,
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => 'issued',
        ]);
    }
}
