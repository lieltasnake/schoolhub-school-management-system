@extends('layouts.app')

@section('title', 'Assessment Setup')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Exams & Assessments</p>
            <h1>Assessment setup & examinations</h1>
            <p class="intro-copy">Schedule tests, term exams, subjects, weightings, max scores, and grading scale assignments.</p>
        </div>
        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
            <a href="{{ route('grade-entries.index') }}" class="secondary-link">Grade Entry overview</a>
            <a href="{{ route('assessments.create') }}" class="primary-link">New assessment setup</a>
        </div>
    </section>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <div class="dashboard-grid" style="margin-bottom: 1.5rem;">
        <article class="metric-card">
            <span class="metric-label">Total assessments</span>
            <strong>{{ $totalAssessments }}</strong>
            <small>All scheduled and graded components</small>
        </article>
        <article class="metric-card">
            <span class="metric-label">Scheduled / Pending</span>
            <strong>{{ $scheduledAssessments }}</strong>
            <small>Awaiting examination & grading</small>
        </article>
        <article class="metric-card">
            <span class="metric-label">Completed</span>
            <strong>{{ $completedAssessments }}</strong>
            <small>Grading completed and finalized</small>
        </article>
    </div>

    <section class="content-card">
        <form method="GET" action="{{ route('assessments.index') }}" class="filter-form">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by title or subject" aria-label="Search assessments">

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
                @foreach (['draft', 'scheduled', 'completed', 'cancelled'] as $statusOption)
                    <option value="{{ $statusOption }}" @selected(request('status') === $statusOption)>{{ ucfirst($statusOption) }}</option>
                @endforeach
            </select>

            <button type="submit" class="secondary-button">Filter</button>
            @if (request()->hasAny(['search', 'branch_id', 'academic_year_id', 'grade_id', 'status']))
                <a href="{{ route('assessments.index') }}" class="text-link" style="align-self: center;">Reset</a>
            @endif
        </form>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Assessment title</th>
                        <th>Subject</th>
                        <th>Type</th>
                        <th>Class & section</th>
                        <th>Max mark</th>
                        <th>Weight</th>
                        <th>Date</th>
                        <th>Grades entered</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assessments as $assessment)
                        <tr>
                            <td>
                                <a href="{{ route('assessments.show', $assessment) }}" class="table-link" style="font-weight: 600;">
                                    {{ $assessment->title }}
                                </a>
                                <small style="display: block; color: var(--muted);">{{ $assessment->academicYear->name }}</small>
                            </td>
                            <td>
                                <strong>{{ $assessment->subject_name }}</strong>
                            </td>
                            <td>
                                <span class="badge" style="background: var(--panel-soft);">
                                    {{ $assessment->assessmentType->name }}
                                </span>
                            </td>
                            <td>
                                {{ $assessment->grade->name }}
                                @if ($assessment->section)
                                    ({{ $assessment->section->name }})
                                @endif
                            </td>
                            <td>{{ number_format($assessment->max_score, 0) }} pts</td>
                            <td>{{ number_format($assessment->weight_percentage, 0) }}%</td>
                            <td>{{ $assessment->assessment_date ? $assessment->assessment_date->format('M d, Y') : 'TBD' }}</td>
                            <td>
                                <strong>{{ $assessment->grades_count }}</strong>
                            </td>
                            <td>
                                <span class="badge status-{{ $assessment->status === 'completed' ? 'active' : ($assessment->status === 'cancelled' ? 'inactive' : 'planned') }}">
                                    {{ ucfirst($assessment->status) }}
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                    <a href="{{ route('grade-entries.edit', $assessment) }}" class="text-link" style="font-weight: 600;">Enter Marks</a>
                                    <a href="{{ route('assessments.show', $assessment) }}" class="text-link">View</a>
                                    <a href="{{ route('assessments.edit', $assessment) }}" class="text-link">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 2rem; color: var(--muted);">
                                No assessments created yet. Click "New assessment setup" to schedule an exam.
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
