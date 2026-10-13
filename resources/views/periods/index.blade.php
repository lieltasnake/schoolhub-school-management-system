@extends('layouts.app')

@section('title', 'Periods & Bell Schedule')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">School Setup</p>
            <h1>Periods & Bell Schedule</h1>
            <p class="intro-copy">Define daily periods, break times, and instructional blocks.</p>
        </div>
        <a href="{{ route('periods.create') }}" class="primary-link">Add period</a>
    </section>

    <section class="content-card">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <form method="GET" action="{{ route('periods.index') }}" class="filter-form">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by name or number..." aria-label="Search periods">
            @if ($branches->count() > 1)
                <select name="branch_id" aria-label="Filter by campus">
                    <option value="">All campuses</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            @endif
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
                        <th>#</th>
                        <th>Period name</th>
                        <th>Campus</th>
                        <th>Start time</th>
                        <th>End time</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($periods as $period)
                        <tr>
                            <td><strong>{{ $period->period_number }}</strong></td>
                            <td>{{ $period->name }}</td>
                            <td>{{ $period->branch->name }}</td>
                            <td>{{ $period->start_time }}</td>
                            <td>{{ $period->end_time }}</td>
                            <td>
                                @if ($period->is_break)
                                    <span class="status-pill pending">Break / Recess</span>
                                @else
                                    <span class="status-pill active">Instructional</span>
                                @endif
                            </td>
                            <td><span class="status-pill {{ $period->status }}">{{ ucfirst($period->status) }}</span></td>
                            <td>
                                <a href="{{ route('periods.edit', $period) }}" class="table-link">Edit</a>
                                <form method="POST" action="{{ route('periods.destroy', $period) }}" onsubmit="return confirm('Delete this period?');" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="table-link" style="background:none; border:none; cursor:pointer; color:var(--danger, #ef4444); padding:0;">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8">No periods configured.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $periods->links() }}
    </section>
@endsection
