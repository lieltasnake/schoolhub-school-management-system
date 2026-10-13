@extends('layouts.app')

@section('title', $period ? 'Edit Period' : 'Create Period')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">School Setup</p>
            <h1>{{ $period ? 'Edit period' : 'Create period' }}</h1>
            <p class="intro-copy">Set up timetable period slot, timing, and break intervals.</p>
        </div>
        <a href="{{ route('periods.index') }}" class="secondary-link">Cancel</a>
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

        <form method="POST" action="{{ $period ? route('periods.update', $period) : route('periods.store') }}" class="form-grid">
            @csrf
            @if ($period)
                @method('PUT')
            @endif

            <div class="full-width">
                <label for="branch_id">Campus</label>
                <select id="branch_id" name="branch_id" required>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) old('branch_id', $period?->branch_id) === (string) $branch->id)>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="period_number">Period number</label>
                <input id="period_number" name="period_number" type="number" min="1" max="20" value="{{ old('period_number', $period?->period_number) }}" required>
            </div>

            <div>
                <label for="name">Period name</label>
                <input id="name" name="name" value="{{ old('name', $period?->name) }}" maxlength="255" placeholder="e.g. Period 1, Lunch Break" required>
            </div>

            <div>
                <label for="start_time">Start time</label>
                <input id="start_time" name="start_time" type="time" value="{{ old('start_time', $period?->start_time) }}" required>
            </div>

            <div>
                <label for="end_time">End time</label>
                <input id="end_time" name="end_time" type="time" value="{{ old('end_time', $period?->end_time) }}" required>
            </div>

            <div>
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    @foreach (['active', 'inactive'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $period?->status ?? 'active') === $status)>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="display:flex; align-items:center; gap:0.5rem; margin-top:1.8rem; cursor:pointer;">
                    <input type="checkbox" name="is_break" value="1" @checked(old('is_break', $period?->is_break))>
                    <span>Is a break / recess period (no classes assigned)</span>
                </label>
            </div>

            <div class="full-width form-actions">
                <button type="submit" class="primary-button">{{ $period ? 'Update period' : 'Create period' }}</button>
            </div>
        </form>
    </section>
@endsection
