@extends('layouts.app')

@section('title', 'Grades & Classes')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">School setup</p>
            <h1>Grades and classes</h1>
            <p class="intro-copy">Define grade levels and educational tiers across your school campuses.</p>
        </div>
        <a href="{{ route('grades.create') }}" class="primary-link">Add grade</a>
    </section>

    <section class="content-card">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <form method="GET" action="{{ route('grades.index') }}" class="filter-form">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by name or code" aria-label="Search grades">
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
                @foreach (['active', 'inactive'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <button type="submit" class="secondary-button">Apply filters</button>
        </form>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Grade</th>
                        <th>Code</th>
                        <th>Level</th>
                        <th>Campus</th>
                        <th>Sections</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($grades as $grade)
                        <tr>
                            <td><strong>{{ $grade->name }}</strong></td>
                            <td>{{ $grade->code }}</td>
                            <td>{{ $grade->level }}</td>
                            <td>{{ $grade->branch->name }}</td>
                            <td>{{ $grade->sections_count }}</td>
                            <td><span class="status-pill {{ $grade->status }}">{{ ucfirst($grade->status) }}</span></td>
                            <td>
                                <a href="{{ route('grades.show', $grade) }}" class="table-link">View</a>
                                <a href="{{ route('grades.edit', $grade) }}" class="table-link">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No grades found for the selected scope.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $grades->links() }}
    </section>
@endsection
