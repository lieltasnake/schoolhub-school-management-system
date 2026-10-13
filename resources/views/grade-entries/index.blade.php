@extends('layouts.app')

@section('title', 'Grade Entry')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Exams & Assessments</p>
            <h1>Grade entry & mark recording</h1>
            <p class="intro-copy">Enter exam scores, calculate letter grades and GPA points, and evaluate student performance.</p>
        </div>
        <a href="{{ route('assessments.create') }}" class="primary-link">New assessment setup</a>
    </section>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <section class="content-card">
        <form method="GET" action="{{ route('grade-entries.index') }}" class="filter-form">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search assessment title or subject" aria-label="Search assessments">

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

            <button type="submit" class="secondary-button">Filter</button>
            @if (request()->hasAny(['search', 'branch_id', 'academic_year_id', 'grade_id']))
                <a href="{{ route('grade-entries.index') }}" class="text-link" style="align-self: center;">Reset</a>
            @endif
        </form>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Assessment</th>
                        <th>Subject</th>
                        <th>Class & section</th>
                        <th>Max mark</th>
                        <th>Passing score</th>
                        <th>Marks entered</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assessments as $assessment)
                        <tr>
                            <td>
                                <strong>{{ $assessment->title }}</strong>
                                <small style="display: block; color: var(--muted);">{{ $assessment->assessmentType->name }} &bull; {{ $assessment->academicYear->name }}</small>
                            </td>
                            <td>{{ $assessment->subject_name }}</td>
                            <td>
                                {{ $assessment->grade->name }}
                                @if ($assessment->section)
                                    ({{ $assessment->section->name }})
                                @endif
                            </td>
                            <td>{{ number_format($assessment->max_score, 0) }} pts</td>
                            <td>{{ number_format($assessment->pass_mark, 0) }} pts</td>
                            <td>
                                <strong>{{ $assessment->grades_count }}</strong> students
                            </td>
                            <td>
                                <span class="badge status-{{ $assessment->status === 'completed' ? 'active' : 'planned' }}">
                                    {{ ucfirst($assessment->status) }}
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                    <a href="{{ route('grade-entries.edit', $assessment) }}" class="primary-link" style="padding: 0.35rem 0.65rem; font-size: 0.825rem;">
                                        Enter marks
                                    </a>
                                    @if ($assessment->grades_count > 0)
                                        <a href="{{ route('grade-entries.show', $assessment) }}" class="secondary-link" style="padding: 0.35rem 0.65rem; font-size: 0.825rem;">
                                            Marksheet
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2rem; color: var(--muted);">
                                No assessments found for grading.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($assessments->hasPages())
            <div style="margin-top: 1.5rem;">
                {{ $assessments->links() }}
            </div>
        @endif
    </section>
@endsection
