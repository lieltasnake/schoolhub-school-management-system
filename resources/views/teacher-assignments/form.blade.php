@extends('layouts.app')

@section('title', 'Create Teacher Assignment')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Academic Staff</p>
            <h1>Create teacher assignment</h1>
            <p class="intro-copy">Assign a teacher to a grade, section, subject, or role like class teacher.</p>
        </div>
        <a href="{{ route('teacher-assignments.index') }}" class="secondary-link">Cancel</a>
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

        <form method="POST" action="{{ route('teacher-assignments.store') }}" class="form-grid">
            @csrf

            <div class="full-width">
                <label for="teacher_id">Teacher</label>
                <select id="teacher_id" name="teacher_id" required>
                    <option value="">Select teacher</option>
                    @foreach ($teachers as $t)
                        <option value="{{ $t->id }}" @selected((string) old('teacher_id') === (string) $t->id)>
                            {{ $t->user->name }} ({{ $t->employee_id }} &bull; {{ $t->branch->name }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="grade_id">Grade level</label>
                <select id="grade_id" name="grade_id" required>
                    <option value="">Select grade</option>
                    @foreach ($grades as $g)
                        <option value="{{ $g->id }}" @selected((string) old('grade_id') === (string) $g->id)>
                            {{ $g->branch->name }} &bull; {{ $g->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="section_id">Section (optional)</label>
                <select id="section_id" name="section_id">
                    <option value="">All sections in grade</option>
                    @foreach ($sections as $s)
                        <option value="{{ $s->id }}" @selected((string) old('section_id') === (string) $s->id)>
                            {{ $s->grade->name }} &bull; {{ $s->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="subject_id">Subject (optional)</label>
                <select id="subject_id" name="subject_id">
                    <option value="">All subjects</option>
                    @foreach ($subjects as $sub)
                        <option value="{{ $sub->id }}" @selected((string) old('subject_id') === (string) $sub->id)>
                            {{ $sub->name }} ({{ $sub->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="academic_year_id">Academic year (optional)</label>
                <select id="academic_year_id" name="academic_year_id">
                    <option value="">All academic years</option>
                    @foreach ($academicYears as $y)
                        <option value="{{ $y->id }}" @selected((string) old('academic_year_id') === (string) $y->id)>
                            {{ $y->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    @foreach (['subject_teacher', 'class_teacher', 'assistant_teacher', 'head_of_department'] as $role)
                        <option value="{{ $role }}" @selected(old('role', 'subject_teacher') === $role)>
                            {{ ucfirst(str_replace('_', ' ', $role)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="full-width">
                <label for="notes">Notes (optional)</label>
                <textarea id="notes" name="notes" rows="3" placeholder="Additional details about this assignment...">{{ old('notes') }}</textarea>
            </div>

            <div class="full-width form-actions">
                <button type="submit" class="primary-button">Save assignment</button>
            </div>
        </form>
    </section>
@endsection
