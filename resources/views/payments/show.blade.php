@extends('layouts.app')

@section('title', 'Receipt ' . $payment->receipt_number)

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">School finance</p>
            <h1>Receipt {{ $payment->receipt_number }}</h1>
            <p class="intro-copy">Payment confirmed for {{ $payment->invoice->student->full_name }}</p>
        </div>
        <div class="intro-actions">
            <button type="button" class="primary-button" onclick="window.print()">Print receipt</button>
            <a href="{{ route('invoices.show', $payment->invoice) }}" class="secondary-link">View invoice</a>
            <a href="{{ route('payments.index') }}" class="secondary-link">All payments</a>
        </div>
    </section>

    @if (session('success'))
        <div class="alert alert-success" style="margin-bottom: 1.5rem;">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error" style="margin-bottom: 1.5rem;">{{ session('error') }}</div>
    @endif

    <section class="content-card" style="max-width: 800px; margin: 0 auto; padding: 2.5rem; border: 1px solid #e2e8f0; background: #ffffff;">
        <!-- RECEIPT HEADER -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 1.5rem; border-bottom: 2px solid #0f766e; margin-bottom: 1.5rem;">
            <div>
                <span class="brand-mark" style="font-size: 1.5rem; margin-bottom: 0.5rem; display: inline-block;">S</span>
                <h2 style="margin: 0; font-size: 1.5rem; color: #111827;">{{ $payment->invoice->branch->name }}</h2>
                <p class="meta-line">Official Payment Receipt</p>
            </div>
            <div style="text-align: right;">
                <span class="status-pill {{ $payment->status === 'confirmed' ? 'active' : 'inactive' }}" style="font-size: 0.875rem;">
                    {{ ucfirst($payment->status) }}
                </span>
                <p class="meta-line" style="margin-top: 0.5rem;">Receipt #: <strong>{{ $payment->receipt_number }}</strong></p>
                <p class="meta-line">Date: <strong>{{ $payment->payment_date ? $payment->payment_date->format('F j, Y') : '—' }}</strong></p>
            </div>
        </div>

        <!-- RECEIVED FROM -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <div>
                <span class="meta-label">Received from student</span>
                <strong style="font-size: 1.15rem; display: block;">{{ $payment->invoice->student->full_name }}</strong>
                <p class="meta-line">Admission #: <code>{{ $payment->invoice->student->admission_number }}</code></p>
            </div>

            <div>
                <span class="meta-label">Applied to invoice</span>
                <strong style="display: block;">{{ $payment->invoice->invoice_number }}</strong>
                <p class="meta-line">{{ $payment->invoice->academicYear->name }} &bull; {{ $payment->invoice->term ? $payment->invoice->term->name : 'Full Year' }}</p>
            </div>

            <div>
                <span class="meta-label">Payment method</span>
                <strong style="display: block;">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</strong>
                @if ($payment->reference_number)
                    <p class="meta-line">Ref #: <code>{{ $payment->reference_number }}</code></p>
                @endif
            </div>
        </div>

        <!-- AMOUNT PAID HIGHLIGHT BOX -->
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 1.5rem; text-align: center; margin-bottom: 2rem;">
            <span class="meta-label" style="color: #166534; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em;">Amount paid</span>
            <strong style="font-size: 2.25rem; color: #15803d; display: block; margin-top: 0.25rem;">
                ${{ number_format((float) $payment->amount, 2) }}
            </strong>
        </div>

        <!-- INVOICE BALANCE STATUS -->
        <div style="border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb; padding: 1rem 0; margin-bottom: 2rem;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                <span>Total Invoice Amount:</span>
                <strong>${{ number_format((float) $payment->invoice->total_amount, 2) }}</strong>
            </div>
            @if ((float) $payment->invoice->discount_amount > 0)
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; color: #16a34a;">
                    <span>Discounts Applied:</span>
                    <strong>-${{ number_format((float) $payment->invoice->discount_amount, 2) }}</strong>
                </div>
            @endif
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                <span>Total Paid to Date:</span>
                <strong style="color: #0f766e;">${{ number_format((float) $payment->invoice->paid_amount, 2) }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 1.1rem; padding-top: 0.5rem; border-top: 1px dashed #d1d5db;">
                <span>Remaining Balance Due:</span>
                <strong style="color: {{ (float) $payment->invoice->balance > 0 ? '#b91c1c' : '#15803d' }};">
                    ${{ number_format((float) $payment->invoice->balance, 2) }}
                </strong>
            </div>
        </div>

        @if ($payment->notes)
            <div style="margin-bottom: 2rem;">
                <span class="meta-label">Payment notes</span>
                <p style="margin: 0.25rem 0 0 0; color: #4b5563;">{{ $payment->notes }}</p>
            </div>
        @endif

        <!-- RECEIPT FOOTER / AUDIT -->
        <div style="display: flex; justify-content: space-between; align-items: flex-end; padding-top: 2rem; margin-top: 2rem; border-top: 1px solid #e5e7eb;">
            <div>
                <p class="meta-line">Recorded by: <strong>{{ $payment->recordedBy?->name ?? 'System Cashier' }}</strong></p>
                <p class="meta-line">Generated on: {{ $payment->created_at->format('M d, Y H:i') }}</p>
            </div>
            <div style="text-align: center; width: 200px;">
                <div style="border-bottom: 1px solid #9ca3af; height: 40px; margin-bottom: 0.5rem;"></div>
                <span class="meta-label" style="font-size: 0.75rem;">Authorized Cashier Signature</span>
            </div>
        </div>

        <!-- REVERSAL ACTION -->
        @can('reverse', $payment)
            @if ($payment->status === 'confirmed')
                <div style="margin-top: 3rem; padding-top: 1.5rem; border-top: 1px dashed #fca5a5;">
                    <form method="POST" action="{{ route('payments.reverse', $payment) }}" onsubmit="return confirm('Are you sure you want to reverse this payment? The invoice balance will be restored.');">
                        @csrf
                        <div style="display: flex; gap: 0.75rem; align-items: center;">
                            <input type="text" name="reason" placeholder="Reason for reversal (e.g. Bounced cheque, erroneous entry)" required style="flex: 1;">
                            <button type="submit" class="secondary-button" style="color: #dc2626; border-color: #fca5a5;">Reverse payment</button>
                        </div>
                    </form>
                </div>
            @endif
        @endcan
    </section>
@endsection
