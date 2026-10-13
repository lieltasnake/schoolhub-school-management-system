<?php

namespace App\Http\Requests;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $invoiceId = $this->integer('invoice_id');
        $invoice = Invoice::find($invoiceId);

        if (! $invoice) {
            return false;
        }

        $canRecord = $user->canManageFinance() || $user->hasPermission('manage_receipts');

        return $canRecord && $user->canAccessBranch($invoice->branch_id);
    }

    public function rules(): array
    {
        return [
            'invoice_id' => ['required', 'integer', 'exists:invoices,id'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999999.99'],
            'payment_method' => ['required', 'in:cash,bank_transfer,cheque,mobile_money,other'],
            'payment_date' => ['required', 'date'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
