@extends('layouts.app')

@section('title', 'Staff Attendance')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Attendance</p>
            <h1>Staff attendance</h1>
            <p class="intro-copy">Track faculty and school staff check-in times, absences, and leaves.</p>
        </div>
        <a href="{{ route('staff-attendance.create') }}" class="primary-link">Take staff attendance</a>
    </section>

    <section class="content-card">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="GET" action="{{ route('staff-attendance.index') }}" class="filter-form">
            <input type="date" name="attendance_date" value="{{ request('attendance_date') }}" aria-label="Filter by date">
            @if ($branches->count() > 1)
                <select name="branch_id" aria-label="Filter by campus">
                    <option value="">All campuses</option>
                    @foreach ($branches as $b)
                        <option value="{{ $b->id }}" @selected((string) request('branch_id') === (string) $b->id)>{{ $b->name }}</option>
                    @endforeach
                </select>
            @endif
            <select name="status" aria-label="Filter by status">
                <option value="">All statuses</option>
                @foreach (['present', 'absent', 'late', 'on_leave', 'half_day'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
            <button type="submit" class="secondary-button">Apply filters</button>
        </form>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Staff Member</th>
                        <th>Campus</th>
                        <th>Check-in / Check-out</th>
                        <th>Status</th>
                        <th>Remarks</th>
                        <th>Recorded By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendances as $record)
                        <tr>
                            <td><strong>{{ $record->attendance_date->format('M d, Y') }}</strong></td>
                            <td>{{ $record->user->name }} ({{ $record->user->email }})</td>
                            <td>{{ $record->branch->name }}</td>
                            <td>
                                {{ $record->check_in_time ?? '—' }} – {{ $record->check_out_time ?? '—' }}
                            </td>
                            <td>
                                @php
                                    $pillClass = match($record->status) {
                                        'present' => 'active',
                                        'absent' => 'inactive',
                                        'late' => 'pending',
                                        'on_leave' => 'pending',
                                        'half_day' => 'pending',
                                        default => 'inactive',
                                    };
                                @endphp
                                <span class="status-pill {{ $pillClass }}">{{ ucfirst(str_replace('_', ' ', $record->status)) }}</span>
                            </td>
                            <td>{{ $record->remarks ?? '—' }}</td>
                            <td>{{ $record->recordedBy?->name ?? 'System' }}</td>
                            <td>
                                <form method="POST" action="{{ route('staff-attendance.destroy', $record) }}" onsubmit="return confirm('Delete this attendance record?');" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="table-link" style="background:none; border:none; cursor:pointer; color:var(--danger, #ef4444); padding:0;">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8">No staff attendance records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $attendances->links() }}
    </section>
@endsection
