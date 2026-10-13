<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\FeeDiscount;
use App\Models\FeeStructure;
use App\Models\Invoice;
use App\Models\InvoiceDiscount;
use App\Models\InvoiceItem;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    /**
     * Create a single invoice with line items and discounts inside a database transaction.
     */
    public function createInvoice(array $data, array $items, array $discounts = [], ?User $creator = null): Invoice
    {
        return DB::transaction(function () use ($data, $items, $discounts, $creator) {
            $branch = Branch::findOrFail($data['branch_id']);

            $invoiceNumber = $data['invoice_number'] ?? Invoice::generateInvoiceNumber($branch);

            $invoice = Invoice::create([
                'branch_id' => $data['branch_id'],
                'student_id' => $data['student_id'],
                'academic_year_id' => $data['academic_year_id'],
                'term_id' => $data['term_id'] ?? null,
                'invoice_number' => $invoiceNumber,
                'due_date' => $data['due_date'],
                'status' => $data['status'] ?? 'issued',
                'notes' => $data['notes'] ?? null,
                'issued_at' => now(),
                'created_by' => $creator?->id,
            ]);

            $totalAmount = 0.00;
            foreach ($items as $itemData) {
                $amount = round((float) $itemData['amount'], 2);
                $invoice->items()->create([
                    'fee_category_id' => $itemData['fee_category_id'],
                    'fee_structure_id' => $itemData['fee_structure_id'] ?? null,
                    'description' => $itemData['description'],
                    'amount' => $amount,
                ]);
                $totalAmount += $amount;
            }

            $totalDiscount = 0.00;
            foreach ($discounts as $discountData) {
                $type = $discountData['type'];
                $val = (float) $discountData['value'];

                $computed = $type === 'percentage'
                    ? round(($totalAmount * $val) / 100, 2)
                    : min($totalAmount, round($val, 2));

                $invoice->discounts()->create([
                    'fee_discount_id' => $discountData['fee_discount_id'] ?? null,
                    'description' => $discountData['description'] ?? 'Discount applied',
                    'type' => $type,
                    'value' => $val,
                    'computed_amount' => $computed,
                    'approved_by' => $creator?->id,
                ]);

                $totalDiscount += $computed;
            }

            $invoice->recalculateTotals();

            return $invoice->fresh(['items', 'discounts', 'student', 'branch']);
        });
    }

    /**
     * Batch generate invoices for all enrolled students in a grade/section based on active fee structures.
     */
    public function batchGenerateInvoices(
        int $branchId,
        int $academicYearId,
        int $gradeId,
        ?int $termId,
        string $dueDate,
        ?int $sectionId = null,
        ?User $creator = null
    ): array {
        return DB::transaction(function () use ($branchId, $academicYearId, $gradeId, $termId, $dueDate, $sectionId, $creator) {
            $branch = Branch::findOrFail($branchId);

            $feeStructures = FeeStructure::query()
                ->where('branch_id', $branchId)
                ->where('academic_year_id', $academicYearId)
                ->where('grade_id', $gradeId)
                ->when($termId, fn ($q) => $q->where(fn ($sub) => $sub->where('term_id', $termId)->orWhereNull('term_id')))
                ->when(! $termId, fn ($q) => $q->whereNull('term_id'))
                ->where('status', 'active')
                ->with('feeCategory')
                ->get();

            if ($feeStructures->isEmpty()) {
                return [
                    'generated' => 0,
                    'skipped' => 0,
                    'message' => 'No active fee structures found for this grade and schedule.',
                ];
            }

            $enrollmentQuery = StudentEnrollment::query()
                ->where('academic_year_id', $academicYearId)
                ->where('grade_id', $gradeId)
                ->where('status', 'active')
                ->whereHas('student', fn ($q) => $q->where('branch_id', $branchId)->where('status', 'active'));

            if ($sectionId) {
                $enrollmentQuery->where('section_id', $sectionId);
            }

            $enrollments = $enrollmentQuery->with('student')->get();

            $generatedCount = 0;
            $skippedCount = 0;

            foreach ($enrollments as $enrollment) {
                $student = $enrollment->student;

                // Prevent duplicate invoice generation for the same student, year, and term
                $alreadyExists = Invoice::query()
                    ->where('student_id', $student->id)
                    ->where('academic_year_id', $academicYearId)
                    ->when($termId, fn ($q) => $q->where('term_id', $termId))
                    ->when(! $termId, fn ($q) => $q->whereNull('term_id'))
                    ->whereNotIn('status', ['cancelled'])
                    ->exists();

                if ($alreadyExists) {
                    $skippedCount++;
                    continue;
                }

                $items = [];
                foreach ($feeStructures as $structure) {
                    $items[] = [
                        'fee_category_id' => $structure->fee_category_id,
                        'fee_structure_id' => $structure->id,
                        'description' => $structure->feeCategory->name,
                        'amount' => $structure->amount,
                    ];
                }

                $this->createInvoice([
                    'branch_id' => $branchId,
                    'student_id' => $student->id,
                    'academic_year_id' => $academicYearId,
                    'term_id' => $termId,
                    'due_date' => $dueDate,
                    'status' => 'issued',
                    'notes' => 'Automated batch generated invoice',
                ], $items, [], $creator);

                $generatedCount++;
            }

            return [
                'generated' => $generatedCount,
                'skipped' => $skippedCount,
                'total_students' => $enrollments->count(),
            ];
        });
    }
}
