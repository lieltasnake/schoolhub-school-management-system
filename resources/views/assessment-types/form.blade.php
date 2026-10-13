@extends('layouts.app')

@section('title', $type ? 'Edit Assessment Type' : 'New Assessment Type')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Exams & Assessments / Types</p>
            <h1>{{ $type ? "Edit type: {$type->name}" : 'New assessment type' }}</h1>
            <p class="intro-copy">Configure examination or grading component types and their default weightings.</p>
        </div>
        <a href="{{ route('assessment-types.index') }}" class="secondary-link">Cancel</a>
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

        <form method="POST" action="{{ $type ? route('assessment-types.update', $type) : route('assessment-types.store') }}" class="form-grid">
            @csrf
            @if ($type)
                @method('PUT')
            @endif

            @if ($type)
                <div>
                    <label>Campus</label>
                    <input type="text" value="{{ $type->branch->name }}" disabled>
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
                <label for="name">Type Name <span style="color: #ef4444;">*</span></label>
                <input id="name" name="name" value="{{ old('name', $type?->name) }}" placeholder="e.g. Midterm Examination, Homework Quiz" required>
            </div>

            <div>
                <label for="code">Unique Code <span style="color: #ef4444;">*</span></label>
                <input id="code" name="code" value="{{ old('code', $type?->code) }}" placeholder="e.g. MIDTERM, QUIZ, FINAL" required>
            </div>

            <div>
                <label for="weight_percentage">Default Weight Percentage (%) <span style="color: #ef4444;">*</span></label>
                <input type="number" step="0.01" min="0" max="100" id="weight_percentage" name="weight_percentage" value="{{ old('weight_percentage', $type?->weight_percentage ?? '20.00') }}" required>
            </div>

            <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 1.5rem;">
                <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $type?->is_active ?? true))>
                <label for="is_active" style="margin: 0; font-weight: 500;">Active</label>
            </div>

            <div class="full-width">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="2" placeholder="Purpose, guidelines, rubric...">{{ old('description', $type?->description) }}</textarea>
            </div>

            <div class="full-width" style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem;">
                <a href="{{ route('assessment-types.index') }}" class="secondary-link">Cancel</a>
                <button type="submit" class="primary-button">{{ $type ? 'Update type' : 'Create type' }}</button>
            </div>
        </form>
    </section>
@endsection
