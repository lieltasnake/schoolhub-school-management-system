@extends('layouts.app')

@section('title', 'Terms and Semesters')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">School setup</p>
            <h1>Terms and semesters</h1>
            <p class="intro-copy">Set calendar periods within each branch's academic year.</p>
        </div>
        <a href="{{ route('terms.create') }}" class="primary-link">Add term</a>
    </section>

    <section class="content-card">
        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        <form method="GET" action="{{ route('terms.index') }}" class="filter-form">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search terms" aria-label="Search terms">
            @if ($branches->count() > 1)
                <select name="branch_id" aria-label="Filter by campus">
                    <option value="">All campuses</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            @endif
            <select name="academic_year_id" aria-label="Filter by academic year">
                <option value="">All academic years</option>
                @foreach ($academicYears as $academicYear)
                    <option value="{{ $academicYear->id }}" @selected((string) request('academic_year_id') === (string) $academicYear->id)>{{ $academicYear->branch->name }} / {{ $academicYear->name }}</option>
                @endforeach
            </select>
            <select name="status" aria-label="Filter by status">
                <option value="">All statuses</option>
                @foreach (['planned', 'active', 'completed'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <button type="submit" class="secondary-button">Apply filters</button>
        </form>

        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Term</th><th>Academic year</th><th>Campus</th><th>Dates</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse ($terms as $term)
                        <tr>
                            <td>{{ $term->name }}</td>
                            <td>{{ $term->academicYear->name }}</td>
                            <td>{{ $term->academicYear->branch->name }}</td>
                            <td>{{ $term->start_date->format('M j, Y') }} - {{ $term->end_date->format('M j, Y') }}</td>
                            <td><span class="status-pill {{ $term->status }}">{{ ucfirst($term->status) }}</span></td>
                            <td><a href="{{ route('terms.show', $term) }}" class="table-link">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No terms configured for the selected scope.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $terms->links() }}
    </section>
@endsection