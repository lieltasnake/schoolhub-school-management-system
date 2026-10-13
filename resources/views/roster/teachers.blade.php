@extends('layouts.app')

@section('title', 'Teachers Roster')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Roster</p>
            <h1>Teachers roster</h1>
            <p class="intro-copy">Comprehensive directory of faculty, their assigned classes, subjects, and contact information.</p>
        </div>
    </section>

    <section class="content-card">
        <form method="GET" action="{{ route('roster.teachers') }}" class="filter-form">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by name, ID, qualification..." aria-label="Search teachers">
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
                @foreach (['active', 'on_leave', 'resigned', 'terminated'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
            <button type="submit" class="secondary-button">Apply filters</button>
        </form>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Teacher</th>
                        <th>Email & Phone</th>
                        <th>Qualification</th>
                        <th>Assigned Classes</th>
                        <th>Assigned Subjects</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($teachers as $teacher)
                        <tr>
                            <td><strong>{{ $teacher->employee_id }}</strong></td>
                            <td>{{ $teacher->user->name }}</td>
                            <td>
                                <div>{{ $teacher->user->email }}</div>
                                <small style="color:var(--text-muted, #64748b);">{{ $teacher->phone ?? '—' }}</small>
                            </td>
                            <td>{{ $teacher->qualification ?? '—' }}</td>
                            <td>
                                @php
                                    $assignedClasses = $teacher->assignments->map(fn($a) => $a->grade->name . ($a->section ? ' (' . $a->section->name . ')' : ''))->unique();
                                @endphp
                                @if ($assignedClasses->isNotEmpty())
                                    {{ $assignedClasses->implode(', ') }}
                                @else
                                    <span style="color:var(--text-muted, #64748b);">None</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $assignedSubjects = $teacher->assignments->pluck('subject.name')->filter()->unique();
                                @endphp
                                @if ($assignedSubjects->isNotEmpty())
                                    {{ $assignedSubjects->implode(', ') }}
                                @else
                                    <span style="color:var(--text-muted, #64748b);">None</span>
                                @endif
                            </td>
                            <td><span class="status-pill {{ $teacher->status }}">{{ ucfirst(str_replace('_', ' ', $teacher->status)) }}</span></td>
                            <td>
                                <a href="{{ route('teachers.show', $teacher) }}" class="table-link">Profile</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8">No teachers found in roster.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $teachers->links() }}
    </section>
@endsection
