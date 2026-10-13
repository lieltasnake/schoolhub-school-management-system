@extends('layouts.app')

@section('title', 'Payments & Receipts')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">School finance</p>
            <h1>Payments and receipts</h1>
            <p class="intro-copy">Track fee collections, transaction methods, receipt generation, and payment histories.</p>
        </div>
        @can('create', App\Models\Payment::class)
            <a href="{{ route('payments.create') }}" class="primary-link">Record payment</a>
        @endcan
    </section>

    <section class="content-card">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <form method="GET" action="{{ route('payments.index') }}" class="filter-form">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Receipt #, student, or invoice #" aria-label="Search payments">

            @if ($branches->count() > 1)
                <select name="branch_id" aria-label="Filter by campus">
                    <option value="">All campuses</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            @endif

            <select name="payment_method" aria-label="Filter by payment method">
                <option value="">All payment methods</option>
                <option value="cash" @selected(request('payment_method') === 'cash')>Cash</option>
                <option value="bank_transfer" @selected(request('payment_method') === 'bank_transfer')>Bank Transfer</option>
                <option value="cheque" @selected(request('payment_method') === 'cheque')>Cheque</option>
                <option value="mobile_money" @selected(request('payment_method') === 'mobile_money')>Mobile Money</option>
                <option value="other" @selected(request('payment_method') === 'other')>Other</option>
            </select>

            <select name="status" aria-label="Filter by status">
                <option value="">All statuses</option>
                <option value="confirmed" @selected(request('status') === 'confirmed')>Confirmed</option>
                <option value="reversed" @selected(request('status') === 'reversed')>Reversed</option>
            </select>

            <button type="submit" class="secondary-button">Apply filters</button>
        </form>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Receipt #</th>
                        <th>Student</th>
                        <th>Invoice #</th>
                        <th>Amount Paid</th>
                        <th>Payment Method</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td>
                                <strong><code>{{ $payment->receipt_number }}</code></strong>
                                <small class="meta-line">{{ $payment->invoice->branch->name }}</small>
                            </td>
                            <td>
                                <strong>{{ $payment->invoice->student->full_name }}</strong>
                                <small class="meta-line">{{ $payment->invoice->student->admission_number }}</small>
                            </td>
                            <td>
                                <a href="{{ route('invoices.show', $payment->invoice) }}" class="table-link">
                                    {{ $payment->invoice->invoice_number }}
                                </a>
                            </td>
                            <td style="font-weight: 600; color: #0f766e;">
                                ${{ number_format((float) $payment->amount, 2) }}
                            </td>
                            <td>
                                <span class="status-pill active">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span>
                                @if ($payment->reference_number)
                                    <small class="meta-line">Ref: {{ $payment->reference_number }}</small>
                                @endif
                            </td>
                            <td>{{ $payment->payment_date ? $payment->payment_date->format('M d, Y') : '—' }}</td>
                            <td>
                                <span class="status-pill {{ $payment->status === 'confirmed' ? 'active' : 'inactive' }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('payments.show', $payment) }}" class="table-link">Receipt</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8">No payment records found for the selected scope.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $payments->links() }}
    </section>
@endsection
