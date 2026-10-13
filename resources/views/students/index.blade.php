@extends('layouts.app')

@section('title', 'Students & Guardians')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">People</p>
            <h1>Students and guardians</h1>
            <p class="intro-copy">Manage student admissions, academic placements, and guardian contact records.</p>
        </div>
        @can('create', App\Models\Student::class)
            <a href="{{ route('students.create') }}" class="primary-link">Register student</a>
        @endcan
    </section>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <div class="dashboard-grid" style="margin-bottom: 1.5rem;">
        <article class="metric-card">
            <span class="metric-label">Total registered students</span>
            <strong>{{ $totalStudents }}</strong>
            <small>All records in scope</small>
        </article>
        <article class="metric-card">
            <span class="metric-label">Active students</span>
            <strong>{{ $activeStudents }}</strong>
            <small>Currently attending</small>
        </article>
        <article class="metric-card">
            <span class="metric-label">Inactive / departed</span>
            <strong>{{ $inactiveStudents }}</strong>
            <small>Transferred, suspended, or inactive</small>
        </article>
    </div>

    <section class="content-card">
        <form method="GET" action="{{ route('students.index') }}" class="filter-form">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by name, admission no, or phone" aria-label="Search students">

            @if ($branches->count() > 1)
                <select name="branch_id" aria-label="Filter by campus">
                    <option value="">All campuses</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            @endif

            <select name="grade_id" aria-label="Filter by grade">
                <option value="">All grades</option>
                @foreach ($grades as $grade)
                    <option value="{{ $grade->id }}" @selected((string) request('grade_id') === (string) $grade->id)>{{ $grade->name }}</option>
                @endforeach
            </select>

            <select name="status" aria-label="Filter by status">
                <option value="">All statuses</option>
                @foreach (['active', 'inactive', 'graduated', 'suspended', 'transferred'] as $statusOption)
                    <option value="{{ $statusOption }}" @selected(request('status') === $statusOption)>{{ ucfirst($statusOption) }}</option>
                @endforeach
            </select>

            <button type="submit" class="secondary-button">Filter</button>
            @if (request()->hasAny(['search', 'branch_id', 'grade_id', 'status']))
                <a href="{{ route('students.index') }}" class="text-link" style="align-self: center;">Reset</a>
            @endif
        </form>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Admission #</th>
                        <th>Student name</th>
                        <th>Campus</th>
                        <th>Class placement</th>
                        <th>Primary guardian</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $student)
                        @php
                            $enrollment = $student->latestEnrollment;
                            $guardian = $student->primary_guardian;
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $student->admission_number }}</strong>
                            </td>
                            <td>
                                <a href="{{ route('students.show', $student) }}" class="table-link" style="font-weight: 600;">
                                    {{ $student->full_name }}
                                </a>
                                <br>
                                <small style="color: var(--muted, #64748b);">{{ ucfirst($student->gender) }} &bull; {{ $student->date_of_birth->format('M d, Y') }}</small>
                            </td>
                            <td>{{ $student->branch->name }}</td>
                            <td>
                                @if ($enrollment)
                                    <span>{{ $enrollment->grade->name }}</span>
                                    @if ($enrollment->section)
                                        <small style="display: block; color: var(--muted, #64748b);">Section: {{ $enrollment->section->name }}</small>
                                    @endif
                                @else
                                    <span style="color: var(--muted, #64748b);">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                @if ($guardian)
                                    <span>{{ $guardian->full_name }}</span>
                                    <small style="display: block; color: var(--muted, #64748b);">{{ $guardian->phone }}</small>
                                @else
                                    <span style="color: var(--muted, #64748b);">None listed</span>
                                @endif
                            </td>
                            <td>
                                <span class="status-pill {{ $student->status }}">{{ ucfirst($student->status) }}</span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                    <a href="{{ route('students.show', $student) }}" class="table-link">View</a>
                                    @can('update', $student)
                                        <a href="{{ route('students.edit', $student) }}" class="table-link">Edit</a>
                                        <form method="POST" action="{{ route('students.toggle-status', $student) }}" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="table-link" style="background: none; border: none; cursor: pointer; padding: 0;">
                                                {{ $student->status === 'active' ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">No students found matching your filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $students->links() }}
    </section>
@endsection
