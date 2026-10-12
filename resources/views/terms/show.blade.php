@extends('layouts.app')

@section('title', $term->name)

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow"><a class="text-link" href="{{ route('academic-years.show', $term->academicYear) }}">{{ $term->academicYear->name }}</a> / {{ $term->academicYear->branch->name }}</p>
            <h1>{{ $term->name }}</h1>
            <p class="intro-copy">{{ $term->start_date->format('F j, Y') }} through {{ $term->end_date->format('F j, Y') }}</p>
        </div>
        <a href="{{ route('terms.edit', $term) }}" class="secondary-link">Edit term</a>
    </section>

    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif

    <section class="content-card">
        <div class="section-heading">
            <div><p class="eyebrow">Term status</p><h2>{{ ucfirst($term->status) }}</h2></div>
            @if ($term->status !== 'active')
                <form method="POST" action="{{ route('terms.activate', $term) }}" onsubmit="return confirm('Activate this term for {{ $term->academicYear->name }}? The current active term will be completed.');">
                    @csrf
                    <button type="submit" class="primary-button">Set as active term</button>
                </form>
            @else
                <span class="status-pill active">Active term</span>
            @endif
        </div>
        <dl class="detail-list">
            <div><dt>Campus</dt><dd>{{ $term->academicYear->branch->name }}</dd></div>
            <div><dt>Academic year</dt><dd>{{ $term->academicYear->name }} ({{ ucfirst($term->academicYear->status) }})</dd></div>
        </dl>
    </section>
@endsection