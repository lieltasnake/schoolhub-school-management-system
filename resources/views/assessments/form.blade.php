@extends('layouts.app')

@section('title', $assessment ? 'Edit Assessment' : 'New Assessment Setup')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Exams & Assessments / Setup</p>
            <h1>{{ $assessment ? "Edit assessment: {$assessment->title}" : 'New assessment setup' }}</h1>
            <p class="intro-copy">Configure examination parameters, target class, subject, scale, and grading thresholds.</p>
        </div>
        <a href="{{ route('assessments.index') }}" class="secondary-link">Cancel</a>
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

        <form method="POST" action="{{ $assessment ? route('assessments.update', $assessment) : route('assessments.store') }}" class="form-grid">
            @csrf
            @if ($assessment)
                @method('PUT')
            @endif

            <div class="full-width">
                <h3 style="margin-bottom: 0.25rem;">1. Academic Context</h3>
            </div>

            @if ($assessment)
                <div>
                    <label>Campus</label>
                    <input type="text" value="{{ $assessment->branch->name }}" disabled>
                </div>
            @else
                <div>
                    <label for="branch_id">Campus <span style="color: #ef4444;">*</span></label>
                    <select id="branch_id" name="branch_id" required>
                        <option value="">Select campus</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected((string) old('branch_id', $selectedBranchId) === (string) $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label for="academic_year_id">Academic Year <span style="color: #ef4444;">*</span></label>
                <select id="academic_year_id" name="academic_year_id" required>
                    <option value="">Select academic year</option>
                    @foreach ($academicYears as $year)
                        <option value="{{ $year->id }}" @selected((string) old('academic_year_id', $assessment?->academic_year_id) === (string) $year->id)>{{ $year->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="term_id">Term / Semester</label>
                <select id="term_id" name="term_id">
                    <option value="">Full Academic Year / Untimed</option>
                    @foreach ($terms as $term)
                        <option value="{{ $term->id }}" @selected((string) old('term_id', $assessment?->term_id) === (string) $term->id)>{{ $term->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="grade_id">Grade / Class <span style="color: #ef4444;">*</span></label>
                <select id="grade_id" name="grade_id" required>
                    <option value="">Select grade</option>
                    @foreach ($grades as $grade)
                        <option value="{{ $grade->id }}" @selected((string) old('grade_id', $assessment?->grade_id) === (string) $grade->id)>{{ $grade->name }} ({{ $grade->code }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="section_id">Classroom Section (Optional)</label>
                <select id="section_id" name="section_id">
                    <option value="">All sections in this grade</option>
                    @foreach ($sections as $section)
                        <option value="{{ $section->id }}" @selected((string) old('section_id', $assessment?->section_id) === (string) $section->id)>{{ $section->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="full-width" style="margin-top: 1rem; border-top: 1px solid var(--edge); padding-top: 1rem;">
                <h3 style="margin-bottom: 0.25rem;">2. Assessment Specification</h3>
            </div>

            <div>
                <label for="title">Assessment Title <span style="color: #ef4444;">*</span></label>
                <input id="title" name="title" value="{{ old('title', $assessment?->title) }}" placeholder="e.g. Midterm 1 Mathematics Exam" required>
            </div>

            <div>
                <label for="subject_name">Subject / Course <span style="color: #ef4444;">*</span></label>
                <input id="subject_name" name="subject_name" value="{{ old('subject_name', $assessment?->subject_name) }}" placeholder="e.g. Mathematics, Biology, English" required>
            </div>

            <div>
                <label for="assessment_type_id">Assessment Type <span style="color: #ef4444;">*</span></label>
                <select id="assessment_type_id" name="assessment_type_id" required>
                    <option value="">Select assessment type</option>
                    @foreach ($assessmentTypes as $type)
                        <option value="{{ $type->id }}" @selected((string) old('assessment_type_id', $assessment?->assessment_type_id) === (string) $type->id)>{{ $type->name }} ({{ $type->weight_percentage }}%)</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="grade_scale_id">Grading Scale (Optional)</label>
                <select id="grade_scale_id" name="grade_scale_id">
                    <option value="">Default percentage conversion</option>
                    @foreach ($gradeScales as $scale)
                        <option value="{{ $scale->id }}" @selected((string) old('grade_scale_id', $assessment?->grade_scale_id) === (string) $scale->id)>{{ $scale->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="max_score">Maximum Marks / Full Score <span style="color: #ef4444;">*</span></label>
                <input type="number" step="0.01" min="1" max="1000" id="max_score" name="max_score" value="{{ old('max_score', $assessment?->max_score ?? '100.00') }}" required>
            </div>

            <div>
                <label for="pass_mark">Passing Mark Threshold <span style="color: #ef4444;">*</span></label>
                <input type="number" step="0.01" min="0" max="1000" id="pass_mark" name="pass_mark" value="{{ old('pass_mark', $assessment?->pass_mark ?? '50.00') }}" required>
            </div>

            <div>
                <label for="weight_percentage">Weighting in Final Grade (%) <span style="color: #ef4444;">*</span></label>
                <input type="number" step="0.01" min="0" max="100" id="weight_percentage" name="weight_percentage" value="{{ old('weight_percentage', $assessment?->weight_percentage ?? '100.00') }}" required>
            </div>

            <div>
                <label for="assessment_date">Examination / Due Date</label>
                <input type="date" id="assessment_date" name="assessment_date" value="{{ old('assessment_date', $assessment?->assessment_date?->format('Y-m-d')) }}">
            </div>

            <div>
                <label for="status">Assessment Lifecycle Status <span style="color: #ef4444;">*</span></label>
                <select id="status" name="status" required>
                    <option value="draft" @selected(old('status', $assessment?->status ?? 'scheduled') === 'draft')>Draft</option>
                    <option value="scheduled" @selected(old('status', $assessment?->status ?? 'scheduled') === 'scheduled')>Scheduled</option>
                    <option value="completed" @selected(old('status', $assessment?->status) === 'completed')>Completed (Graded)</option>
                    <option value="cancelled" @selected(old('status', $assessment?->status) === 'cancelled')>Cancelled</option>
                </select>
            </div>

            <div class="full-width">
                <label for="description">Instructions / Remarks</label>
                <textarea id="description" name="description" rows="2" placeholder="Exam format, syllabus topics, grading guidelines...">{{ old('description', $assessment?->description) }}</textarea>
            </div>

            <div class="full-width" style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem;">
                <a href="{{ route('assessments.index') }}" class="secondary-link">Cancel</a>
                <button type="submit" class="primary-button">{{ $assessment ? 'Update assessment' : 'Create assessment setup' }}</button>
            </div>
        </form>
    </section>
@endsection
