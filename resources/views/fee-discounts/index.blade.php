@extends('layouts.app')

@section('title', 'Fee Discounts & Waivers')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">School finance</p>
            <h1>Discounts and waivers</h1>
            <p class="intro-copy">Configure concession rules, merit scholarships, sibling discounts, and fee relief policies.</p>
        </div>
        @can('create', App\Models\FeeDiscount::class)
            <a href="{{ route('fee-discounts.create') }}" class="primary-link">Add discount</a>
        @endcan
    </section>

    <section class="content-card">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <form method="GET" action="{{ route('fee-discounts.index') }}" class="filter-form">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by name or code" aria-label="Search discounts">
            @if ($branches->count() > 1)
                <select name="branch_id" aria-label="Filter by campus">
                    <option value="">All campuses</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            @endif
            <select name="type" aria-label="Filter by discount type">
                <option value="">All types</option>
                <option value="percentage" @selected(request('type') === 'percentage')>Percentage (%)</option>
                <option value="fixed" @selected(request('type') === 'fixed')>Fixed amount ($)</option>
            </select>
            <select name="status" aria-label="Filter by status">
                <option value="">All statuses</option>
                @foreach (['active', 'inactive'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <button type="submit" class="secondary-button">Apply filters</button>
        </form>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Discount name</th>
                        <th>Code</th>
                        <th>Type</th>
                        <th>Value</th>
                        <th>Campus</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($feeDiscounts as $discount)
                        <tr>
                            <td>
                                <strong>{{ $discount->name }}</strong>
                                @if ($discount->description)
                                    <small class="meta-line">{{ Str::limit($discount->description, 50) }}</small>
                                @endif
                            </td>
                            <td><code>{{ $discount->code }}</code></td>
                            <td>
                                <span class="status-pill {{ $discount->type === 'percentage' ? 'active' : 'inactive' }}">
                                    {{ ucfirst($discount->type) }}
                                </span>
                            </td>
                            <td>
                                <strong>
                                    @if ($discount->type === 'percentage')
                                        {{ number_format((float) $discount->value, 2) }}%
                                    @else
                                        ${{ number_format((float) $discount->value, 2) }}
                                    @endif
                                </strong>
                            </td>
                            <td>{{ $discount->branch->name }}</td>
                            <td><span class="status-pill {{ $discount->status }}">{{ ucfirst($discount->status) }}</span></td>
                            <td>
                                @can('update', $discount)
                                    <a href="{{ route('fee-discounts.edit', $discount) }}" class="table-link">Edit</a>
                                @endcan
                                @can('delete', $discount)
                                    <form method="POST" action="{{ route('fee-discounts.destroy', $discount) }}" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this discount?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="table-link text-danger" style="background:none;border:none;padding:0;cursor:pointer;">Delete</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No fee discounts found for the selected scope.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $feeDiscounts->links() }}
    </section>
@endsection
