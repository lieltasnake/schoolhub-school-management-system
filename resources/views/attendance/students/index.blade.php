@extends('layouts.app')

@section('title', 'Student Attendance')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Attendance</p>
            <h1>Student attendance</h1>
            <p class="intro-copy">View and manage daily student attendance logs, absences, and excuses.</p>
        </div>
        <a href="{{ route('student-attendance.create') }}" class="primary-link">Take attendance</a>
    </section>

    <section class="content-card">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="GET" action="{{ route('student-attendance.index') }}" class="filter-form">
            <input type="date" name="attendance_date" value="{{ request('attendance_date') }}" aria-label="Filter by date">
            <select name="section_id" aria-label="Filter by section">
                <option value="">All sections</option>
                @foreach ($sections as $sec)
                    <option value="{{ $sec->id }}" @selected((string) request('section_id') === (string) $sec->id)>{{ $sec->grade->name }} &bull; {{ $sec->name }}</option>
                @endforeach
            </select>
            <select name="status" aria-label="Filter by status">
                <option value="">All statuses</option>
                @foreach (['present', 'absent', 'late', 'excused'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <button type="submit" class="secondary-button">Apply filters</button>
        </form>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Student</th>
                        <th>Section</th>
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
                            <td>{{ $record->student->full_name }} ({{ $record->student->admission_number }})</td>
                            <td>{{ $record->section->grade->name }} &bull; {{ $record->section->name }}</td>
                            <td>
                                @php
                                    $pillClass = match($record->status) {
                                        'present' => 'active',
                                        'absent' => 'inactive',
                                        'late' => 'pending',
                                        'excused' => 'pending',
                                        default => 'inactive',
                                    };
                                @endphp
                                <span class="status-pill {{ $pillClass }}">{{ ucfirst($record->status) }}</span>
                            </td>
                            <td>{{ $record->remarks ?? '—' }}</td>
                            <td>{{ $record->recordedBy?->name ?? 'System' }}</td>
                            <td>
                                <form method="POST" action="{{ route('student-attendance.destroy', $record) }}" onsubmit="return confirm('Delete this attendance record?');" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="table-link" style="background:none; border:none; cursor:pointer; color:var(--danger, #ef4444); padding:0;">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No attendance records found for this criteria.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $attendances->links() }}
    </section>
@endsection
