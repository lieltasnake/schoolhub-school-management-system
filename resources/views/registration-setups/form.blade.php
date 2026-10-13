@extends('layouts.app')

@section('title', $setup ? 'Edit Registration Setup' : 'New Registration Setup')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Registration / Setup</p>
            <h1>{{ $setup ? "Edit setup: {$setup->title}" : 'New registration setup' }}</h1>
            <p class="intro-copy">Define application window dates, registration fees, and intake limits.</p>
        </div>
        <a href="{{ route('registrations.setup') }}" class="secondary-link">Cancel</a>
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

        <form method="POST" action="{{ $setup ? route('registrations.setup.update', $setup) : route('registrations.setup.store') }}" class="form-grid">
            @csrf
            @if ($setup)
                @method('PUT')
            @endif

            @if ($setup)
                <div>
                    <label>Campus</label>
                    <input type="text" value="{{ $setup->branch->name }}" disabled>
                    <input type="hidden" name="branch_id" value="{{ $setup->branch_id }}">
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
                <label for="title">Campaign / Setup Title <span style="color: #ef4444;">*</span></label>
                <input id="title" name="title" value="{{ old('title', $setup?->title) }}" placeholder="e.g. Fall 2026 Admissions" required>
            </div>

            <div>
                <label for="academic_year_id">Target Academic Year <span style="color: #ef4444;">*</span></label>
                <select id="academic_year_id" name="academic_year_id" required>
                    <option value="">Select academic year</option>
                    @foreach ($academicYears as $year)
                        <option value="{{ $year->id }}" @selected((string) old('academic_year_id', $setup?->academic_year_id) === (string) $year->id)>{{ $year->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status">Session Status <span style="color: #ef4444;">*</span></label>
                <select id="status" name="status" required>
                    <option value="open" @selected(old('status', $setup?->status ?? 'open') === 'open')>Open (Accepting Applications)</option>
                    <option value="upcoming" @selected(old('status', $setup?->status) === 'upcoming')>Upcoming (Scheduled)</option>
                    <option value="closed" @selected(old('status', $setup?->status) === 'closed')>Closed</option>
                </select>
            </div>

            <div>
                <label for="start_date">Opening Date <span style="color: #ef4444;">*</span></label>
                <input type="date" id="start_date" name="start_date" value="{{ old('start_date', $setup?->start_date?->format('Y-m-d') ?? now()->toDateString()) }}" required>
            </div>

            <div>
                <label for="end_date">Closing Date <span style="color: #ef4444;">*</span></label>
                <input type="date" id="end_date" name="end_date" value="{{ old('end_date', $setup?->end_date?->format('Y-m-d') ?? now()->addMonths(2)->toDateString()) }}" required>
            </div>

            <div>
                <label for="application_fee">Application Fee ($)</label>
                <input type="number" step="0.01" min="0" id="application_fee" name="application_fee" value="{{ old('application_fee', $setup?->application_fee ?? '0.00') }}" placeholder="0.00">
            </div>

            <div>
                <label for="capacity">Applicant Capacity Limit (optional)</label>
                <input type="number" min="1" id="capacity" name="capacity" value="{{ old('capacity', $setup?->capacity) }}" placeholder="e.g. 150">
            </div>

            <div class="full-width">
                <label for="description">Instructions / Description</label>
                <textarea id="description" name="description" rows="3" placeholder="Admission requirements, required documentation, guidelines...">{{ old('description', $setup?->description) }}</textarea>
            </div>

            <div class="full-width" style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem;">
                <a href="{{ route('registrations.setup') }}" class="secondary-link">Cancel</a>
                <button type="submit" class="primary-button">{{ $setup ? 'Update setup' : 'Create setup' }}</button>
            </div>
        </form>
    </section>
@endsection
