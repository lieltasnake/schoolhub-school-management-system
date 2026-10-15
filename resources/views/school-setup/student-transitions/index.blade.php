@extends('layouts.app')

@section('title', 'Student Transitions — Withdraw, Graduate & Repeat')

@section('content')
<section class="page-intro">
    <div>
        <p class="eyebrow">School Setup / Student Lifecycle</p>
        <h1>Student Transitions</h1>
        <p class="intro-copy">Manage student withdrawals, graduations, and grade repeats across academic years.</p>
    </div>
    @if ($branches->count() > 1 && auth()->user()->hasAnyRole(['super-administrator', 'school-administrator']))
        <form method="GET" action="{{ route('student-transitions.index') }}" class="branch-switcher">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <label for="branch-select" class="sr-only">Campus / Branch</label>
            <select id="branch-select" name="branch_id" onchange="this.form.submit()">
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" @selected($selectedBranchId == $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
        </form>
    @endif
</section>

{{-- Flash Messages --}}
@if (session('success'))
    <div class="alert alert-success transition-alert">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        {{ session('success') }}
    </div>
@endif
@if (session('error'))
    <div class="alert alert-error transition-alert">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        {{ session('error') }}
    </div>
@endif
@if ($errors->any())
    <div class="alert alert-error transition-alert">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <ul style="margin: 0; padding-left: 1.25rem;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Navigation Tabs --}}
<div class="transition-tabs-wrapper">
    <nav class="transition-tabs" aria-label="Student Transition Modes">
        <a href="{{ route('student-transitions.index', ['tab' => 'withdraw', 'branch_id' => $selectedBranchId]) }}"
           class="transition-tab {{ $tab === 'withdraw' ? 'active' : '' }}"
           id="tab-withdraw">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="18" y1="8" x2="23" y2="13"/><line x1="23" y1="8" x2="18" y2="13"/></svg>
            Withdraw Student
        </a>
        <a href="{{ route('student-transitions.index', ['tab' => 'graduate', 'branch_id' => $selectedBranchId]) }}"
           class="transition-tab {{ $tab === 'graduate' ? 'active' : '' }}"
           id="tab-graduate">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 2 2 3 6 3s6-1 6-3v-5"/></svg>
            Graduate Students
        </a>
        <a href="{{ route('student-transitions.index', ['tab' => 'repeat', 'branch_id' => $selectedBranchId]) }}"
           class="transition-tab {{ $tab === 'repeat' ? 'active' : '' }}"
           id="tab-repeat">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><polyline points="1 4 1 10 7 10"/><polyline points="23 20 23 14 17 14"/><path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"/></svg>
            Repeating Feature
        </a>
        <a href="{{ route('student-transitions.index', ['tab' => 'history', 'branch_id' => $selectedBranchId]) }}"
           class="transition-tab {{ $tab === 'history' ? 'active' : '' }}"
           id="tab-history">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Transition History
        </a>
    </nav>
</div>

