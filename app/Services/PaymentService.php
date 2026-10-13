<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    /**
     * Record a payment against an invoice inside a database transaction with concurrency locking.
     */
    public function recordPayment(array $data, ?User $recorder = null): Payment
    {
        return DB::transaction(function () use ($data, $recorder) {
            $invoice = Invoice::query()
                ->whereKey($data['invoice_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($invoice->status === 'cancelled') {
                throw ValidationException::withMessages([
                    'invoice_id' => 'Cannot record payment for a cancelled invoice.',
                ]);
            }

            $amount = round((float) $data['amount'], 2);
            $balance = (float) $invoice->balance;

            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'Payment amount must be greater than zero.',
                ]);
            }

            if ($amount > $balance) {
                throw ValidationException::withMessages([
                    'amount' => "Payment amount (\${$amount}) cannot exceed outstanding invoice balance (\${$balance}).",
                ]);
            }

            $branch = $invoice->branch;
            $receiptNumber = $data['receipt_number'] ?? Payment::generateReceiptNumber($branch);

            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'receipt_number' => $receiptNumber,
                'amount' => $amount,
                'payment_method' => $data['payment_method'],
                'payment_date' => $data['payment_date'],
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'confirmed',
                'recorded_by' => $recorder?->id,
            ]);

            $newPaid = round((float) $invoice->paid_amount + $amount, 2);
            $newBalance = max(0, round((float) $invoice->balance - $amount, 2));

            $invoice->update([
                'paid_amount' => $newPaid,
                'balance' => $newBalance,
                'status' => $newBalance <= 0 ? 'paid' : 'partially_paid',
            ]);

            return $payment->fresh(['invoice.student', 'invoice.branch', 'recordedBy']);
        });
    }

    /**
     * Reverse a recorded payment and update the invoice balance.
     */
    public function reversePayment(Payment $payment, ?string $reason = null): Payment
    {
        return DB::transaction(function () use ($payment, $reason) {
            if ($payment->status === 'reversed') {
                throw ValidationException::withMessages([
                    'payment' => 'This payment has already been reversed.',
                ]);
            }

            $invoice = Invoice::query()
                ->whereKey($payment->invoice_id)
                ->lockForUpdate()
                ->firstOrFail();

            $reversalNote = $reason ? "[Reversed: {$reason}] " . $payment->notes : '[Reversed] ' . $payment->notes;

            $payment->update([
                'status' => 'reversed',
                'notes' => $reversalNote,
            ]);

            $newPaid = max(0, round((float) $invoice->paid_amount - (float) $payment->amount, 2));
            $netTotal = max(0, round((float) $invoice->total_amount - (float) $invoice->discount_amount, 2));
            $newBalance = max(0, round($netTotal - $newPaid, 2));

            $newStatus = $invoice->status;
            if ($newStatus !== 'cancelled') {
                if ($newBalance <= 0 && $netTotal > 0) {
                    $newStatus = 'paid';
                } elseif ($newPaid > 0) {
                    $newStatus = 'partially_paid';
                } elseif ($invoice->due_date && $invoice->due_date->isPast()) {
                    $newStatus = 'overdue';
                } else {
                    $newStatus = 'issued';
                }
            }

            $invoice->update([
                'paid_amount' => $newPaid,
                'balance' => $newBalance,
                'status' => $newStatus,
            ]);

            return $payment->fresh(['invoice']);
        });
    }
}
