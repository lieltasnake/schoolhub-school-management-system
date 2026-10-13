@extends('layouts.app')

@section('title', 'Teacher Assignments')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Academic Staff</p>
            <h1>Teacher assignments</h1>
            <p class="intro-copy">Assign teachers to grades, sections, subjects, and roles.</p>
        </div>
        <a href="{{ route('teacher-assignments.create') }}" class="primary-link">New assignment</a>
    </section>

    <section class="content-card">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <form method="GET" action="{{ route('teacher-assignments.index') }}" class="filter-form">
            @if ($branches->count() > 1)
                <select name="branch_id" aria-label="Filter by campus">
                    <option value="">All campuses</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            @endif
            <select name="teacher_id" aria-label="Filter by teacher">
                <option value="">All teachers</option>
                @foreach ($teachers as $t)
                    <option value="{{ $t->id }}" @selected((string) request('teacher_id') === (string) $t->id)>{{ $t->user->name }} ({{ $t->employee_id }})</option>
                @endforeach
            </select>
            <select name="grade_id" aria-label="Filter by grade">
                <option value="">All grades</option>
                @foreach ($grades as $g)
                    <option value="{{ $g->id }}" @selected((string) request('grade_id') === (string) $g->id)>{{ $g->name }}</option>
                @endforeach
            </select>
            <select name="role" aria-label="Filter by role">
                <option value="">All roles</option>
                @foreach (['subject_teacher', 'class_teacher', 'assistant_teacher', 'head_of_department'] as $role)
                    <option value="{{ $role }}" @selected(request('role') === $role)>{{ ucfirst(str_replace('_', ' ', $role)) }}</option>
                @endforeach
            </select>
            <button type="submit" class="secondary-button">Apply filters</button>
        </form>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Teacher</th>
                        <th>Grade</th>
                        <th>Section</th>
                        <th>Subject</th>
                        <th>Role</th>
                        <th>Academic Year</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assignments as $assignment)
                        <tr>
                            <td><strong>{{ $assignment->teacher->user->name }}</strong> ({{ $assignment->teacher->employee_id }})</td>
                            <td>{{ $assignment->grade->name }}</td>
                            <td>{{ $assignment->section?->name ?? 'All sections' }}</td>
                            <td>{{ $assignment->subject?->name ?? 'All subjects' }}</td>
                            <td><span class="status-pill active">{{ ucfirst(str_replace('_', ' ', $assignment->role)) }}</span></td>
                            <td>{{ $assignment->academicYear?->name ?? 'All years' }}</td>
                            <td>{{ $assignment->notes ?? '—' }}</td>
                            <td>
                                <form method="POST" action="{{ route('teacher-assignments.destroy', $assignment) }}" onsubmit="return confirm('Remove this assignment?');" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="table-link" style="background:none; border:none; cursor:pointer; color:var(--danger, #ef4444); padding:0;">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8">No assignments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $assignments->links() }}
    </section>
@endsection
