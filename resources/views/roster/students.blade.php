@extends('layouts.app')

@section('title', 'Student Roster')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Roster</p>
            <h1>Student roster</h1>
            <p class="intro-copy">Class-by-class student roll, registration info, and enrollment status.</p>
        </div>
    </section>

    <section class="content-card">
        <form method="GET" action="{{ route('roster.students') }}" class="filter-form">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by student name or admission #..." aria-label="Search student roster">
            @if ($branches->count() > 1)
                <select name="branch_id" aria-label="Filter by campus">
                    <option value="">All campuses</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) request('branch_id', $selectedBranchId) === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            @endif
            <select name="grade_id" aria-label="Filter by grade">
                <option value="">All grades</option>
                @foreach ($grades as $grade)
                    <option value="{{ $grade->id }}" @selected((string) request('grade_id') === (string) $grade->id)>{{ $grade->name }}</option>
                @endforeach
            </select>
            <select name="section_id" aria-label="Filter by section">
                <option value="">All sections</option>
                @foreach ($sections as $section)
                    <option value="{{ $section->id }}" @selected((string) request('section_id') === (string) $section->id)>{{ $section->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="secondary-button">Apply filters</button>
        </form>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Roll #</th>
                        <th>Admission #</th>
                        <th>Student Name</th>
                        <th>Grade</th>
                        <th>Section</th>
                        <th>Gender</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($enrollments as $enrollment)
                        <tr>
                            <td><strong>{{ $enrollment->roll_number ?? '—' }}</strong></td>
                            <td><code>{{ $enrollment->student->admission_number }}</code></td>
                            <td>{{ $enrollment->student->full_name }}</td>
                            <td>{{ $enrollment->grade->name }}</td>
                            <td>{{ $enrollment->section?->name ?? 'Unassigned' }}</td>
                            <td>{{ ucfirst($enrollment->student->gender ?? '—') }}</td>
                            <td><span class="status-pill {{ $enrollment->student->status }}">{{ ucfirst($enrollment->student->status) }}</span></td>
                            <td>
                                <a href="{{ route('students.show', $enrollment->student) }}" class="table-link">Student File</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8">No active students found in this roster view.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $enrollments->links() }}
    </section>
@endsection
