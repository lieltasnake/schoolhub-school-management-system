@extends('layouts.app')

@section('title', $section->name)

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">{{ $section->grade->branch->name }} / {{ $section->grade->name }}</p>
            <h1>{{ $section->name }}</h1>
            <p class="intro-copy">Section for {{ $section->grade->name }} &bull; {{ $section->grade->branch->name }}</p>
        </div>
        <div class="intro-actions">
            <a href="{{ route('sections.edit', $section) }}" class="secondary-link">Edit</a>
            <a href="{{ route('grades.show', $section->grade) }}" class="secondary-link">View grade</a>
            <form method="POST" action="{{ route('sections.destroy', $section) }}" class="inline-action" onsubmit="return confirm('Are you sure you want to delete this section?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="table-link danger-link">Delete</button>
            </form>
        </div>
    </section>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <section class="content-card">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Section details</p>
                <h2>Overview</h2>
            </div>
            <span class="status-pill {{ $section->status }}">{{ ucfirst($section->status) }}</span>
        </div>
        <dl class="detail-list">
            <div><dt>Campus</dt><dd>{{ $section->grade->branch->name }}</dd></div>
            <div><dt>Grade</dt><dd><a href="{{ route('grades.show', $section->grade) }}" class="table-link">{{ $section->grade->name }}</a></dd></div>
            <div><dt>Section code</dt><dd>{{ $section->code ?? '—' }}</dd></div>
            <div><dt>Academic year</dt><dd>{{ $section->academicYear?->name ?? 'Applicable to all years' }}</dd></div>
            <div><dt>Room number</dt><dd>{{ $section->room_number ?? 'Not assigned' }}</dd></div>
            <div><dt>Student capacity</dt><dd>{{ $section->capacity }}</dd></div>
        </dl>
    </section>
@endsection
