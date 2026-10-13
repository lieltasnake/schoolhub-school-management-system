@extends('layouts.app')

@section('title', 'Run Grade Promotion Wizard')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Students / Grade Promotion</p>
            <h1>Promotion wizard</h1>
            <p class="intro-copy">Select the current academic class and define student promotion decisions for the upcoming academic year.</p>
        </div>
        <a href="{{ route('grade-promotions.index') }}" class="secondary-link">Cancel</a>
    </section>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <!-- Step 1: Filter Source Class -->
    <section class="content-card" style="margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.15rem; margin-bottom: 0.75rem;">Step 1: Select Source Class</h2>
        <form method="GET" action="{{ route('grade-promotions.create') }}" class="filter-form">
            @if ($branches->count() > 1)
                <select name="branch_id" aria-label="Campus">
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) old('branch_id', $selectedBranchId) === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            @endif

            <select name="source_academic_year_id" required aria-label="Source academic year">
                <option value="">Choose source academic year *</option>
                @foreach ($academicYears as $year)
                    <option value="{{ $year->id }}" @selected($sourceAcademicYearId === $year->id)>{{ $year->name }}</option>
                @endforeach
            </select>

            <select name="source_grade_id" required aria-label="Source grade">
                <option value="">Choose source grade *</option>
                @foreach ($grades as $grade)
                    <option value="{{ $grade->id }}" @selected($sourceGradeId === $grade->id)>{{ $grade->name }} (Level {{ $grade->level }})</option>
                @endforeach
            </select>

            <select name="source_section_id" aria-label="Source section (optional)">
                <option value="">All sections</option>
                @foreach ($sections as $section)
                    <option value="{{ $section->id }}" @selected($sourceSectionId === $section->id)>{{ $section->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="primary-button">Load students</button>
        </form>
    </section>

    <!-- Step 2: Destination & Actions -->
    @if ($students->isNotEmpty())
        <section class="content-card">
            <form method="POST" action="{{ route('grade-promotions.store') }}">
                @csrf
                <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">
                <input type="hidden" name="from_academic_year_id" value="{{ $sourceAcademicYearId }}">
                <input type="hidden" name="from_grade_id" value="{{ $sourceGradeId }}">

                <div style="background: var(--panel-soft); border: 1px solid var(--edge); border-radius: 8px; padding: 1.25rem; margin-bottom: 1.5rem;">
                    <h3 style="font-size: 1.05rem; margin-bottom: 0.75rem;">Step 2: Destination Placement Target</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                        <div>
                            <label for="to_academic_year_id" style="font-weight: 500; font-size: 0.875rem; margin-bottom: 0.35rem; display: block;">Target Academic Year <span style="color: #ef4444;">*</span></label>
                            <select id="to_academic_year_id" name="to_academic_year_id" required style="width: 100%;">
                                <option value="">Select target year...</option>
                                @foreach ($academicYears as $year)
                                    @if ($year->id !== $sourceAcademicYearId)
                                        <option value="{{ $year->id }}" @selected((string) old('to_academic_year_id', $targetAcademicYearId) === (string) $year->id)>{{ $year->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="to_grade_id" style="font-weight: 500; font-size: 0.875rem; margin-bottom: 0.35rem; display: block;">Target Grade / Level <span style="color: #ef4444;">*</span></label>
                            <select id="to_grade_id" name="to_grade_id" required style="width: 100%;">
                                <option value="">Select target grade...</option>
                                @foreach ($grades as $grade)
                                    <option value="{{ $grade->id }}" @selected((string) old('to_grade_id', $targetGradeId) === (string) $grade->id)>{{ $grade->name }} (Level {{ $grade->level }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="to_section_id" style="font-weight: 500; font-size: 0.875rem; margin-bottom: 0.35rem; display: block;">Target Section (Optional)</label>
                            <select id="to_section_id" name="to_section_id" style="width: 100%;">
                                <option value="">Auto / unassigned section</option>
                                @foreach ($sections as $section)
                                    <option value="{{ $section->id }}" @selected((string) old('to_section_id', $targetSectionId) === (string) $section->id)>{{ $section->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <div>
                        <h3 style="margin: 0; font-size: 1.1rem;">Student Promotion Roster ({{ $students->count() }})</h3>
                        <small style="color: var(--muted);">Set individual promotion decision for each student.</small>
                    </div>

                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <button type="button" class="secondary-button" onclick="document.querySelectorAll('.action-select').forEach(s => s.value = 'promote')">Mark all Promote</button>
                        <button type="button" class="secondary-button" onclick="document.querySelectorAll('.action-select').forEach(s => s.value = 'repeat')">Mark all Repeat</button>
                    </div>
                </div>

                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Admission #</th>
                                <th>Student name</th>
                                <th>Current roll #</th>
                                <th>Current section</th>
                                <th>Decision action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($students as $index => $enrollment)
                                <tr>
                                    <td>
                                        <strong>{{ $enrollment->student->admission_number }}</strong>
                                        <input type="hidden" name="students[{{ $index }}][student_id]" value="{{ $enrollment->student_id }}">
                                        <input type="hidden" name="students[{{ $index }}][enrollment_id]" value="{{ $enrollment->id }}">
                                    </td>
                                    <td>
                                        <a href="{{ route('students.show', $enrollment->student) }}" class="table-link" target="_blank" style="font-weight: 600;">
                                            {{ $enrollment->student->full_name }}
                                        </a>
                                    </td>
                                    <td>{{ $enrollment->roll_number ?? '—' }}</td>
                                    <td>{{ $enrollment->section?->name ?? 'None' }}</td>
                                    <td>
                                        <select name="students[{{ $index }}][action]" class="action-select" style="padding: 0.35rem 0.6rem; font-weight: 500;">
                                            <option value="promote" selected>Promote to Next Grade</option>
                                            <option value="repeat">Repeat in Same Grade</option>
                                            <option value="graduate">Graduate Student</option>
                                            <option value="leave">Withdraw / Leave</option>
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 1.5rem; border-top: 1px solid var(--edge); padding-top: 1rem;">
                    <label for="notes" style="font-weight: 500; font-size: 0.875rem; margin-bottom: 0.35rem; display: block;">Promotion Notes / Audit remarks</label>
                    <textarea id="notes" name="notes" rows="2" placeholder="e.g. End of year academic promotions approved by Academic Board" style="width: 100%;"></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
                    <a href="{{ route('grade-promotions.index') }}" class="secondary-link">Cancel</a>
                    <button type="submit" class="primary-button" onclick="return confirm('Execute promotion batch? This will update student statuses and create new enrollments.')">Execute Batch Promotion</button>
                </div>
            </form>
        </section>
    @elseif ($sourceAcademicYearId && $sourceGradeId)
        <section class="content-card">
            <div style="text-align: center; padding: 2rem; color: var(--muted);">
                <h3>No active students found in this class</h3>
                <p>There are no active enrolled students in the selected academic year and grade.</p>
            </div>
        </section>
    @endif
@endsection
