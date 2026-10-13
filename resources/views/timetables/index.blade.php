@extends('layouts.app')

@section('title', 'Timetable Management')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Academics</p>
            <h1>Timetable schedule</h1>
            <p class="intro-copy">Manage master class timetable schedules, teacher slots, and classroom allocation.</p>
        </div>
        <a href="{{ route('timetables.create') }}" class="primary-link">Add timetable slot</a>
    </section>

    <section class="content-card">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <form method="GET" action="{{ route('timetables.index') }}" class="filter-form">
            @if ($branches->count() > 1)
                <select name="branch_id" aria-label="Filter by campus">
                    <option value="">All campuses</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            @endif
            <select name="section_id" aria-label="Filter by section">
                <option value="">All sections</option>
                @foreach ($sections as $sec)
                    <option value="{{ $sec->id }}" @selected((string) request('section_id') === (string) $sec->id)>{{ $sec->grade->name }} &bull; {{ $sec->name }}</option>
                @endforeach
            </select>
            <select name="teacher_id" aria-label="Filter by teacher">
                <option value="">All teachers</option>
                @foreach ($teachers as $tch)
                    <option value="{{ $tch->id }}" @selected((string) request('teacher_id') === (string) $tch->id)>{{ $tch->user->name }}</option>
                @endforeach
            </select>
            <select name="day_of_week" aria-label="Filter by day">
                <option value="">All days</option>
                @foreach ($days as $day)
                    <option value="{{ $day }}" @selected(request('day_of_week') === $day)>{{ $day }}</option>
                @endforeach
            </select>
            <button type="submit" class="secondary-button">Apply filters</button>
        </form>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Day</th>
                        <th>Period</th>
                        <th>Time</th>
                        <th>Section</th>
                        <th>Subject</th>
                        <th>Teacher</th>
                        <th>Classroom</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($timetables as $slot)
                        <tr>
                            <td><strong>{{ $slot->day_of_week }}</strong></td>
                            <td>{{ $slot->period->name }}</td>
                            <td>{{ $slot->period->start_time }} – {{ $slot->period->end_time }}</td>
                            <td>{{ $slot->section->grade->name }} &bull; {{ $slot->section->name }}</td>
                            <td>{{ $slot->subject->name }}</td>
                            <td>{{ $slot->teacher->user->name }}</td>
                            <td>{{ $slot->classroom?->room_number ?? '—' }}</td>
                            <td>
                                <a href="{{ route('timetables.edit', $slot) }}" class="table-link">Edit</a>
                                <form method="POST" action="{{ route('timetables.destroy', $slot) }}" onsubmit="return confirm('Delete this timetable slot?');" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="table-link" style="background:none; border:none; cursor:pointer; color:var(--danger, #ef4444); padding:0;">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8">No timetable slots found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $timetables->links() }}
    </section>
@endsection
