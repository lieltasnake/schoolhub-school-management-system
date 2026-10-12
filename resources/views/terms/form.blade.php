@extends('layouts.app')

@section('title', $term ? 'Edit Term' : 'Create Term')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">School setup / Academic calendar</p>
            <h1>{{ $term ? 'Edit term' : 'Create term or semester' }}</h1>
            <p class="intro-copy">Term dates must fit entirely within the selected academic year.</p>
        </div>
        <a href="{{ $term ? route('terms.show', $term) : route('terms.index') }}" class="secondary-link">Cancel</a>
    </section>

    <section class="content-card">
        @if ($errors->any())
            <div class="alert alert-error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <form method="POST" action="{{ $term ? route('terms.update', $term) : route('terms.store') }}" class="form-grid">
            @csrf
            @if ($term)
                @method('PUT')
            @endif

            @unless ($term)
                <div class="full-width">
                    <label for="academic_year_id">Academic year</label>
                    <select id="academic_year_id" name="academic_year_id" required>
                        <option value="">Choose academic year</option>
                        @foreach ($academicYears as $academicYear)
                            <option value="{{ $academicYear->id }}" @selected((string) old('academic_year_id', request('academic_year_id')) === (string) $academicYear->id)>{{ $academicYear->branch->name }} / {{ $academicYear->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endunless

            <div>
                <label for="name">Term name</label>
                <input id="name" name="name" value="{{ old('name', $term?->name) }}" maxlength="80" placeholder="For example, Term 1" required>
            </div>
            <div>
                <label for="status">Status</label>
                @if ($term?->status === 'active')
                    <p class="field-note">Active. Use the term detail page to change the active term.</p>
                @else
                    <select id="status" name="status">
                        <option value="planned" @selected(old('status', $term?->status ?? 'planned') === 'planned')>Planned</option>
                        <option value="completed" @selected(old('status', $term?->status) === 'completed')>Completed</option>
                    </select>
                @endif
            </div>
            <div>
                <label for="start_date">Start date</label>
                <input id="start_date" type="date" name="start_date" value="{{ old('start_date', $term?->start_date?->toDateString()) }}" required>
            </div>
            <div>
                <label for="end_date">End date</label>
                <input id="end_date" type="date" name="end_date" value="{{ old('end_date', $term?->end_date?->toDateString()) }}" required>
            </div>
            <div class="full-width form-actions"><button type="submit" class="primary-button">{{ $term ? 'Save changes' : 'Create term' }}</button></div>
        </form>
    </section>
@endsection