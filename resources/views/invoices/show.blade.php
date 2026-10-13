@extends('layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">School finance</p>
            <h1>Invoice {{ $invoice->invoice_number }}</h1>
            <p class="intro-copy">Issued for {{ $invoice->student->full_name }} &bull; {{ $invoice->branch->name }}</p>
        </div>
        <div class="intro-actions">
            @if ($invoice->status !== 'paid' && $invoice->status !== 'cancelled')
                <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}" class="primary-link">Record payment</a>
            @endif
            <button type="button" class="secondary-button" onclick="window.print()">Print statement</button>
            <a href="{{ route('invoices.index') }}" class="secondary-link">Back to invoices</a>
        </div>
    </section>

    @if (session('success'))
        <div class="alert alert-success" style="margin-bottom: 1.5rem;">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error" style="margin-bottom: 1.5rem;">{{ session('error') }}</div>
    @endif

    <div class="dashboard-columns">
        <section class="content-card" style="grid-column: span 2;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 1.5rem; border-bottom: 2px solid #e5e7eb; margin-bottom: 1.5rem;">
                <div>
                    <h2 style="margin: 0; font-size: 1.5rem; color: #111827;">{{ $invoice->branch->name }}</h2>
                    <p class="meta-line" style="margin-top: 0.25rem;">Official Fee Billing Statement</p>
                </div>
                <div style="text-align: right;">
                    <span class="status-pill {{ $invoice->status }}" style="font-size: 0.875rem; padding: 0.35rem 0.75rem;">
                        {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}
                    </span>
                    <p class="meta-line" style="margin-top: 0.5rem;">Invoice #: <strong>{{ $invoice->invoice_number }}</strong></p>
                    <p class="meta-line">Due Date: <strong>{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : '—' }}</strong></p>
                </div>
            </div>

            <!-- STUDENT & BILLING INFO -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                <div>
                    <span class="meta-label">Billed to student</span>
                    <strong style="font-size: 1.1rem; display: block;">{{ $invoice->student->full_name }}</strong>
                    <p class="meta-line">Admission #: <code>{{ $invoice->student->admission_number }}</code></p>
                    @if ($invoice->student->email)
                        <p class="meta-line">{{ $invoice->student->email }}</p>
                    @endif
                </div>

                <div>
                    <span class="meta-label">Academic session</span>
                    <strong style="display: block;">{{ $invoice->academicYear->name }}</strong>
                    <p class="meta-line">Term: {{ $invoice->term ? $invoice->term->name : 'Full Academic Year' }}</p>
                </div>

                <div>
                    <span class="meta-label">Guardians</span>
                    @forelse ($invoice->student->guardians as $guardian)
                        <p style="margin: 0; font-weight: 500;">{{ $guardian->name }} ({{ $guardian->pivot->relationship ?? 'Guardian' }})</p>
                        <p class="meta-line" style="margin-bottom: 0.5rem;">{{ $guardian->phone }}</p>
                    @empty
                        <p class="meta-line">No guardians registered</p>
                    @endforelse
                </div>
            </div>

            <!-- LINE ITEMS TABLE -->
            <div class="table-wrap" style="margin-bottom: 1.5rem;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Fee Category</th>
                            <th>Description</th>
                            <th style="text-align: right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoice->items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $item->feeCategory->name }}</strong>
                                    <small class="meta-line"><code>{{ $item->feeCategory->code }}</code></small>
                                </td>
                                <td>{{ $item->description }}</td>
                                <td style="text-align: right; font-weight: 600;">
                                    ${{ number_format((float) $item->amount, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- DISCOUNTS BREAKDOWN (IF ANY) -->
            @if ($invoice->discounts->isNotEmpty())
                <div style="margin-bottom: 1.5rem; background: #f0fdf4; padding: 1rem; border-radius: 8px; border: 1px solid #bbf7d0;">
                    <h3 style="margin: 0 0 0.5rem 0; font-size: 0.95rem; color: #166534;">Discounts & Waivers Applied</h3>
                    @foreach ($invoice->discounts as $discount)
                        <div style="display: flex; justify-content: space-between; font-size: 0.875rem; color: #15803d; margin-bottom: 0.25rem;">
                            <span>{{ $discount->description }} ({{ $discount->type === 'percentage' ? number_format((float) $discount->value, 1) . '%' : '$' . number_format((float) $discount->value, 2) }})</span>
                            <strong>-${{ number_format((float) $discount->computed_amount, 2) }}</strong>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- FINANCIAL TOTALS SUMMARY -->
            <div style="display: flex; justify-content: flex-end; margin-bottom: 2rem;">
                <div style="width: 320px; border-top: 2px solid #e5e7eb; padding-top: 1rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span>Subtotal:</span>
                        <strong>${{ number_format((float) $invoice->total_amount, 2) }}</strong>
                    </div>
                    @if ((float) $invoice->discount_amount > 0)
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; color: #16a34a;">
                            <span>Total Discounts:</span>
                            <strong>-${{ number_format((float) $invoice->discount_amount, 2) }}</strong>
                        </div>
                    @endif
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; color: #0284c7;">
                        <span>Amount Paid:</span>
                        <strong>${{ number_format((float) $invoice->paid_amount, 2) }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding-top: 0.75rem; border-top: 1px solid #d1d5db; font-size: 1.25rem;">
                        <span>Outstanding Balance:</span>
                        <strong style="color: {{ (float) $invoice->balance > 0 ? '#b91c1c' : '#15803d' }};">
                            ${{ number_format((float) $invoice->balance, 2) }}
                        </strong>
                    </div>
                </div>
            </div>

            <!-- PAYMENT HISTORY -->
            @if ($invoice->payments->isNotEmpty())
                <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 2px solid #e5e7eb;">
                    <h3>Payments recorded</h3>
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Receipt #</th>
                                    <th>Payment Date</th>
                                    <th>Method</th>
                                    <th>Reference</th>
                                    <th style="text-align: right;">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoice->payments as $payment)
                                    <tr>
                                        <td><strong><code>{{ $payment->receipt_number }}</code></strong></td>
                                        <td>{{ $payment->payment_date ? $payment->payment_date->format('M d, Y') : '—' }}</td>
                                        <td><span class="status-pill active">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span></td>
                                        <td>{{ $payment->reference_number ?? '—' }}</td>
                                        <td style="text-align: right; font-weight: 600; color: #0f766e;">
                                            ${{ number_format((float) $payment->amount, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- CANCEL INVOICE -->
            @can('cancel', $invoice)
                @if ($invoice->status !== 'cancelled' && (float) $invoice->paid_amount == 0)
                    <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e5e7eb;">
                        <form method="POST" action="{{ route('invoices.cancel', $invoice) }}" onsubmit="return confirm('Are you sure you want to cancel this invoice? This cannot be undone.');">
                            @csrf
                            <button type="submit" class="secondary-button" style="color: #dc2626; border-color: #fca5a5;">
                                Cancel invoice
                            </button>
                        </form>
                    </div>
                @endif
            @endcan
        </section>
    </div>
@endsection
