@extends('layouts.app')

@section('title', "Marksheet: {$assessment->title}")

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Exams & Assessments / Marksheet</p>
            <h1>Class marksheet: {{ $assessment->title }}</h1>
            <p class="intro-copy">
                Subject: <strong>{{ $assessment->subject_name }}</strong> &bull;
                {{ $assessment->grade->name }}{{ $assessment->section ? ' (' . $assessment->section->name . ')' : '' }} &bull;
                {{ $assessment->academicYear->name }}
            </p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <a href="{{ route('grade-entries.index') }}" class="secondary-link">All assessments</a>
            <a href="{{ route('grade-entries.edit', $assessment) }}" class="primary-link">Edit grades</a>
            <button type="button" class="secondary-button" onclick="window.print()">Print marksheet</button>
        </div>
    </section>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="dashboard-grid" style="margin-bottom: 1.5rem;">
        <article class="metric-card">
            <span class="metric-label">Assessed students</span>
            <strong>{{ $presentCount }} / {{ $gradesCount }}</strong>
            <small>{{ $absentCount }} absent or exempt</small>
        </article>
        <article class="metric-card">
            <span class="metric-label">Class average</span>
            <strong>{{ $averageScore }} / {{ number_format($assessment->max_score, 0) }}</strong>
            <small>Passing mark: {{ number_format($assessment->pass_mark, 0) }}</small>
        </article>
        <article class="metric-card">
            <span class="metric-label">Highest mark</span>
            <strong>{{ $highestScore }} / {{ number_format($assessment->max_score, 0) }}</strong>
            <small>Lowest: {{ $lowestScore }}</small>
        </article>
        <article class="metric-card">
            <span class="metric-label">Pass rate</span>
            <strong>{{ $passRate }}%</strong>
            <small>{{ $assessment->grades->filter(fn ($g) => $g->score >= $assessment->pass_mark)->count() }} passed</small>
        </article>
    </div>

    <section class="content-card">
        <h2 style="font-size: 1.15rem; margin-bottom: 1rem;">Performance Results Table</h2>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">Rank</th>
                        <th>Admission #</th>
                        <th>Student name</th>
                        <th>Status</th>
                        <th>Score (Max: {{ number_format($assessment->max_score, 0) }})</th>
                        <th>Percentage</th>
                        <th>Grade</th>
                        <th>GPA Pts</th>
                        <th>Outcome</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @php $rank = 1; @endphp
                    @forelse ($grades as $grade)
                        @php
                            $isPass = $grade->status === 'present' && $grade->score !== null && $grade->score >= $assessment->pass_mark;
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $grade->status === 'present' && $grade->score !== null ? $rank++ : '—' }}</strong>
                            </td>
                            <td>
                                <code>{{ $grade->student->admission_number }}</code>
                            </td>
                            <td>
                                <a href="{{ route('students.show', $grade->student) }}" class="table-link" style="font-weight: 600;">
                                    {{ $grade->student->full_name }}
                                </a>
                            </td>
                            <td>
                                <span class="badge" style="background: var(--panel-soft); text-transform: capitalize;">
                                    {{ $grade->status }}
                                </span>
                            </td>
                            <td>
                                <strong>{{ $grade->score !== null ? number_format($grade->score, 2) : '—' }}</strong>
                            </td>
                            <td>
                                {{ $grade->percentage !== null ? number_format($grade->percentage, 1) . '%' : '—' }}
                            </td>
                            <td>
                                <span class="badge" style="font-weight: 700; background: {{ $isPass ? 'var(--panel-soft)' : '#fee2e2' }}; color: {{ $isPass ? 'var(--text)' : '#b91c1c' }};">
                                    {{ $grade->letter_grade ?? '—' }}
                                </span>
                            </td>
                            <td>{{ $grade->grade_point !== null ? number_format($grade->grade_point, 2) : '—' }}</td>
                            <td>
                                @if ($grade->status === 'present' && $grade->score !== null)
                                    <span class="badge status-{{ $isPass ? 'active' : 'inactive' }}">
                                        {{ $isPass ? 'Passed' : 'Failed' }}
                                    </span>
                                @else
                                    <span style="color: var(--muted);">—</span>
                                @endif
                            </td>
                            <td>{{ $grade->remarks ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 2rem; color: var(--muted);">
                                No grade entries recorded for this assessment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
