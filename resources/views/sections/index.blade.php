@extends('layouts.app')

@section('title', 'Sections & Classrooms')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">School setup</p>
            <h1>Sections and classrooms</h1>
            <p class="intro-copy">Manage class sections, streams, and physical classroom assignments.</p>
        </div>
        <a href="{{ route('sections.create') }}" class="primary-link">Add section</a>
    </section>

    <section class="content-card">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <form method="GET" action="{{ route('sections.index') }}" class="filter-form">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by name, code, or room" aria-label="Search sections">
            @if ($branches->count() > 1)
                <select name="branch_id" aria-label="Filter by campus">
                    <option value="">All campuses</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            @endif
            <select name="grade_id" aria-label="Filter by grade">
                <option value="">All grades</option>
                @foreach ($grades as $grade)
                    <option value="{{ $grade->id }}" @selected((string) request('grade_id') === (string) $grade->id)>{{ $grade->branch->name }} / {{ $grade->name }}</option>
                @endforeach
            </select>
            <select name="academic_year_id" aria-label="Filter by academic year">
                <option value="">All academic years</option>
                @foreach ($academicYears as $year)
                    <option value="{{ $year->id }}" @selected((string) request('academic_year_id') === (string) $year->id)>{{ $year->branch->name }} / {{ $year->name }}</option>
                @endforeach
            </select>
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
                        <th>Section</th>
                        <th>Grade</th>
                        <th>Campus</th>
                        <th>Academic year</th>
                        <th>Room</th>
                        <th>Capacity</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sections as $section)
                        <tr>
                            <td><strong>{{ $section->name }}</strong></td>
                            <td>{{ $section->grade->name }}</td>
                            <td>{{ $section->grade->branch->name }}</td>
                            <td>{{ $section->academicYear?->name ?? 'All years' }}</td>
                            <td>{{ $section->room_number ?? '—' }}</td>
                            <td>{{ $section->capacity }}</td>
                            <td><span class="status-pill {{ $section->status }}">{{ ucfirst($section->status) }}</span></td>
                            <td>
                                <a href="{{ route('sections.show', $section) }}" class="table-link">View</a>
                                <a href="{{ route('sections.edit', $section) }}" class="table-link">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8">No sections found for the selected scope.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $sections->links() }}
    </section>
@endsection