{{-- ═════════════════════════ TAB 1: WITHDRAW STUDENT ═════════════════════════ --}}
@if ($tab === 'withdraw')
<div class="transition-grid">
    {{-- Withdrawal Form Card --}}
    <section class="content-card">
        <div class="section-heading">
            <div>
                <p class="eyebrow" style="color: #ea580c;">Departure</p>
                <h2>Withdraw a Student</h2>
            </div>
        </div>
        <p class="section-desc">Mark a student as formally withdrawn due to transfer, relocation, or personal reasons.</p>

        <form method="POST" action="{{ route('student-transitions.withdraw') }}" class="styled-form"
              onsubmit="return confirm('Are you sure you want to withdraw this student? Their active enrollment will be closed.');">
            @csrf
            <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">

            <div class="form-group">
                <label for="withdraw_grade_filter">Filter by Class / Grade (optional)</label>
                <select id="withdraw_grade_filter" onchange="filterStudentDropdown(this.value, 'withdraw_student_id')">
                    <option value="">— Show all classes —</option>
                    @foreach ($grades as $grade)
                        <option value="{{ $grade->id }}">{{ $grade->name }} (Level {{ $grade->level }})</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="withdraw_student_id">Select Student <span class="required">*</span></label>
                <select id="withdraw_student_id" name="student_id" required>
                    <option value="">— Select student to withdraw —</option>
                    @foreach ($activeStudents as $student)
                        <option value="{{ $student->id }}" data-grade-id="{{ $student->latestEnrollment?->grade_id }}">
                            {{ $student->full_name }} ({{ $student->admission_number }}) — {{ $student->latestEnrollment?->grade->name ?? 'No class' }} {{ $student->latestEnrollment?->section ? '('.$student->latestEnrollment->section->name.')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label for="withdraw_date">Withdrawal Date <span class="required">*</span></label>
                    <input type="date" id="withdraw_date" name="transition_date" value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="form-group">
                    <label for="withdraw_reason">Reason for Withdrawal <span class="required">*</span></label>
                    <select id="withdraw_reason" name="reason" required>
                        <option value="">— Select reason —</option>
                        <option value="Transferred to another school">Transferred to another school</option>
                        <option value="Family relocation / moving">Family relocation / moving</option>
                        <option value="Personal or family reasons">Personal or family reasons</option>
                        <option value="Financial difficulty">Financial difficulty</option>
                        <option value="Extended medical leave">Extended medical leave</option>
                        <option value="Disciplinary / expulsion">Disciplinary / expulsion</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="withdraw_notes">Departure Notes / Forwarding School</label>
                <textarea id="withdraw_notes" name="notes" rows="2" placeholder="e.g. Requested transfer certificate; moving to Addis Ababa Secondary School..."></textarea>
            </div>

            <button type="submit" class="danger-button" style="display: inline-flex; align-items: center; gap: .5rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 1.1rem; height: 1.1rem;"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="18" y1="8" x2="23" y2="13"/><line x1="23" y1="8" x2="18" y2="13"/></svg>
                Confirm Student Withdrawal
            </button>
        </form>
    </section>

    {{-- Withdrawn Students List Card --}}
    <section class="content-card">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Records</p>
                <h2>Withdrawn Students ({{ $withdrawnStudents->total() }})</h2>
            </div>
        </div>
        <p class="section-desc">List of students currently marked as withdrawn. You may re-admit any student at any time.</p>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Admission #</th>
                        <th>Student Name</th>
                        <th>Last Class</th>
                        <th>Withdrawal Date & Reason</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($withdrawnStudents as $student)
                        @php
                            $latestWithdrawal = $student->transitions->first();
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $student->admission_number }}</strong>
                            </td>
                            <td>
                                <a href="{{ route('students.show', $student) }}" class="table-link font-bold">
                                    {{ $student->full_name }}
                                </a>
                                <span class="status-pill withdrawn" style="display: inline-block; margin-left: .25rem; font-size: .7rem; padding: 2px 6px;">Withdrawn</span>
                            </td>
                            <td>{{ $student->latestEnrollment?->grade->name ?? '—' }}</td>
                            <td>
                                <div>{{ $latestWithdrawal?->transition_date?->format('M d, Y') ?? '—' }}</div>
                                <small style="color: var(--muted, #6b7280);">{{ $latestWithdrawal?->reason ?? 'Withdrawn' }}</small>
                            </td>
                            <td>
                                <button type="button" class="secondary-button btn-sm"
                                        onclick="openReadmitModal({{ $student->id }}, '{{ addslashes($student->full_name) }}', '{{ $student->admission_number }}')">
                                    Re-admit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--muted, #6b7280); padding: 2rem;">
                                No withdrawn students found in this branch.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($withdrawnStudents->hasPages())
            <div style="margin-top: 1rem;">
                {{ $withdrawnStudents->links() }}
            </div>
        @endif
    </section>
</div>

