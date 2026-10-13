@extends('layouts.app')

@section('title', $studentType ? 'Edit Student Type' : 'New Student Type')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Students / Student Types</p>
            <h1>{{ $studentType ? "Edit type: {$studentType->name}" : 'New student type' }}</h1>
            <p class="intro-copy">Create a student classification for tracking and fee billing purposes.</p>
        </div>
        <a href="{{ route('student-types.index') }}" class="secondary-link">Cancel</a>
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

        <form method="POST" action="{{ $studentType ? route('student-types.update', $studentType) : route('student-types.store') }}" class="form-grid">
            @csrf
            @if ($studentType)
                @method('PUT')
            @endif

            <div>
                <label for="branch_id">Campus Scope (Optional)</label>
                <select id="branch_id" name="branch_id">
                    <option value="">All Campuses (System-wide)</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) old('branch_id', $studentType?->branch_id ?? $selectedBranchId) === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
                <small style="color: var(--muted);">Leave blank to make available across all campuses</small>
            </div>

            <div>
                <label for="name">Type Name <span style="color: #ef4444;">*</span></label>
                <input id="name" name="name" value="{{ old('name', $studentType?->name) }}" placeholder="e.g. Day Scholar, Boarder, Scholarship" required>
            </div>

            <div>
                <label for="code">Unique Code <span style="color: #ef4444;">*</span></label>
                <input id="code" name="code" value="{{ old('code', $studentType?->code) }}" placeholder="e.g. DAY, BOARD, SCHOLAR" required>
            </div>

            <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 1.5rem;">
                <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $studentType?->is_active ?? true))>
                <label for="is_active" style="margin: 0; font-weight: 500;">Active</label>
            </div>

            <div class="full-width">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="2" placeholder="Eligibility, notes, privileges...">{{ old('description', $studentType?->description) }}</textarea>
            </div>

            <div class="full-width" style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem;">
                <a href="{{ route('student-types.index') }}" class="secondary-link">Cancel</a>
                <button type="submit" class="primary-button">{{ $studentType ? 'Update student type' : 'Create student type' }}</button>
            </div>
        </form>
    </section>
@endsection
