@extends('layouts.app')

@section('title', 'Student Invoices')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">School finance</p>
            <h1>Student invoices</h1>
            <p class="intro-copy">Manage student billing statements, fee schedules, outstanding balances, and payment statuses.</p>
        </div>
        @can('create', App\Models\Invoice::class)
            <a href="{{ route('invoices.create') }}" class="primary-link">Generate invoice</a>
        @endcan
    </section>

    <section class="content-card">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <form method="GET" action="{{ route('invoices.index') }}" class="filter-form">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by student name or invoice #" aria-label="Search invoices">

            @if ($branches->count() > 1)
                <select name="branch_id" aria-label="Filter by campus" onchange="this.form.submit()">
                    <option value="">All campuses</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            @endif

            @if ($academicYears->isNotEmpty())
                <select name="academic_year_id" aria-label="Filter by academic year">
                    <option value="">All academic years</option>
                    @foreach ($academicYears as $year)
                        <option value="{{ $year->id }}" @selected((string) request('academic_year_id') === (string) $year->id)>{{ $year->name }}</option>
                    @endforeach
                </select>
            @endif

            <select name="status" aria-label="Filter by status">
                <option value="">All statuses</option>
                @foreach (['issued', 'partially_paid', 'paid', 'overdue', 'cancelled'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>

            <button type="submit" class="secondary-button">Apply filters</button>
        </form>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Student</th>
                        <th>Academic Year</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td>
                                <strong><code>{{ $invoice->invoice_number }}</code></strong>
                                <small class="meta-line">{{ $invoice->branch->name }}</small>
                            </td>
                            <td>
                                <strong>{{ $invoice->student->full_name }}</strong>
                                <small class="meta-line">{{ $invoice->student->admission_number }}</small>
                            </td>
                            <td>
                                {{ $invoice->academicYear->name }}
                                @if ($invoice->term)
                                    <small class="meta-line">{{ $invoice->term->name }}</small>
                                @endif
                            </td>
                            <td>${{ number_format((float) $invoice->total_amount, 2) }}</td>
                            <td>${{ number_format((float) $invoice->paid_amount, 2) }}</td>
                            <td>
                                <strong style="color: {{ (float) $invoice->balance > 0 ? '#b91c1c' : '#15803d' }};">
                                    ${{ number_format((float) $invoice->balance, 2) }}
                                </strong>
                            </td>
                            <td>
                                {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : '—' }}
                                @if ($invoice->due_date && $invoice->due_date->isPast() && (float) $invoice->balance > 0)
                                    <small class="meta-line" style="color: #b91c1c;">Past due</small>
                                @endif
                            </td>
                            <td>
                                <span class="status-pill {{ $invoice->status }}">
                                    {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('invoices.show', $invoice) }}" class="table-link">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9">No invoices found for the selected scope.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $invoices->links() }}
    </section>
@endsection
