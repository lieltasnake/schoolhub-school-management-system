@extends('layouts.app')

@section('title', 'School Hours')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">School Setup</p>
            <h1>School Hours & Shifts</h1>
            <p class="intro-copy">Manage school opening/closing hours and operating shifts.</p>
        </div>
        <a href="{{ route('school-hours.create') }}" class="primary-link">Add school hours</a>
    </section>

    <section class="content-card">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <form method="GET" action="{{ route('school-hours.index') }}" class="filter-form">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by shift name..." aria-label="Search shifts">
            @if ($branches->count() > 1)
                <select name="branch_id" aria-label="Filter by campus">
                    <option value="">All campuses</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            @endif
            <button type="submit" class="secondary-button">Apply filters</button>
        </form>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Shift name</th>
                        <th>Campus</th>
                        <th>Opening time</th>
                        <th>Closing time</th>
                        <th>Operating days</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($schoolHours as $shift)
                        <tr>
                            <td><strong>{{ $shift->shift_name }}</strong></td>
                            <td>{{ $shift->branch->name }}</td>
                            <td>{{ $shift->opening_time }}</td>
                            <td>{{ $shift->closing_time }}</td>
                            <td>{{ $shift->operating_days_label ?: '—' }}</td>
                            <td>
                                @if ($shift->is_active)
                                    <span class="status-pill active">Active</span>
                                @else
                                    <span class="status-pill inactive">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('school-hours.edit', $shift) }}" class="table-link">Edit</a>
                                <form method="POST" action="{{ route('school-hours.destroy', $shift) }}" onsubmit="return confirm('Delete this shift?');" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="table-link" style="background:none; border:none; cursor:pointer; color:var(--danger, #ef4444); padding:0;">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No school hours shifts configured.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $schoolHours->links() }}
    </section>
@endsection
