<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\FeeCategory;
use App\Models\FeeDiscount;
use App\Models\FeeStructure;
use App\Models\Grade;
use App\Models\Invoice;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_invoices(): void
    {
        $this->get(route('invoices.index'))->assertRedirect(route('login'));
    }

    public function test_authorized_user_can_view_invoices_list(): void
    {
        $branch = $this->makeBranch('Central Campus', 'CENTRAL');
        $admin = $this->makeUser('school-administrator', $branch);
        $student = $this->makeStudent($branch);
        $year = $this->makeAcademicYear($branch);

        $invoice = Invoice::create([
            'branch_id' => $branch->id,
            'student_id' => $student->id,
            'academic_year_id' => $year->id,
            'invoice_number' => 'INV-CENTRAL-2026-0001',
            'total_amount' => '500.00',
            'discount_amount' => '0.00',
            'paid_amount' => '0.00',
            'balance' => '500.00',
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => 'issued',
        ]);

        $this->actingAs($admin)
            ->get(route('invoices.index'))
            ->assertOk()
            ->assertSee('INV-CENTRAL-2026-0001')
            ->assertSee($student->first_name);
    }

    public function test_accountant_can_create_single_invoice_with_items_and_discounts(): void
    {
        $branch = $this->makeBranch('North Campus', 'NORTH');
        $accountant = $this->makeUser('accountant-cashier', $branch);
        $student = $this->makeStudent($branch);
        $year = $this->makeAcademicYear($branch);
        $category = FeeCategory::create([
            'branch_id' => $branch->id,
            'name' => 'Tuition Fee',
            'code' => 'TUI',
            'status' => 'active',
        ]);
        $discount = FeeDiscount::create([
            'branch_id' => $branch->id,
            'name' => 'Early Payment Waiver',
            'code' => 'EARLY',
            'type' => 'fixed',
            'value' => '50.00',
            'status' => 'active',
        ]);

        $response = $this->actingAs($accountant)->post(route('invoices.store'), [
            'is_batch' => 0,
            'branch_id' => $branch->id,
            'student_id' => $student->id,
            'academic_year_id' => $year->id,
            'due_date' => now()->addDays(15)->toDateString(),
            'notes' => 'Please pay via bank transfer',
            'items' => [
                [
                    'fee_category_id' => $category->id,
                    'description' => 'Tuition for Term 1',
                    'amount' => '500.00',
                ],
            ],
            'discounts' => [
                [
                    'fee_discount_id' => $discount->id,
                    'description' => 'Early Payment Waiver',
                    'type' => 'fixed',
                    'value' => '50.00',
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('invoices', [
            'branch_id' => $branch->id,
            'student_id' => $student->id,
            'total_amount' => '500.00',
            'discount_amount' => '50.00',
            'balance' => '450.00',
            'status' => 'issued',
        ]);
        $this->assertDatabaseHas('invoice_items', [
            'fee_category_id' => $category->id,
            'amount' => '500.00',
        ]);
        $this->assertDatabaseHas('invoice_discounts', [
            'fee_discount_id' => $discount->id,
            'computed_amount' => '50.00',
        ]);
    }

    public function test_batch_generate_invoices_for_enrolled_students(): void
    {
        $branch = $this->makeBranch('East Campus', 'EAST');
        $admin = $this->makeUser('school-administrator', $branch);
        $year = $this->makeAcademicYear($branch);
        $grade = Grade::create([
            'branch_id' => $branch->id,
            'name' => 'Grade 1',
            'code' => 'G1',
            'level' => 1,
            'status' => 'active',
        ]);
        $category = FeeCategory::create([
            'branch_id' => $branch->id,
            'name' => 'Annual Tuition',
            'code' => 'ANN-TUI',
            'status' => 'active',
        ]);

        FeeStructure::create([
            'branch_id' => $branch->id,
            'fee_category_id' => $category->id,
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'amount' => '1200.00',
            'status' => 'active',
        ]);

        // Create 2 students enrolled in this grade
        $student1 = $this->makeStudent($branch);
        $student2 = $this->makeStudent($branch);

        StudentEnrollment::create([
            'student_id' => $student1->id,
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'enrolled_at' => now()->toDateString(),
            'status' => 'active',
        ]);
        StudentEnrollment::create([
            'student_id' => $student2->id,
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'enrolled_at' => now()->toDateString(),
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->post(route('invoices.store'), [
            'is_batch' => 1,
            'branch_id' => $branch->id,
            'grade_id' => $grade->id,
            'academic_year_id' => $year->id,
            'due_date' => now()->addDays(20)->toDateString(),
        ]);

        $response->assertRedirect(route('invoices.index'));
        $this->assertDatabaseCount('invoices', 2);
        $this->assertDatabaseHas('invoices', [
            'student_id' => $student1->id,
            'total_amount' => '1200.00',
            'balance' => '1200.00',
        ]);
        $this->assertDatabaseHas('invoices', [
            'student_id' => $student2->id,
            'total_amount' => '1200.00',
            'balance' => '1200.00',
        ]);

        // Running batch again should skip them
        $responseSecond = $this->actingAs($admin)->post(route('invoices.store'), [
            'is_batch' => 1,
            'branch_id' => $branch->id,
            'grade_id' => $grade->id,
            'academic_year_id' => $year->id,
            'due_date' => now()->addDays(20)->toDateString(),
        ]);
        $responseSecond->assertRedirect(route('invoices.index'));
        $this->assertDatabaseCount('invoices', 2);
    }

    public function test_authorized_user_can_cancel_unpaid_invoice(): void
    {
        $branch = $this->makeBranch('South Campus', 'SOUTH');
        $admin = $this->makeUser('school-administrator', $branch);
        $student = $this->makeStudent($branch);
        $year = $this->makeAcademicYear($branch);

        $invoice = Invoice::create([
            'branch_id' => $branch->id,
            'student_id' => $student->id,
            'academic_year_id' => $year->id,
            'invoice_number' => 'INV-SOUTH-2026-0001',
            'total_amount' => '300.00',
            'discount_amount' => '0.00',
            'paid_amount' => '0.00',
            'balance' => '300.00',
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => 'issued',
        ]);

        $response = $this->actingAs($admin)->post(route('invoices.cancel', $invoice));

        $response->assertRedirect(route('invoices.show', $invoice));
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'cancelled',
        ]);
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
            'gender' => 'male',
            'date_of_birth' => '2015-05-10',
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
}
