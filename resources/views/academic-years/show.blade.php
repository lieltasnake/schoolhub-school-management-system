@extends('layouts.app')

@section('title', $academicYear->name)

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">{{ $academicYear->branch->name }} / Academic year</p>
            <h1>{{ $academicYear->name }}</h1>
            <p class="intro-copy">{{ $academicYear->start_date->format('F j, Y') }} through {{ $academicYear->end_date->format('F j, Y') }}</p>
        </div>
        <div class="intro-actions">
            <a href="{{ route('academic-years.edit', $academicYear) }}" class="secondary-link">Edit</a>
            <a href="{{ route('terms.create', ['academic_year_id' => $academicYear->id]) }}" class="primary-link">Add term</a>
        </div>
    </section>

    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif

    <section class="content-card">
        <div class="section-heading">
            <div><p class="eyebrow">Calendar status</p><h2>{{ ucfirst($academicYear->status) }}</h2></div>
            @if ($academicYear->status !== 'active')
                <form method="POST" action="{{ route('academic-years.activate', $academicYear) }}" onsubmit="return confirm('Activate this academic year for {{ $academicYear->branch->name }}? The current active year will be completed.');">
                    @csrf
                    <button type="submit" class="primary-button">Set as active year</button>
                </form>
            @else
                <span class="status-pill active">Active academic year</span>
            @endif
        </div>
        <dl class="detail-list">
            <div><dt>Campus</dt><dd>{{ $academicYear->branch->name }}</dd></div>
            <div><dt>Terms configured</dt><dd>{{ $academicYear->terms->count() }}</dd></div>
        </dl>
    </section>

    <section class="content-card">
        <div class="section-heading"><div><p class="eyebrow">Academic calendar</p><h2>Terms and semesters</h2></div></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Name</th><th>Dates</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse ($academicYear->terms as $term)
                        <tr>
                            <td>{{ $term->name }}</td>
                            <td>{{ $term->start_date->format('M j, Y') }} - {{ $term->end_date->format('M j, Y') }}</td>
                            <td><span class="status-pill {{ $term->status }}">{{ ucfirst($term->status) }}</span></td>
                            <td><a href="{{ route('terms.show', $term) }}" class="table-link">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4">No terms are configured for this academic year.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection