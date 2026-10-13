@extends('layouts.app')

@section('title', $schoolHour ? 'Edit School Hours' : 'Create School Hours')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">School Setup</p>
            <h1>{{ $schoolHour ? 'Edit school hours' : 'Create school hours' }}</h1>
            <p class="intro-copy">Define operational opening/closing schedule and active days.</p>
        </div>
        <a href="{{ route('school-hours.index') }}" class="secondary-link">Cancel</a>
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

        <form method="POST" action="{{ $schoolHour ? route('school-hours.update', $schoolHour) : route('school-hours.store') }}" class="form-grid">
            @csrf
            @if ($schoolHour)
                @method('PUT')
            @endif

            <div class="full-width">
                <label for="branch_id">Campus</label>
                <select id="branch_id" name="branch_id" required>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) old('branch_id', $schoolHour?->branch_id) === (string) $branch->id)>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="shift_name">Shift name</label>
                <input id="shift_name" name="shift_name" value="{{ old('shift_name', $schoolHour?->shift_name) }}" maxlength="255" placeholder="e.g. Regular Day Shift, Morning Kindergarten" required>
            </div>

            <div>
                <label for="opening_time">Opening time</label>
                <input id="opening_time" name="opening_time" type="time" value="{{ old('opening_time', $schoolHour?->opening_time) }}" required>
            </div>

            <div>
                <label for="closing_time">Closing time</label>
                <input id="closing_time" name="closing_time" type="time" value="{{ old('closing_time', $schoolHour?->closing_time) }}" required>
            </div>

            <div>
                <label style="display:flex; align-items:center; gap:0.5rem; margin-top:1.8rem; cursor:pointer;">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $schoolHour?->is_active ?? true))>
                    <span>Shift is currently active</span>
                </label>
            </div>

            <div class="full-width">
                <label>Operating days</label>
                <div style="display:flex; flex-wrap:wrap; gap:1.25rem; margin-top:0.5rem;">
                    @php
                        $selectedDays = old('operating_days', $schoolHour?->operating_days ?? ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']);
                    @endphp
                    @foreach ($availableDays as $day)
                        <label style="display:flex; align-items:center; gap:0.35rem; cursor:pointer;">
                            <input type="checkbox" name="operating_days[]" value="{{ $day }}" @checked(in_array($day, (array) $selectedDays))>
                            <span>{{ $day }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="full-width form-actions">
                <button type="submit" class="primary-button">{{ $schoolHour ? 'Update shift' : 'Create shift' }}</button>
            </div>
        </form>
    </section>
@endsection
