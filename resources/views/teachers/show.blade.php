@extends('layouts.app')

@section('title', 'Teacher Profile — ' . $teacher->user->name)

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Academic Staff</p>
            <h1>{{ $teacher->user->name }}</h1>
            <p class="intro-copy">{{ $teacher->employee_id }} &bull; {{ $teacher->branch->name }} &bull; {{ $teacher->specialization ?? 'General' }}</p>
        </div>
        <div style="display:flex; gap:0.5rem;">
            <a href="{{ route('teachers.edit', $teacher) }}" class="primary-link">Edit profile</a>
            <a href="{{ route('teachers.index') }}" class="secondary-link">Back to list</a>
        </div>
    </section>

    <div style="display:grid; grid-template-columns: 1fr 2fr; gap:1.5rem; align-items:start;">
        <section class="content-card">
            <h2>Personal & Employment Info</h2>
            <dl style="display:grid; grid-template-columns: 1fr 1fr; gap: 0.75rem 1rem; margin-top: 1rem; font-size: 0.95rem;">
                <dt style="color:var(--text-muted, #64748b);">Email</dt>
                <dd>{{ $teacher->user->email }}</dd>

                <dt style="color:var(--text-muted, #64748b);">Phone</dt>
                <dd>{{ $teacher->phone ?? '—' }}</dd>

                <dt style="color:var(--text-muted, #64748b);">Campus</dt>
                <dd>{{ $teacher->branch->name }}</dd>

                <dt style="color:var(--text-muted, #64748b);">Qualification</dt>
                <dd>{{ $teacher->qualification ?? '—' }}</dd>

                <dt style="color:var(--text-muted, #64748b);">Specialization</dt>
                <dd>{{ $teacher->specialization ?? '—' }}</dd>

                <dt style="color:var(--text-muted, #64748b);">Joining date</dt>
                <dd>{{ $teacher->joining_date?->format('M d, Y') ?? '—' }}</dd>

                <dt style="color:var(--text-muted, #64748b);">Status</dt>
                <dd><span class="status-pill {{ $teacher->status }}">{{ ucfirst(str_replace('_', ' ', $teacher->status)) }}</span></dd>
            </dl>

            <form method="POST" action="{{ route('teachers.destroy', $teacher) }}" style="margin-top:2rem;" onsubmit="return confirm('Are you sure you want to remove this teacher?');">
                @csrf
                @method('DELETE')
                <button type="submit" style="color:var(--danger, #ef4444); background:none; border:none; cursor:pointer; font-weight:600; padding:0;">Delete teacher profile</button>
            </form>
        </section>

        <section class="content-card">
            <h2>Current Class & Subject Assignments</h2>
            <div class="table-wrap" style="margin-top:1rem;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Grade</th>
                            <th>Section</th>
                            <th>Subject</th>
                            <th>Role</th>
                            <th>Academic Year</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($teacher->assignments as $assignment)
                            <tr>
                                <td>{{ $assignment->grade->name }}</td>
                                <td>{{ $assignment->section?->name ?? 'All sections' }}</td>
                                <td>{{ $assignment->subject?->name ?? 'All subjects' }}</td>
                                <td><span class="status-pill active">{{ ucfirst(str_replace('_', ' ', $assignment->role)) }}</span></td>
                                <td>{{ $assignment->academicYear?->name ?? 'All years' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5">No current assignments.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <h2 style="margin-top:2rem;">Weekly Teaching Schedule</h2>
            <div class="table-wrap" style="margin-top:1rem;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Day</th>
                            <th>Period</th>
                            <th>Section</th>
                            <th>Subject</th>
                            <th>Room</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($teacher->timetables as $slot)
                            <tr>
                                <td><strong>{{ $slot->day_of_week }}</strong></td>
                                <td>{{ $slot->period->name }} ({{ $slot->period->start_time }} - {{ $slot->period->end_time }})</td>
                                <td>{{ $slot->section->grade->name }} — {{ $slot->section->name }}</td>
                                <td>{{ $slot->subject->name }}</td>
                                <td>{{ $slot->classroom?->room_number ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5">No scheduled classes in the timetable.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
