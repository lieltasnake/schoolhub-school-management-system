@extends('layouts.app')

@section('title', 'Record Payment')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">School finance</p>
            <h1>Record payment</h1>
            <p class="intro-copy">Enter payment transaction details and issue an official payment receipt.</p>
        </div>
        <a href="{{ route('payments.index') }}" class="secondary-link">Cancel</a>
    </section>

    <section class="content-card">
        @if ($errors->any())
            <div class="alert alert-error" style="margin-bottom: 1.5rem;">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('payments.store') }}" class="form-grid">
            @csrf

            @if ($selectedInvoice)
                <input type="hidden" name="invoice_id" value="{{ $selectedInvoice->id }}">

                <div class="full-width" style="background: #f8fafc; padding: 1.25rem; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 1rem;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <div>
                            <span class="meta-label">Selected invoice</span>
                            <strong>{{ $selectedInvoice->invoice_number }}</strong>
                        </div>
                        <div>
                            <span class="meta-label">Student</span>
                            <strong>{{ $selectedInvoice->student->full_name }}</strong>
                            <small class="meta-line">{{ $selectedInvoice->student->admission_number }}</small>
                        </div>
                        <div>
                            <span class="meta-label">Total billed</span>
                            <strong>${{ number_format((float) $selectedInvoice->total_amount, 2) }}</strong>
                        </div>
                        <div>
                            <span class="meta-label">Outstanding balance</span>
                            <strong style="color: #b91c1c; font-size: 1.15rem;">${{ number_format((float) $selectedInvoice->balance, 2) }}</strong>
                        </div>
                    </div>
                </div>
            @else
                <div class="full-width">
                    <label for="invoice_id">Choose invoice to pay</label>
                    <select id="invoice_id" name="invoice_id" required>
                        <option value="">Select an outstanding invoice</option>
                        @foreach ($unpaidInvoices as $inv)
                            <option value="{{ $inv->id }}" @selected((string) old('invoice_id') === (string) $inv->id)>
                                {{ $inv->invoice_number }} — {{ $inv->student->full_name }} (Balance: ${{ number_format((float) $inv->balance, 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label for="amount">Payment amount ($)</label>
                <input id="amount" type="number" step="0.01" min="0.01" max="999999999999.99" name="amount" value="{{ old('amount', $selectedInvoice ? $selectedInvoice->balance : '') }}" placeholder="0.00" required>
                @if ($selectedInvoice)
                    <small class="meta-line">Max payable: ${{ number_format((float) $selectedInvoice->balance, 2) }}</small>
                @endif
            </div>

            <div>
                <label for="payment_method">Payment method</label>
                <select id="payment_method" name="payment_method" required>
                    <option value="cash" @selected(old('payment_method') === 'cash')>Cash</option>
                    <option value="bank_transfer" @selected(old('payment_method') === 'bank_transfer')>Bank Transfer</option>
                    <option value="cheque" @selected(old('payment_method') === 'cheque')>Cheque</option>
                    <option value="mobile_money" @selected(old('payment_method') === 'mobile_money')>Mobile Money</option>
                    <option value="other" @selected(old('payment_method') === 'other')>Other</option>
                </select>
            </div>

            <div>
                <label for="payment_date">Payment date</label>
                <input id="payment_date" type="date" name="payment_date" value="{{ old('payment_date', now()->toDateString()) }}" required>
            </div>

            <div>
                <label for="reference_number">Reference / Transaction # (optional)</label>
                <input id="reference_number" type="text" name="reference_number" value="{{ old('reference_number') }}" placeholder="e.g. Bank Ref #, Cheque #, Mobile Money ID">
            </div>

            <div class="full-width">
                <label for="notes">Notes (optional)</label>
                <textarea id="notes" name="notes" rows="2" placeholder="Payer name, bank branch, or internal cashier notes">{{ old('notes') }}</textarea>
            </div>

            <div class="full-width form-actions">
                <button type="submit" class="primary-button">Confirm & issue receipt</button>
            </div>
        </form>
    </section>
@endsection
