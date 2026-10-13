@extends('layouts.app')

@section('title', 'Student Enrollments')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Students</p>
            <h1>Enrollment management</h1>
            <p class="intro-copy">Manage active student placements across academic years, grades, sections, and roll numbers.</p>
        </div>
        <a href="{{ route('enrollments.create') }}" class="primary-link">Enroll student</a>
    </section>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <div class="dashboard-grid" style="margin-bottom: 1.5rem;">
        <article class="metric-card">
            <span class="metric-label">Total enrollments</span>
            <strong>{{ $totalEnrollments }}</strong>
            <small>All placement records</small>
        </article>
        <article class="metric-card">
            <span class="metric-label">Active enrollment</span>
            <strong>{{ $activeEnrollments }}</strong>
            <small>Currently attending</small>
        </article>
        <article class="metric-card">
            <span class="metric-label">Promoted records</span>
            <strong>{{ $promotedEnrollments }}</strong>
            <small>Historical completed sessions</small>
        </article>
    </div>

    <section class="content-card">
        <form method="GET" action="{{ route('enrollments.index') }}" class="filter-form">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by student name or admission #" aria-label="Search enrollments">

            @if ($branches->count() > 1)
                <select name="branch_id" aria-label="Filter by campus">
                    <option value="">All campuses</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) request('branch_id', $selectedBranchId) === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            @endif

            <select name="academic_year_id" aria-label="Filter by academic year">
                <option value="">All academic years</option>
                @foreach ($academicYears as $year)
                    <option value="{{ $year->id }}" @selected((string) request('academic_year_id') === (string) $year->id)>{{ $year->name }}</option>
                @endforeach
            </select>

            <select name="grade_id" aria-label="Filter by grade">
                <option value="">All grades</option>
                @foreach ($grades as $grade)
                    <option value="{{ $grade->id }}" @selected((string) request('grade_id') === (string) $grade->id)>{{ $grade->name }}</option>
                @endforeach
            </select>

            <select name="status" aria-label="Filter by status">
                <option value="">All statuses</option>
                @foreach (['active', 'promoted', 'repeated', 'transferred', 'completed'] as $statusOption)
                    <option value="{{ $statusOption }}" @selected(request('status') === $statusOption)>{{ ucfirst($statusOption) }}</option>
                @endforeach
            </select>

            <button type="submit" class="secondary-button">Filter</button>
            @if (request()->hasAny(['search', 'branch_id', 'academic_year_id', 'grade_id', 'status']))
                <a href="{{ route('enrollments.index') }}" class="text-link" style="align-self: center;">Reset</a>
            @endif
        </form>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Admission #</th>
                        <th>Student name</th>
                        <th>Campus</th>
                        <th>Academic year</th>
                        <th>Grade / Class</th>
                        <th>Section</th>
                        <th>Roll #</th>
                        <th>Enrolled date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($enrollments as $enrollment)
                        <tr>
                            <td>
                                <strong>{{ $enrollment->student->admission_number }}</strong>
                            </td>
                            <td>
                                <a href="{{ route('students.show', $enrollment->student) }}" class="table-link" style="font-weight: 600;">
                                    {{ $enrollment->student->full_name }}
                                </a>
                            </td>
                            <td>{{ $enrollment->student->branch->name }}</td>
                            <td>{{ $enrollment->academicYear->name }}</td>
                            <td>{{ $enrollment->grade->name }}</td>
                            <td>{{ $enrollment->section?->name ?? 'Unassigned' }}</td>
                            <td>{{ $enrollment->roll_number ?? '—' }}</td>
                            <td>{{ $enrollment->enrolled_at->format('M d, Y') }}</td>
                            <td>
                                <span class="badge status-{{ $enrollment->status }}">
                                    {{ ucfirst($enrollment->status) }}
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                    <a href="{{ route('enrollments.edit', $enrollment) }}" class="text-link">Edit</a>
                                    <form method="POST" action="{{ route('enrollments.destroy', $enrollment) }}" onsubmit="return confirm('Delete this enrollment record?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-link" style="color: var(--danger); background: none; border: none; padding: 0; cursor: pointer;">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 2rem; color: var(--muted);">
                                No enrollment records found matching criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($enrollments->hasPages())
            <div style="margin-top: 1.5rem;">
                {{ $enrollments->links() }}
            </div>
        @endif
    </section>
@endsection
