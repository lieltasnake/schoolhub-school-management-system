@extends('layouts.app')

@section('title', 'Academic Years')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">School setup</p>
            <h1>Academic years</h1>
            <p class="intro-copy">Configure the academic calendar independently for each campus.</p>
        </div>
        <a href="{{ route('academic-years.create') }}" class="primary-link">Add academic year</a>
    </section>

    <section class="content-card">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="GET" action="{{ route('academic-years.index') }}" class="filter-form">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search academic years" aria-label="Search academic years">
            @if ($branches->count() > 1)
                <select name="branch_id" aria-label="Filter by campus">
                    <option value="">All campuses</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            @endif
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
                <thead>
                    <tr><th>Academic year</th><th>Campus</th><th>Dates</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @forelse ($academicYears as $academicYear)
                        <tr>
                            <td>{{ $academicYear->name }}</td>
                            <td>{{ $academicYear->branch->name }}</td>
                            <td>{{ $academicYear->start_date->format('M j, Y') }} - {{ $academicYear->end_date->format('M j, Y') }}</td>
                            <td><span class="status-pill {{ $academicYear->status }}">{{ ucfirst($academicYear->status) }}</span></td>
                            <td>
                                <a href="{{ route('academic-years.show', $academicYear) }}" class="table-link">View</a>
                                <a href="{{ route('academic-years.edit', $academicYear) }}" class="table-link">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5">No academic years configured for the selected scope.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $academicYears->links() }}
    </section>
@endsection