@extends('layouts.app')

@section('title', "Assessment: {$assessment->title}")

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Exams & Assessments / Overview</p>
            <h1>{{ $assessment->title }}</h1>
            <p class="intro-copy">Subject: <strong>{{ $assessment->subject_name }}</strong> &bull; {{ $assessment->grade->name }}{{ $assessment->section ? ' (' . $assessment->section->name . ')' : '' }} &bull; {{ $assessment->academicYear->name }}</p>
        </div>
        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
            <a href="{{ route('assessments.index') }}" class="secondary-link">All assessments</a>
            <a href="{{ route('grade-entries.edit', $assessment) }}" class="primary-link">Enter / Edit student grades</a>
            <a href="{{ route('grade-entries.show', $assessment) }}" class="secondary-link">View class marksheet</a>
            <a href="{{ route('assessments.edit', $assessment) }}" class="secondary-link">Edit setup</a>
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
            <span class="metric-label">Graded students</span>
            <strong>{{ $gradesCount }}</strong>
            <small>Student records entered</small>
        </article>
        <article class="metric-card">
            <span class="metric-label">Class average</span>
            <strong>{{ $averageScore }} / {{ number_format($assessment->max_score, 0) }}</strong>
            <small>Mean score</small>
        </article>
        <article class="metric-card">
            <span class="metric-label">Highest / Lowest</span>
            <strong>{{ $highestScore }} / {{ $lowestScore }}</strong>
            <small>Max: {{ number_format($assessment->max_score, 0) }} | Pass: {{ number_format($assessment->pass_mark, 0) }}</small>
        </article>
        <article class="metric-card">
            <span class="metric-label">Pass rate</span>
            <strong>{{ $passRate }}%</strong>
            <small>Above pass mark ({{ number_format($assessment->pass_mark, 0) }})</small>
        </article>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
        <section class="content-card">
            <h2 style="font-size: 1.15rem; margin-bottom: 1rem;">Setup Specification</h2>
            <div style="display: grid; gap: 0.75rem;">
                <div>
                    <span style="color: var(--muted); font-size: 0.85rem; display: block;">Subject & Component</span>
                    <strong>{{ $assessment->subject_name }} &bull; {{ $assessment->assessmentType->name }}</strong>
                </div>
                <div>
                    <span style="color: var(--muted); font-size: 0.85rem; display: block;">Campus & Term</span>
                    <span>{{ $assessment->branch->name }} &bull; {{ $assessment->term?->name ?? 'Full Year' }}</span>
                </div>
                <div>
                    <span style="color: var(--muted); font-size: 0.85rem; display: block;">Grading Scale</span>
                    <span>{{ $assessment->gradeScale?->name ?? 'Standard Percentage' }}</span>
                </div>
                <div>
                    <span style="color: var(--muted); font-size: 0.85rem; display: block;">Exam Date</span>
                    <span>{{ $assessment->assessment_date ? $assessment->assessment_date->format('M d, Y') : 'Not scheduled' }}</span>
                </div>
                <div>
                    <span style="color: var(--muted); font-size: 0.85rem; display: block;">Weighting</span>
                    <span>{{ number_format($assessment->weight_percentage, 1) }}% of subject total</span>
                </div>
                @if ($assessment->description)
                    <div>
                        <span style="color: var(--muted); font-size: 0.85rem; display: block;">Instructions</span>
                        <p style="margin: 0.25rem 0 0; color: var(--text);">{{ $assessment->description }}</p>
                    </div>
                @endif
            </div>
        </section>

        <section class="content-card">
            <h2 style="font-size: 1.15rem; margin-bottom: 1rem;">Grading Progress</h2>
            @if ($gradesCount === 0)
                <div style="text-align: center; padding: 2rem; color: var(--muted);">
                    <p style="margin-bottom: 1rem;">No grades have been entered for this assessment yet.</p>
                    <a href="{{ route('grade-entries.edit', $assessment) }}" class="primary-link">Open Grade Entry sheet</a>
                </div>
            @else
                <p>Grades have been recorded for <strong>{{ $gradesCount }}</strong> students.</p>
                <div style="margin-top: 1rem; display: flex; gap: 0.75rem;">
                    <a href="{{ route('grade-entries.edit', $assessment) }}" class="primary-link">Modify marks</a>
                    <a href="{{ route('grade-entries.show', $assessment) }}" class="secondary-link">View marksheet</a>
                </div>
            @endif
        </section>
    </div>
@endsection
