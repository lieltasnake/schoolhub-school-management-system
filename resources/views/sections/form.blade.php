@extends('layouts.app')

@section('title', $section ? 'Edit Section' : 'Create Section')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">School setup</p>
            <h1>{{ $section ? 'Edit section' : 'Create section' }}</h1>
            <p class="intro-copy">Configure class sections and room capacities.</p>
        </div>
        <a href="{{ $section ? route('sections.show', $section) : route('sections.index') }}" class="secondary-link">Cancel</a>
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

        <form method="POST" action="{{ $section ? route('sections.update', $section) : route('sections.store') }}" class="form-grid">
            @csrf
            @if ($section)
                @method('PUT')
            @endif

            <div class="full-width">
                <label for="grade_id">Grade level</label>
                @if ($section)
                    <input type="text" value="{{ $section->grade->branch->name }} — {{ $section->grade->name }}" disabled>
                @else
                    <select id="grade_id" name="grade_id" required>
                        <option value="">Choose grade</option>
                        @foreach ($grades as $grade)
                            <option value="{{ $grade->id }}" @selected((string) old('grade_id', $selectedGradeId) === (string) $grade->id)>
                                {{ $grade->branch->name }} &bull; {{ $grade->name }}
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>

            <div>
                <label for="name">Section name</label>
                <input id="name" name="name" value="{{ old('name', $section?->name) }}" maxlength="80" placeholder="e.g. Section A or Blue" required>
            </div>

            <div>
                <label for="code">Section code (optional)</label>
                <input id="code" name="code" value="{{ old('code', $section?->code) }}" maxlength="30" placeholder="e.g. G1-A">
            </div>

            <div>
                <label for="academic_year_id">Academic year (optional)</label>
                <select id="academic_year_id" name="academic_year_id">
                    <option value="">All academic years</option>
                    @foreach ($academicYears as $year)
                        <option value="{{ $year->id }}" @selected((string) old('academic_year_id', $section?->academic_year_id) === (string) $year->id)>
                            {{ $year->branch->name }} / {{ $year->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="capacity">Student capacity</label>
                <input id="capacity" type="number" name="capacity" value="{{ old('capacity', $section?->capacity ?? 40) }}" min="1" max="500" required>
            </div>

            <div>
                <label for="room_number">Room number (optional)</label>
                <input id="room_number" name="room_number" value="{{ old('room_number', $section?->room_number) }}" maxlength="50" placeholder="e.g. Room 102">
            </div>

            <div>
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="active" @selected(old('status', $section?->status ?? 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $section?->status) === 'inactive')>Inactive</option>
                </select>
            </div>

            <div class="full-width form-actions">
                <button type="submit" class="primary-button">{{ $section ? 'Save changes' : 'Create section' }}</button>
            </div>
        </form>
    </section>
@endsection
