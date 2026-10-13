@extends('layouts.app')

@section('title', "Grade Entry: {$assessment->title}")

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Exams & Assessments / Grade Entry</p>
            <h1>Grade entry: {{ $assessment->title }}</h1>
            <p class="intro-copy">
                Subject: <strong>{{ $assessment->subject_name }}</strong> &bull;
                Max marks: <strong>{{ number_format($assessment->max_score, 0) }}</strong> &bull;
                Pass threshold: <strong>{{ number_format($assessment->pass_mark, 0) }}</strong> &bull;
                Scale: <strong>{{ $assessment->gradeScale?->name ?? 'Standard' }}</strong>
            </p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <a href="{{ route('grade-entries.index') }}" class="secondary-link">Cancel</a>
            <a href="{{ route('assessments.show', $assessment) }}" class="secondary-link">Assessment info</a>
        </div>
    </section>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    @if ($enrollments->isEmpty())
        <section class="content-card">
            <div style="text-align: center; padding: 2.5rem; color: var(--muted);">
                <h3>No active students enrolled in this class</h3>
                <p>There are no students currently enrolled in {{ $assessment->grade->name }} for {{ $assessment->academicYear->name }}.</p>
                <a href="{{ route('enrollments.create') }}" class="primary-link" style="margin-top: 1rem; display: inline-block;">Enroll students</a>
            </div>
        </section>
    @else
        <section class="content-card">
            <form method="POST" action="{{ route('grade-entries.update', $assessment) }}">
                @csrf
                @method('PUT')

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <h2 style="font-size: 1.15rem; margin-bottom: 0.25rem;">Student Roster ({{ $enrollments->count() }})</h2>
                        <small style="color: var(--muted);">Enter numeric scores (0 to {{ number_format($assessment->max_score, 0) }}). Percentage and grades update automatically.</small>
                    </div>

                    <button type="submit" class="primary-button">Save all grades</button>
                </div>

                <div class="table-wrap">
                    <table class="data-table" id="grade-entry-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Admission #</th>
                                <th>Student name</th>
                                <th>Roll #</th>
                                <th>Status</th>
                                <th style="width: 140px;">Score (Max: {{ number_format($assessment->max_score, 0) }})</th>
                                <th style="width: 90px;">%</th>
                                <th style="width: 90px;">Grade</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $maxScore = (float) $assessment->max_score;
                                $scaleItems = $assessment->gradeScale ? $assessment->gradeScale->items->map(fn ($i) => [
                                    'letter' => $i->letter_grade,
                                    'min' => (float) $i->min_percentage,
                                    'max' => (float) $i->max_percentage,
                                    'is_pass' => (bool) $i->is_pass,
                                ])->values()->all() : [];
                            @endphp

                            @foreach ($enrollments as $index => $enrollment)
                                @php
                                    $student = $enrollment->student;
                                    $existing = $existingGrades->get($student->id);
                                    $score = $existing ? $existing->score : null;
                                    $status = $existing ? $existing->status : 'present';
                                    $remarks = $existing ? $existing->remarks : '';
                                    $pct = ($score !== null && $maxScore > 0) ? round(($score / $maxScore) * 100, 1) : null;
                                    $letter = $existing ? $existing->letter_grade : null;
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <code>{{ $student->admission_number }}</code>
                                        <input type="hidden" name="grades[{{ $index }}][student_id]" value="{{ $student->id }}">
                                    </td>
                                    <td>
                                        <strong>{{ $student->full_name }}</strong>
                                    </td>
                                    <td>{{ $enrollment->roll_number ?? '—' }}</td>
                                    <td>
                                        <select name="grades[{{ $index }}][status]" class="status-selector" data-row="{{ $index }}" style="padding: 0.35rem 0.5rem; font-size: 0.875rem;">
                                            <option value="present" @selected($status === 'present')>Present</option>
                                            <option value="absent" @selected($status === 'absent')>Absent</option>
                                            <option value="exempt" @selected($status === 'exempt')>Exempt</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="{{ $maxScore }}"
                                            name="grades[{{ $index }}][score]"
                                            id="score-{{ $index }}"
                                            value="{{ $score !== null ? $score : '' }}"
                                            class="score-input"
                                            data-row="{{ $index }}"
                                            style="width: 110px; font-weight: 600;"
                                            placeholder="Score"
                                            @disabled($status !== 'present')
                                        >
                                    </td>
                                    <td>
                                        <span id="pct-{{ $index }}" style="font-weight: 600; color: var(--navy);">
                                            {{ $pct !== null ? $pct . '%' : '—' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span id="grade-{{ $index }}" class="badge" style="background: var(--panel-soft); font-weight: 700;">
                                            {{ $letter ?? '—' }}
                                        </span>
                                    </td>
                                    <td>
                                        <input type="text" name="grades[{{ $index }}][remarks]" value="{{ $remarks }}" placeholder="Teacher notes" style="width: 100%;">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
                    <a href="{{ route('grade-entries.index') }}" class="secondary-link">Cancel</a>
                    <button type="submit" class="primary-button">Save all grades</button>
                </div>
            </form>
        </section>

        <script>
            const maxScore = {{ $maxScore }};
            const gradeTiers = @json($scaleItems);

            function computeGrade(score) {
                if (score === null || score === '' || isNaN(score)) return { pct: '—', letter: '—' };
                const pct = Math.round(((parseFloat(score) / maxScore) * 100) * 10) / 10;
                let letter = '—';

                if (gradeTiers.length > 0) {
                    for (const tier of gradeTiers) {
                        if (pct >= tier.min && pct <= tier.max) {
                            letter = tier.letter;
                            break;
                        }
                    }
                } else {
                    if (pct >= 90) letter = 'A+';
                    else if (pct >= 80) letter = 'A';
                    else if (pct >= 70) letter = 'B';
                    else if (pct >= 60) letter = 'C';
                    else if (pct >= 50) letter = 'D';
                    else letter = 'F';
                }

                return { pct: pct + '%', letter: letter };
            }

            document.querySelectorAll('.score-input').forEach(input => {
                input.addEventListener('input', function() {
                    const row = this.getAttribute('data-row');
                    const res = computeGrade(this.value);
                    document.getElementById('pct-' + row).innerText = res.pct;
                    document.getElementById('grade-' + row).innerText = res.letter;
                });
            });

            document.querySelectorAll('.status-selector').forEach(sel => {
                sel.addEventListener('change', function() {
                    const row = this.getAttribute('data-row');
                    const scoreInput = document.getElementById('score-' + row);
                    if (this.value !== 'present') {
                        scoreInput.disabled = true;
                        scoreInput.value = '';
                        document.getElementById('pct-' + row).innerText = '—';
                        document.getElementById('grade-' + row).innerText = this.value.toUpperCase();
                    } else {
                        scoreInput.disabled = false;
                    }
                });
            });
        </script>
    @endif
@endsection
