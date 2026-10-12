@extends('layouts.app')

@section('title', $academicYear ? 'Edit Academic Year' : 'Create Academic Year')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">School setup</p>
            <h1>{{ $academicYear ? 'Edit academic year' : 'Create academic year' }}</h1>
            <p class="intro-copy">Academic calendars are managed separately for each campus.</p>
        </div>
        <a href="{{ $academicYear ? route('academic-years.show', $academicYear) : route('academic-years.index') }}" class="secondary-link">Cancel</a>
    </section>

    <section class="content-card">
        @if ($errors->any())
            <div class="alert alert-error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <form method="POST" action="{{ $academicYear ? route('academic-years.update', $academicYear) : route('academic-years.store') }}" class="form-grid">
            @csrf
            @if ($academicYear)
                @method('PUT')
            @endif

            @unless ($academicYear)
                <div class="full-width">
                    <label for="branch_id">Campus</label>
                    <select id="branch_id" name="branch_id" required>
                        <option value="">Choose campus</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected((string) old('branch_id', request('branch_id')) === (string) $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endunless

            <div>
                <label for="name">Academic year name</label>
                <input id="name" name="name" value="{{ old('name', $academicYear?->name) }}" maxlength="80" placeholder="For example, 2026-2027" required>
            </div>
            <div>
                <label for="status">Status</label>
                @if ($academicYear?->status === 'active')
                    <p class="field-note">Active. Use the year detail page to change the active academic year.</p>
                @else
                    <select id="status" name="status">
                        <option value="planned" @selected(old('status', $academicYear?->status ?? 'planned') === 'planned')>Planned</option>
                        <option value="completed" @selected(old('status', $academicYear?->status) === 'completed')>Completed</option>
                    </select>
                @endif
            </div>
            <div>
                <label for="start_date">Start date</label>
                <input id="start_date" type="date" name="start_date" value="{{ old('start_date', $academicYear?->start_date?->toDateString()) }}" required>
            </div>
            <div>
                <label for="end_date">End date</label>
                <input id="end_date" type="date" name="end_date" value="{{ old('end_date', $academicYear?->end_date?->toDateString()) }}" required>
            </div>
            <div class="full-width form-actions">
                <button type="submit" class="primary-button">{{ $academicYear ? 'Save changes' : 'Create academic year' }}</button>
            </div>
        </form>
    </section>
@endsection