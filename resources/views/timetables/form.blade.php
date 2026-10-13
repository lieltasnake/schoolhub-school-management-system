@extends('layouts.app')

@section('title', $timetable ? 'Edit Timetable Slot' : 'Create Timetable Slot')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Academics</p>
            <h1>{{ $timetable ? 'Edit timetable slot' : 'Create timetable slot' }}</h1>
            <p class="intro-copy">Schedule class lessons with automatic teacher and classroom clash detection.</p>
        </div>
        <a href="{{ route('timetables.index') }}" class="secondary-link">Cancel</a>
    </section>

    <section class="content-card">
        @if ($errors->any())
            <div class="alert alert-error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $timetable ? route('timetables.update', $timetable) : route('timetables.store') }}" class="form-grid">
            @csrf
            @if ($timetable)
                @method('PUT')
            @endif

            <div class="full-width">
                <label for="branch_id">Campus</label>
                <select id="branch_id" name="branch_id" required>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) old('branch_id', $timetable?->branch_id ?? $selectedBranchId) === (string) $branch->id)>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="day_of_week">Day of week</label>
                <select id="day_of_week" name="day_of_week" required>
                    @foreach ($days as $day)
                        <option value="{{ $day }}" @selected(old('day_of_week', $timetable?->day_of_week) === $day)>
                            {{ $day }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="period_id">Period</label>
                <select id="period_id" name="period_id" required>
                    <option value="">Select period</option>
                    @foreach ($periods as $period)
                        <option value="{{ $period->id }}" @selected((string) old('period_id', $timetable?->period_id) === (string) $period->id)>
                            {{ $period->name }} ({{ $period->start_time }} – {{ $period->end_time }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="section_id">Section / Class</label>
                <select id="section_id" name="section_id" required>
                    <option value="">Select section</option>
                    @foreach ($sections as $section)
                        <option value="{{ $section->id }}" @selected((string) old('section_id', $timetable?->section_id) === (string) $section->id)>
                            {{ $section->grade->name }} &bull; {{ $section->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="subject_id">Subject</label>
                <select id="subject_id" name="subject_id" required>
                    <option value="">Select subject</option>
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected((string) old('subject_id', $timetable?->subject_id) === (string) $subject->id)>
                            {{ $subject->name }} ({{ $subject->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="teacher_id">Teacher</label>
                <select id="teacher_id" name="teacher_id" required>
                    <option value="">Select teacher</option>
                    @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected((string) old('teacher_id', $timetable?->teacher_id) === (string) $teacher->id)>
                            {{ $teacher->user->name }} ({{ $teacher->employee_id }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="classroom_id">Classroom (optional)</label>
                <select id="classroom_id" name="classroom_id">
                    <option value="">No specific room / Class's home room</option>
                    @foreach ($classrooms as $room)
                        <option value="{{ $room->id }}" @selected((string) old('classroom_id', $timetable?->classroom_id) === (string) $room->id)>
                            {{ $room->room_number }} ({{ $room->name }} &bull; {{ $room->capacity }} seats)
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="academic_year_id">Academic year (optional)</label>
                <select id="academic_year_id" name="academic_year_id">
                    <option value="">All academic years</option>
                    @foreach ($academicYears as $year)
                        <option value="{{ $year->id }}" @selected((string) old('academic_year_id', $timetable?->academic_year_id) === (string) $year->id)>
                            {{ $year->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="term_id">Term / Semester (optional)</label>
                <select id="term_id" name="term_id">
                    <option value="">All terms</option>
                    @foreach ($terms as $term)
                        <option value="{{ $term->id }}" @selected((string) old('term_id', $timetable?->term_id) === (string) $term->id)>
                            {{ $term->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="full-width">
                <label for="notes">Notes (optional)</label>
                <textarea id="notes" name="notes" rows="2" placeholder="Lab instructions or notes...">{{ old('notes', $timetable?->notes) }}</textarea>
            </div>

            <div class="full-width form-actions">
                <button type="submit" class="primary-button">{{ $timetable ? 'Update timetable slot' : 'Save timetable slot' }}</button>
            </div>
        </form>
    </section>
@endsection
