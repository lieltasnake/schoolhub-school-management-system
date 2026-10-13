@extends('layouts.app')

@section('title', 'Take Student Attendance')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Attendance</p>
            <h1>Take student attendance</h1>
            <p class="intro-copy">Select class section and date to record or update attendance for all enrolled students.</p>
        </div>
        <a href="{{ route('student-attendance.index') }}" class="secondary-link">Back to list</a>
    </section>

    <section class="content-card">
        <form method="GET" action="{{ route('student-attendance.create') }}" class="filter-form" style="margin-bottom:1.5rem;">
            <select name="section_id" required aria-label="Select section" onchange="this.form.submit()">
                <option value="">Select section to mark attendance</option>
                @foreach ($sections as $section)
                    <option value="{{ $section->id }}" @selected((string) $selectedSectionId === (string) $section->id)>
                        {{ $section->grade->name }} &bull; {{ $section->name }}
                    </option>
                @endforeach
            </select>
            <input type="date" name="attendance_date" value="{{ $attendanceDate }}" required onchange="this.form.submit()">
            <button type="submit" class="secondary-button">Load Class</button>
        </form>

        @if ($errors->any())
            <div class="alert alert-error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($selectedSectionId && $students->isNotEmpty())
            <form method="POST" action="{{ route('student-attendance.store') }}">
                @csrf
                <input type="hidden" name="section_id" value="{{ $selectedSectionId }}">
                <input type="hidden" name="attendance_date" value="{{ $attendanceDate }}">

                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Admission #</th>
                                <th>Student Name</th>
                                <th>Attendance Status</th>
                                <th>Remarks (Optional)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($students as $index => $student)
                                @php
                                    $existing = $existingRecords->get($student->id);
                                    $currentStatus = $existing ? $existing->status : 'present';
                                    $currentRemarks = $existing ? $existing->remarks : '';
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td><code>{{ $student->admission_number }}</code></td>
                                    <td>
                                        <strong>{{ $student->full_name }}</strong>
                                        <input type="hidden" name="attendances[{{ $index }}][student_id]" value="{{ $student->id }}">
                                    </td>
                                    <td>
                                        <div style="display:flex; gap:1rem; align-items:center;">
                                            @foreach (['present', 'absent', 'late', 'excused'] as $opt)
                                                <label style="display:flex; align-items:center; gap:0.25rem; cursor:pointer;">
                                                    <input type="radio" name="attendances[{{ $index }}][status]" value="{{ $opt }}" @checked(old("attendances.{$index}.status", $currentStatus) === $opt)>
                                                    <span>{{ ucfirst($opt) }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" name="attendances[{{ $index }}][remarks]" value="{{ old("attendances.{$index}.remarks", $currentRemarks) }}" placeholder="e.g. Doctor's note, Sick" style="width:100%; max-width:260px;">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="margin-top:1.5rem; display:flex; justify-content:flex-end;">
                    <button type="submit" class="primary-button">Save attendance record</button>
                </div>
            </form>
        @elseif ($selectedSectionId)
            <p style="text-align:center; padding:3rem 1rem; color:var(--text-muted, #64748b);">
                No active students are currently enrolled in this section. Please enroll students first.
            </p>
        @else
            <p style="text-align:center; padding:3rem 1rem; color:var(--text-muted, #64748b);">
                Select a class section above to load students.
            </p>
        @endif
    </section>
@endsection
