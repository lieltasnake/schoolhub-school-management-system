@extends('layouts.app')

@section('title', $enrollment ? 'Edit Enrollment' : 'Enroll Student')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Students / Enrollment</p>
            <h1>{{ $enrollment ? "Edit enrollment: {$enrollment->student->full_name}" : 'Enroll student' }}</h1>
            <p class="intro-copy">Assign academic year session, grade placement, section, and roll number.</p>
        </div>
        <a href="{{ route('enrollments.index') }}" class="secondary-link">Cancel</a>
    </section>

    <section class="content-card">
        @if ($errors->any())
            <div class="alert alert-error">
                <strong>Please resolve the following issues:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $enrollment ? route('enrollments.update', $enrollment) : route('enrollments.store') }}" class="form-grid">
            @csrf
            @if ($enrollment)
                @method('PUT')
            @endif

            @if ($enrollment)
                <div>
                    <label>Student</label>
                    <input type="text" value="{{ $enrollment->student->full_name }} ({{ $enrollment->student->admission_number }})" disabled>
                </div>
                <div>
                    <label>Academic Year</label>
                    <input type="text" value="{{ $enrollment->academicYear->name }}" disabled>
                </div>
            @else
                <div>
                    <label for="student_id">Student <span style="color: #ef4444;">*</span></label>
                    <select id="student_id" name="student_id" required>
                        <option value="">Select student</option>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}" @selected((string) old('student_id') === (string) $student->id)>{{ $student->full_name }} ({{ $student->admission_number }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="academic_year_id">Academic Year <span style="color: #ef4444;">*</span></label>
                    <select id="academic_year_id" name="academic_year_id" required>
                        <option value="">Select academic year</option>
                        @foreach ($academicYears as $year)
                            <option value="{{ $year->id }}" @selected((string) old('academic_year_id') === (string) $year->id)>{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label for="grade_id">Grade / Class <span style="color: #ef4444;">*</span></label>
                <select id="grade_id" name="grade_id" required>
                    <option value="">Select grade</option>
                    @foreach ($grades as $grade)
                        <option value="{{ $grade->id }}" @selected((string) old('grade_id', $enrollment?->grade_id) === (string) $grade->id)>{{ $grade->name }} ({{ $grade->code }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="section_id">Section / Classroom</label>
                <select id="section_id" name="section_id">
                    <option value="">Unassigned section</option>
                    @foreach ($sections as $section)
                        <option value="{{ $section->id }}" @selected((string) old('section_id', $enrollment?->section_id) === (string) $section->id)>{{ $section->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="roll_number">Roll / Class Number (optional)</label>
                <input id="roll_number" name="roll_number" value="{{ old('roll_number', $enrollment?->roll_number) }}" placeholder="e.g. 12">
            </div>

            <div>
                <label for="enrolled_at">Enrollment Date <span style="color: #ef4444;">*</span></label>
                <input type="date" id="enrolled_at" name="enrolled_at" value="{{ old('enrolled_at', $enrollment?->enrolled_at?->format('Y-m-d') ?? now()->toDateString()) }}" required>
            </div>

            <div>
                <label for="status">Enrollment Status <span style="color: #ef4444;">*</span></label>
                <select id="status" name="status" required>
                    <option value="active" @selected(old('status', $enrollment?->status ?? 'active') === 'active')>Active</option>
                    <option value="promoted" @selected(old('status', $enrollment?->status) === 'promoted')>Promoted</option>
                    <option value="repeated" @selected(old('status', $enrollment?->status) === 'repeated')>Repeated</option>
                    <option value="transferred" @selected(old('status', $enrollment?->status) === 'transferred')>Transferred</option>
                    <option value="completed" @selected(old('status', $enrollment?->status) === 'completed')>Completed</option>
                </select>
            </div>

            <div class="full-width" style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem;">
                <a href="{{ route('enrollments.index') }}" class="secondary-link">Cancel</a>
                <button type="submit" class="primary-button">{{ $enrollment ? 'Update enrollment' : 'Enroll student' }}</button>
            </div>
        </form>
    </section>
@endsection
