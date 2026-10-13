@extends('layouts.app')

@section('title', 'Student Grades Roster')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Roster</p>
            <h1>Student grades roster</h1>
            <p class="intro-copy">Overview of recorded assessment scores, percentages, and letter grades across classes.</p>
        </div>
    </section>

    <section class="content-card">
        <form method="GET" action="{{ route('roster.grades') }}" class="filter-form">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by student name or admission #..." aria-label="Search student grades">
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
                @foreach ($sections as $section)
                    <option value="{{ $section->id }}" @selected((string) request('section_id') === (string) $section->id)>{{ $section->grade->name }} &bull; {{ $section->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="secondary-button">Apply filters</button>
        </form>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Admission #</th>
                        <th>Student Name</th>
                        <th>Assessment</th>
                        <th>Subject</th>
                        <th>Score / Max</th>
                        <th>Percentage</th>
                        <th>Grade</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($grades as $grade)
                        <tr>
                            <td><code>{{ $grade->student->admission_number }}</code></td>
                            <td>{{ $grade->student->full_name }}</td>
                            <td>{{ $grade->assessment->title }}</td>
                            <td>{{ $grade->assessment->subject_name }}</td>
                            <td><strong>{{ $grade->score }}</strong> / {{ $grade->assessment->max_score }}</td>
                            <td>{{ $grade->percentage }}%</td>
                            <td><span class="status-pill active">{{ $grade->letter_grade ?? '—' }}</span></td>
                            <td><span class="status-pill {{ $grade->status }}">{{ ucfirst($grade->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="8">No student grades found matching criteria.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $grades->links() }}
    </section>
@endsection
