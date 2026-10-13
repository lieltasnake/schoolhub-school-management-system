@extends('layouts.app')

@section('title', 'Teachers')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Academic Staff</p>
            <h1>Teachers</h1>
            <p class="intro-copy">Manage teaching faculty, qualifications, and employment details.</p>
        </div>
        <a href="{{ route('teachers.create') }}" class="primary-link">Add teacher</a>
    </section>

    <section class="content-card">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <form method="GET" action="{{ route('teachers.index') }}" class="filter-form">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by name, employee ID, phone..." aria-label="Search teachers">
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
                @foreach (['active', 'on_leave', 'resigned', 'terminated'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
            <button type="submit" class="secondary-button">Apply filters</button>
        </form>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Campus</th>
                        <th>Specialization</th>
                        <th>Phone</th>
                        <th>Joining date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($teachers as $teacher)
                        <tr>
                            <td><strong>{{ $teacher->employee_id }}</strong></td>
                            <td>{{ $teacher->user->name }}</td>
                            <td>{{ $teacher->branch->name }}</td>
                            <td>{{ $teacher->specialization ?? '—' }}</td>
                            <td>{{ $teacher->phone ?? '—' }}</td>
                            <td>{{ $teacher->joining_date?->format('M d, Y') ?? '—' }}</td>
                            <td><span class="status-pill {{ $teacher->status }}">{{ ucfirst(str_replace('_', ' ', $teacher->status)) }}</span></td>
                            <td>
                                <a href="{{ route('teachers.show', $teacher) }}" class="table-link">View</a>
                                <a href="{{ route('teachers.edit', $teacher) }}" class="table-link">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8">No teachers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $teachers->links() }}
    </section>
@endsection