{{-- Re-admit Modal --}}
<div id="readmit-modal" class="modal-backdrop" style="display: none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Re-admit Student</h3>
            <button type="button" class="modal-close" onclick="closeReadmitModal()">&times;</button>
        </div>
        <form method="POST" action="{{ route('student-transitions.readmit') }}" class="styled-form">
            @csrf
            <input type="hidden" id="readmit_student_id" name="student_id">

            <p id="readmit-student-label" style="font-weight: 600; margin-bottom: 1rem; color: #1e293b;"></p>

            <div class="form-group">
                <label for="readmit_year">Academic Year <span class="required">*</span></label>
                <select id="readmit_year" name="academic_year_id" required>
                    @foreach ($academicYears as $year)
                        <option value="{{ $year->id }}" @selected($year->status === 'active')>{{ $year->name }} {{ $year->status === 'active' ? '(Active)' : '' }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label for="readmit_grade">Placement Grade <span class="required">*</span></label>
                    <select id="readmit_grade" name="grade_id" required>
                        @foreach ($grades as $grade)
                            <option value="{{ $grade->id }}">{{ $grade->name }} (Level {{ $grade->level }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="readmit_section">Section (Optional)</label>
                    <select id="readmit_section" name="section_id">
                        <option value="">— Unassigned section —</option>
                        @foreach ($sections as $section)
                            <option value="{{ $section->id }}">{{ $section->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="readmit_date">Re-admission Date <span class="required">*</span></label>
                <input type="date" id="readmit_date" name="transition_date" value="{{ date('Y-m-d') }}" required>
            </div>

            <div class="form-group">
                <label for="readmit_notes">Re-admission Notes</label>
                <textarea id="readmit_notes" name="notes" rows="2" placeholder="e.g. Readmitted after returning from relocation..."></textarea>
            </div>

            <div class="modal-actions">
                <button type="button" class="secondary-button" onclick="closeReadmitModal()">Cancel</button>
                <button type="submit" class="primary-button">Complete Re-admission</button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- ═════════════════════════ TAB 2: GRADUATE STUDENTS ═════════════════════════ --}}
@if ($tab === 'graduate')
<div class="transition-grid">
    {{-- Graduation Form Card --}}
    <section class="content-card">
        <div class="section-heading">
            <div>
                <p class="eyebrow" style="color: #4f46e5;">Completion</p>
                <h2>Graduate Student(s)</h2>
            </div>
        </div>
        <p class="section-desc">Mark completing students as graduated, close active enrollments, and issue graduation records.</p>

        <form method="POST" action="{{ route('student-transitions.graduate') }}" class="styled-form"
              onsubmit="return confirm('Are you sure you want to graduate the selected student(s)?');">
            @csrf
            <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">

            <div class="form-row-2">
                <div class="form-group">
                    <label for="grad_year">Graduating Academic Year <span class="required">*</span></label>
                    <select id="grad_year" name="academic_year_id" required>
                        @foreach ($academicYears as $year)
                            <option value="{{ $year->id }}" @selected($year->status === 'active')>{{ $year->name }} {{ $year->status === 'active' ? '(Current)' : '' }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="grad_grade">Graduating Class / Grade <span class="required">*</span></label>
                    <select id="grad_grade" name="grade_id" required onchange="filterRosterByGrade(this.value, 'grad_roster')">
                        <option value="">— Select class —</option>
                        @foreach ($grades as $grade)
                            <option value="{{ $grade->id }}">{{ $grade->name }} (Level {{ $grade->level }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label for="grad_date">Graduation Date <span class="required">*</span></label>
                    <input type="date" id="grad_date" name="transition_date" value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="form-group">
                    <label for="grad_certificate">Batch Certificate / Diploma Reference</label>
                    <input type="text" id="grad_certificate" name="certificate_number" placeholder="e.g. CLASS-OF-{{ date('Y') }}">
                </div>
            </div>

            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: .4rem;">
                    <label style="margin: 0;">Select Students to Graduate <span class="required">*</span></label>
                    <div style="display: flex; gap: .5rem;">
                        <button type="button" class="btn-link-action" onclick="toggleAllCheckboxes('grad_roster', true)">Select all</button>
                        <span style="color: #cbd5e1;">|</span>
                        <button type="button" class="btn-link-action" onclick="toggleAllCheckboxes('grad_roster', false)">Deselect all</button>
                    </div>
                </div>

                <div class="student-select-roster" id="grad_roster">
                    @forelse ($activeStudents as $student)
                        <label class="student-checkbox-row" data-grade-id="{{ $student->latestEnrollment?->grade_id }}">
                            <input type="checkbox" name="student_ids[]" value="{{ $student->id }}">
                            <div class="student-info">
                                <strong>{{ $student->full_name }}</strong>
                                <small>Admission #{{ $student->admission_number }} &bull; Class: {{ $student->latestEnrollment?->grade->name ?? 'Unassigned' }}</small>
                            </div>
                        </label>
                    @empty
                        <p style="padding: 1rem; color: var(--muted, #6b7280); text-align: center;">No active students available.</p>
                    @endforelse
                </div>
            </div>

            <div class="form-group">
                <label for="grad_notes">Honors / Commencement Notes</label>
                <textarea id="grad_notes" name="notes" rows="2" placeholder="e.g. Completed secondary curriculum with honors..."></textarea>
            </div>

            <button type="submit" class="primary-button" style="display: inline-flex; align-items: center; gap: .5rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 1.15rem; height: 1.15rem;"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 2 2 3 6 3s6-1 6-3v-5"/></svg>
                Graduate Selected Students
            </button>
        </form>
    </section>

    {{-- Graduated Students Archive --}}
    <section class="content-card">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Alumni Archive</p>
                <h2>Graduated Students ({{ $graduatedStudents->total() }})</h2>
            </div>
        </div>
        <p class="section-desc">Students who have graduated from the institution.</p>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Admission #</th>
                        <th>Student Name</th>
                        <th>Graduation Date</th>
                        <th>Diploma Ref</th>
                        <th>Class Completed</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($graduatedStudents as $student)
                        @php $latestGrad = $student->transitions->first(); @endphp
                        <tr>
                            <td><strong>{{ $student->admission_number }}</strong></td>
                            <td>
                                <a href="{{ route('students.show', $student) }}" class="table-link font-bold">
                                    {{ $student->full_name }}
                                </a>
                                <span class="status-pill graduated" style="display: inline-block; margin-left: .25rem; font-size: .7rem; padding: 2px 6px;">Graduated</span>
                            </td>
                            <td>{{ $latestGrad?->transition_date?->format('M d, Y') ?? $student->updated_at->format('M d, Y') }}</td>
                            <td>{{ $latestGrad?->certificate_number ?? '—' }}</td>
                            <td>{{ $student->latestEnrollment?->grade->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--muted, #6b7280); padding: 2rem;">
                                No graduated student records found yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($graduatedStudents->hasPages())
            <div style="margin-top: 1rem;">
                {{ $graduatedStudents->links() }}
            </div>
        @endif
    </section>
</div>
@endif

{{-- ═════════════════════════ TAB 3: REPEATING FEATURE ═════════════════════════ --}}
@if ($tab === 'repeat')
<div class="transition-grid">
    {{-- Repeating Form Card --}}
    <section class="content-card">
        <div class="section-heading">
            <div>
                <p class="eyebrow" style="color: #b45309;">Academic Retention</p>
                <h2>Assign Student(s) to Repeat Grade</h2>
            </div>
        </div>
        <p class="section-desc">Designate students to repeat their current grade in the upcoming academic year. Creates a new repeating enrollment.</p>

        <form method="POST" action="{{ route('student-transitions.repeat') }}" class="styled-form"
              onsubmit="return confirm('Are you sure you want to register the selected student(s) to repeat this grade for the target academic year?');">
            @csrf
            <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">

            <div class="form-row-2">
                <div class="form-group">
                    <label for="repeat_from_year">Current Academic Year <span class="required">*</span></label>
                    <select id="repeat_from_year" name="from_academic_year_id" required>
                        @foreach ($academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->name }} {{ $year->status === 'active' ? '(Current)' : '' }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="repeat_to_year">Target / Upcoming Academic Year <span class="required">*</span></label>
                    <select id="repeat_to_year" name="to_academic_year_id" required>
                        <option value="">— Select target year —</option>
                        @foreach ($academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label for="repeat_grade">Repeating Grade / Level <span class="required">*</span></label>
                    <select id="repeat_grade" name="grade_id" required onchange="filterRosterByGrade(this.value, 'repeat_roster')">
                        <option value="">— Select repeating grade —</option>
                        @foreach ($grades as $grade)
                            <option value="{{ $grade->id }}">{{ $grade->name }} (Level {{ $grade->level }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="repeat_section">Target Section (Optional)</label>
                    <select id="repeat_section" name="to_section_id">
                        <option value="">— Unassigned section —</option>
                        @foreach ($sections as $section)
                            <option value="{{ $section->id }}">{{ $section->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label for="repeat_date">Effective Date <span class="required">*</span></label>
                    <input type="date" id="repeat_date" name="transition_date" value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="form-group">
                    <label for="repeat_reason">Reason for Repeating <span class="required">*</span></label>
                    <select id="repeat_reason" name="reason" required>
                        <option value="">— Select reason —</option>
                        <option value="Academic retention / low GPA">Academic retention / low GPA</option>
                        <option value="Attendance threshold not met">Attendance threshold not met</option>
                        <option value="Parental / guardian request">Parental / guardian request</option>
                        <option value="Prolonged medical absence">Prolonged medical absence</option>
                        <option value="Curriculum gap / language transfer">Curriculum gap / language transfer</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: .4rem;">
                    <label style="margin: 0;">Select Student(s) to Repeat <span class="required">*</span></label>
                    <div style="display: flex; gap: .5rem;">
                        <button type="button" class="btn-link-action" onclick="toggleAllCheckboxes('repeat_roster', true)">Select all</button>
                        <span style="color: #cbd5e1;">|</span>
                        <button type="button" class="btn-link-action" onclick="toggleAllCheckboxes('repeat_roster', false)">Deselect all</button>
                    </div>
                </div>

                <div class="student-select-roster" id="repeat_roster">
                    @forelse ($activeStudents as $student)
                        <label class="student-checkbox-row" data-grade-id="{{ $student->latestEnrollment?->grade_id }}">
                            <input type="checkbox" name="student_ids[]" value="{{ $student->id }}">
                            <div class="student-info">
                                <strong>{{ $student->full_name }}</strong>
                                <small>Admission #{{ $student->admission_number }} &bull; Class: {{ $student->latestEnrollment?->grade->name ?? 'Unassigned' }}</small>
                            </div>
                        </label>
                    @empty
                        <p style="padding: 1rem; color: var(--muted, #6b7280); text-align: center;">No active students available.</p>
                    @endforelse
                </div>
            </div>

            <div class="form-group">
                <label for="repeat_notes">Retention Committee Notes / Remediation Plan</label>
                <textarea id="repeat_notes" name="notes" rows="2" placeholder="e.g. Recommended additional tutoring in Mathematics; repeating grade 8 next year..."></textarea>
            </div>

            <button type="submit" class="warning-button" style="display: inline-flex; align-items: center; gap: .5rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 1.15rem; height: 1.15rem;"><polyline points="1 4 1 10 7 10"/><polyline points="23 20 23 14 17 14"/><path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"/></svg>
                Confirm Repeating Registration
            </button>
        </form>
    </section>

    {{-- Repeat Log Card --}}
    <section class="content-card">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Retention Records</p>
                <h2>Repeating Log ({{ $repeatingTransitions->total() }})</h2>
            </div>
        </div>
        <p class="section-desc">Students registered to repeat their grade level.</p>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Admission #</th>
                        <th>Student Name</th>
                        <th>Repeating Grade</th>
                        <th>Academic Years</th>
                        <th>Reason</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($repeatingTransitions as $transition)
                        <tr>
                            <td><strong>{{ $transition->student?->admission_number ?? '—' }}</strong></td>
                            <td>
                                @if ($transition->student)
                                    <a href="{{ route('students.show', $transition->student) }}" class="table-link font-bold">
                                        {{ $transition->student->full_name }}
                                    </a>
                                @else
                                    <em>Deleted Student</em>
                                @endif
                                <span class="status-pill repeated" style="display: inline-block; margin-left: .25rem; font-size: .7rem; padding: 2px 6px;">Repeating</span>
                            </td>
                            <td>{{ $transition->fromGrade?->name ?? '—' }}</td>
                            <td>
                                <div><small>From:</small> {{ $transition->fromAcademicYear?->name ?? '—' }}</div>
                                <div><small>To:</small> <strong>{{ $transition->toAcademicYear?->name ?? '—' }}</strong></div>
                            </td>
                            <td>
                                <div>{{ $transition->reason }}</div>
                                <small style="color: var(--muted, #6b7280);">{{ $transition->transition_date->format('M d, Y') }}</small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--muted, #6b7280); padding: 2rem;">
                                No student repeating records recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($repeatingTransitions->hasPages())
            <div style="margin-top: 1rem;">
                {{ $repeatingTransitions->links() }}
            </div>
        @endif
    </section>
</div>
@endif

{{-- ═════════════════════════ TAB 4: TRANSITION HISTORY ═════════════════════════ --}}
@if ($tab === 'history')
<section class="content-card">
    <div class="section-heading">
        <div>
            <p class="eyebrow">Audit Trail</p>
            <h2>Transition History Log</h2>
        </div>
    </div>
    <p class="section-desc">Complete chronological record of all student lifecycle events: withdrawals, graduations, repeats, and re-admissions.</p>

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('student-transitions.index') }}" class="filter-form" style="margin-bottom: 1.5rem;">
        <input type="hidden" name="tab" value="history">
        <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">

        <select name="type" aria-label="Event type">
            <option value="">— All Transition Types —</option>
            <option value="withdrawal" @selected(request('type') === 'withdrawal')>Withdrawals</option>
            <option value="graduation" @selected(request('type') === 'graduation')>Graduations</option>
            <option value="repeat" @selected(request('type') === 'repeat')>Repeats</option>
            <option value="readmission" @selected(request('type') === 'readmission')>Re-admissions</option>
        </select>

        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by student name or admission #..." style="min-width: 260px;">

        <button type="submit" class="primary-button">Filter Log</button>
        @if (request()->hasAny(['type', 'search']))
            <a href="{{ route('student-transitions.index', ['tab' => 'history', 'branch_id' => $selectedBranchId]) }}" class="secondary-link">Reset</a>
        @endif
    </form>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Student</th>
                    <th>Transition Event</th>
                    <th>Grade / Academic Year</th>
                    <th>Reason &amp; Notes</th>
                    <th>Processed By</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($historyTransitions as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->transition_date->format('M d, Y') }}</strong>
                            <small style="display: block; color: var(--muted, #6b7280);">{{ $item->created_at->format('H:i') }}</small>
                        </td>
                        <td>
                            @if ($item->student)
                                <a href="{{ route('students.show', $item->student) }}" class="table-link font-bold">
                                    {{ $item->student->full_name }}
                                </a>
                                <small style="display: block; color: var(--muted, #6b7280);">#{{ $item->student->admission_number }}</small>
                            @else
                                <em>Deleted Student</em>
                            @endif
                        </td>
                        <td>
                            @if ($item->transition_type === 'withdrawal')
                                <span class="status-pill status-withdrawn">Withdrawal</span>
                            @elseif ($item->transition_type === 'graduation')
                                <span class="status-pill status-graduated">Graduation</span>
                            @elseif ($item->transition_type === 'repeat')
                                <span class="status-pill status-repeated">Repeat Grade</span>
                            @elseif ($item->transition_type === 'readmission')
                                <span class="status-pill status-active">Re-admission</span>
                            @else
                                <span class="status-pill">{{ ucfirst($item->transition_type) }}</span>
                            @endif
                        </td>
                        <td>
                            @if ($item->transition_type === 'repeat')
                                <div><strong>{{ $item->fromGrade?->name ?? '—' }}</strong></div>
                                <small style="color: var(--muted, #6b7280);">{{ $item->fromAcademicYear?->name }} &rarr; {{ $item->toAcademicYear?->name }}</small>
                            @elseif ($item->transition_type === 'graduation')
                                <div>Graduated from: <strong>{{ $item->fromGrade?->name ?? 'Final Grade' }}</strong></div>
                                <small style="color: var(--muted, #6b7280);">Year: {{ $item->fromAcademicYear?->name ?? '—' }}</small>
                            @else
                                <div>{{ $item->fromGrade?->name ?? $item->toGrade?->name ?? '—' }}</div>
                            @endif
                        </td>
                        <td>
                            <div>{{ $item->reason ?? '—' }}</div>
                            @if ($item->certificate_number)
                                <small style="color: #4f46e5; font-weight: 500;">Diploma/Cert: {{ $item->certificate_number }}</small>
                            @endif
                            @if ($item->notes)
                                <small style="display: block; color: var(--muted, #6b7280);">Note: {{ $item->notes }}</small>
                            @endif
                        </td>
                        <td>
                            <small>{{ $item->processor?->name ?? 'System' }}</small>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--muted, #6b7280); padding: 2.5rem;">
                            No transition records logged yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($historyTransitions->hasPages())
        <div style="margin-top: 1rem;">
            {{ $historyTransitions->links() }}
        </div>
    @endif
</section>
@endif

@push('styles')
<style>
/* ── Transitions Tab Layout ───────────────────────────────────────── */
.transition-tabs-wrapper { margin: 0 0 1.5rem; }
.transition-tabs {
    display: flex;
    gap: .25rem;
    border-bottom: 2px solid var(--border-color, #e2e8f0);
    flex-wrap: wrap;
}
.transition-tab {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .7rem 1.15rem;
    font-size: .9rem;
    font-weight: 500;
    color: var(--text-muted, #64748b);
    text-decoration: none;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    border-radius: 6px 6px 0 0;
    transition: all .15s ease-in-out;
}
.transition-tab svg { width: 1.15rem; height: 1.15rem; }
.transition-tab:hover {
    color: #1e293b;
    background: #f8fafc;
}
.transition-tab.active {
    color: #2563eb;
    border-bottom-color: #2563eb;
    background: #eff6ff;
    font-weight: 600;
}

/* ── Grid & Card Layouts ─────────────────────────────────────────── */
.transition-grid {
    display: grid;
    grid-template-columns: minmax(340px, 460px) 1fr;
    gap: 1.5rem;
    align-items: start;
}
@media (max-width: 1024px) {
    .transition-grid { grid-template-columns: 1fr; }
}

.section-desc {
    color: var(--muted, #64748b);
    font-size: .88rem;
    margin-top: -.25rem;
    margin-bottom: 1.25rem;
    line-height: 1.45;
}

.styled-form { display: flex; flex-direction: column; gap: 1rem; }
.form-row-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}
@media (max-width: 640px) {
    .form-row-2 { grid-template-columns: 1fr; }
}

.form-group { display: flex; flex-direction: column; gap: .35rem; }
.form-group label {
    font-size: .84rem;
    font-weight: 500;
    color: #334155;
}
.form-group input,
.form-group select,
.form-group textarea {
    padding: .55rem .75rem;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: .9rem;
    background: #fff;
    color: #0f172a;
    transition: border-color .15s, box-shadow .15s;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}
.required { color: #ef4444; }

/* ── Student Checkbox Roster ─────────────────────────────────────── */
.student-select-roster {
    max-height: 240px;
    overflow-y: auto;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    background: #f8fafc;
    padding: .25rem;
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.student-checkbox-row {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .5rem .75rem;
    background: #fff;
    border: 1px solid #f1f5f9;
    border-radius: 4px;
    cursor: pointer;
    transition: background .12s, border-color .12s;
}
.student-checkbox-row:hover {
    background: #f0fdf4;
    border-color: #bbf7d0;
}
.student-checkbox-row input[type="checkbox"] {
    width: 1.05rem;
    height: 1.05rem;
    accent-color: #2563eb;
    cursor: pointer;
}
.student-checkbox-row .student-info {
    display: flex;
    flex-direction: column;
}
.student-checkbox-row .student-info strong {
    font-size: .86rem;
    color: #1e293b;
}
.student-checkbox-row .student-info small {
    font-size: .75rem;
    color: #64748b;
}

.btn-link-action {
    background: none;
    border: none;
    color: #2563eb;
    font-size: .8rem;
    font-weight: 500;
    cursor: pointer;
    padding: 0;
    text-decoration: underline;
}
.btn-link-action:hover { color: #1d4ed8; }

/* ── Custom Buttons ─────────────────────────────────────────────── */
.danger-button {
    background: #dc2626;
    color: #fff;
    font-weight: 600;
    padding: .65rem 1.15rem;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    transition: background .15s;
}
.danger-button:hover { background: #b91c1c; }

.warning-button {
    background: #d97706;
    color: #fff;
    font-weight: 600;
    padding: .65rem 1.15rem;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    transition: background .15s;
}
.warning-button:hover { background: #b45309; }

.btn-sm {
    padding: .25rem .6rem;
    font-size: .8rem;
}

/* ── Status Badges ──────────────────────────────────────────────── */
.status-pill.withdrawn,
.status-pill.status-withdrawn {
    background: #ffedd5;
    color: #c2410c;
}
.status-pill.graduated,
.status-pill.status-graduated {
    background: #ede9fe;
    color: #6d28d9;
}
.status-pill.repeated,
.status-pill.status-repeated {
    background: #fef3c7;
    color: #92400e;
}

/* ── Modal ──────────────────────────────────────────────────────── */
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.55);
    backdrop-filter: blur(2px);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}
.modal-box {
    background: #fff;
    border-radius: 10px;
    max-width: 520px;
    width: 100%;
    padding: 1.5rem;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.25rem;
}
.modal-header h3 { margin: 0; font-size: 1.2rem; }
.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #64748b;
}
.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: .75rem;
    margin-top: 1rem;
}
</style>
@endpush

@push('scripts')
<script>
// Filter student dropdown by class / grade
function filterStudentDropdown(gradeId, selectId) {
    const select = document.getElementById(selectId);
    if (!select) return;
    const options = select.querySelectorAll('option[data-grade-id]');
    let visibleCount = 0;
    options.forEach(opt => {
        if (!gradeId || opt.dataset.gradeId === gradeId) {
            opt.style.display = '';
            visibleCount++;
        } else {
            opt.style.display = 'none';
        }
    });
    // Reset selection to default placeholder
    select.value = '';
}

// Filter student roster checklist by class / grade
function filterRosterByGrade(gradeId, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    const rows = container.querySelectorAll('.student-checkbox-row');
    rows.forEach(row => {
        const input = row.querySelector('input[type="checkbox"]');
        if (!gradeId || row.dataset.gradeId === gradeId) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
            if (input) input.checked = false;
        }
    });
}

// Toggle all visible checkboxes in a roster
function toggleAllCheckboxes(containerId, checkState) {
    const container = document.getElementById(containerId);
    if (!container) return;
    const rows = container.querySelectorAll('.student-checkbox-row');
    rows.forEach(row => {
        if (row.style.display !== 'none') {
            const input = row.querySelector('input[type="checkbox"]');
            if (input) input.checked = checkState;
        }
    });
}

// Re-admit Modal controls
function openReadmitModal(studentId, studentName, admissionNumber) {
    const modal = document.getElementById('readmit-modal');
    const studentInput = document.getElementById('readmit_student_id');
    const label = document.getElementById('readmit-student-label');

    if (modal && studentInput && label) {
        studentInput.value = studentId;
        label.textContent = studentName + ' (' + admissionNumber + ')';
        modal.style.display = 'flex';
    }
}

function closeReadmitModal() {
    const modal = document.getElementById('readmit-modal');
    if (modal) modal.style.display = 'none';
}
</script>
@endpush
@endsection
