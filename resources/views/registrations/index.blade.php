@extends('layouts.app')

@section('title', 'Student Registration')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Registration</p>
            <h1>Student registration applications</h1>
            <p class="intro-copy">Manage admission inquiries, registration forms, and applicant review workflows.</p>
        </div>
        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
            <a href="{{ route('registrations.promotion') }}" class="secondary-link">Review for promotion</a>
            <a href="{{ route('registrations.create') }}" class="primary-link">New registration</a>
        </div>
    </section>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <div class="dashboard-grid" style="margin-bottom: 1.5rem;">
        <article class="metric-card">
            <span class="metric-label">Total applications</span>
            <strong>{{ $totalCount }}</strong>
            <small>All registration submissions</small>
        </article>
        <article class="metric-card">
            <span class="metric-label">Pending review</span>
            <strong>{{ $pendingCount }}</strong>
            <small>Awaiting administrative approval</small>
        </article>
        <article class="metric-card">
            <span class="metric-label">Approved</span>
            <strong>{{ $approvedCount }}</strong>
            <small>Ready for student promotion</small>
        </article>
        <article class="metric-card">
            <span class="metric-label">Enrolled</span>
            <strong>{{ $enrolledCount }}</strong>
            <small>Successfully admitted as students</small>
        </article>
    </div>

    <section class="content-card">
        <form method="GET" action="{{ route('registrations.index') }}" class="filter-form">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by applicant, reg #, or phone" aria-label="Search registrations">

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
                @foreach ($academicYears as $year)
                    <option value="{{ $year->id }}" @selected((string) request('academic_year_id') === (string) $year->id)>{{ $year->name }}</option>
                @endforeach
            </select>

            <select name="grade_id" aria-label="Filter by grade">
                <option value="">All grades</option>
                @foreach ($grades as $grade)
                    <option value="{{ $grade->id }}" @selected((string) request('grade_id') === (string) $grade->id)>{{ $grade->name }}</option>
                @endforeach
            </select>

            <select name="status" aria-label="Filter by status">
                <option value="">All statuses</option>
                @foreach (['pending', 'approved', 'enrolled', 'rejected'] as $statusOption)
                    <option value="{{ $statusOption }}" @selected(request('status') === $statusOption)>{{ ucfirst($statusOption) }}</option>
                @endforeach
            </select>

            <button type="submit" class="secondary-button">Filter</button>
            @if (request()->hasAny(['search', 'branch_id', 'academic_year_id', 'grade_id', 'status']))
                <a href="{{ route('registrations.index') }}" class="text-link" style="align-self: center;">Reset</a>
            @endif
        </form>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Reg #</th>
                        <th>Applicant name</th>
                        <th>Campus</th>
                        <th>Target grade</th>
                        <th>Guardian</th>
                        <th>Registration date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($registrations as $reg)
                        <tr>
                            <td>
                                <strong>{{ $reg->registration_number }}</strong>
                            </td>
                            <td>
                                <a href="{{ route('registrations.show', $reg) }}" class="table-link" style="font-weight: 600;">
                                    {{ $reg->full_name }}
                                </a>
                                @if ($reg->studentType)
                                    <small style="display: block; color: var(--muted);">{{ $reg->studentType->name }}</small>
                                @endif
                            </td>
                            <td>{{ $reg->branch->name }}</td>
                            <td>{{ $reg->grade->name }}</td>
                            <td>
                                <div>{{ $reg->guardian_name }}</div>
                                <small style="color: var(--muted);">{{ $reg->guardian_phone }}</small>
                            </td>
                            <td>{{ $reg->registration_date->format('M d, Y') }}</td>
                            <td>
                                <span class="badge status-{{ $reg->status }}">
                                    {{ ucfirst($reg->status) }}
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                    <a href="{{ route('registrations.show', $reg) }}" class="text-link">View</a>
                                    <a href="{{ route('registrations.edit', $reg) }}" class="text-link">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2rem; color: var(--muted);">
                                No registration records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($registrations->hasPages())
            <div style="margin-top: 1.5rem;">
                {{ $registrations->links() }}
            </div>
        @endif
    </section>
@endsection
