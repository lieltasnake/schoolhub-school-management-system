@extends('layouts.app')

@section('title', 'Student Types')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Students</p>
            <h1>Student types & categories</h1>
            <p class="intro-copy">Categorize students (e.g. Regular, Boarding, Day Scholar, Scholarship, Special Needs).</p>
        </div>
        <a href="{{ route('student-types.create') }}" class="primary-link">New student type</a>
    </section>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <section class="content-card">
        <form method="GET" action="{{ route('student-types.index') }}" class="filter-form">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by name or code" aria-label="Search student types">

            @if ($branches->count() > 1)
                <select name="branch_id" aria-label="Filter by campus">
                    <option value="">All campuses</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) request('branch_id', $selectedBranchId) === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            @endif

            <button type="submit" class="secondary-button">Filter</button>
            @if (request()->hasAny(['search', 'branch_id']))
                <a href="{{ route('student-types.index') }}" class="text-link" style="align-self: center;">Reset</a>
            @endif
        </form>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Type name</th>
                        <th>Campus scope</th>
                        <th>Description</th>
                        <th>Enrolled students</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($studentTypes as $type)
                        <tr>
                            <td>
                                <code>{{ $type->code }}</code>
                            </td>
                            <td>
                                <strong>{{ $type->name }}</strong>
                            </td>
                            <td>{{ $type->branch?->name ?? 'System-wide (All Campuses)' }}</td>
                            <td>{{ $type->description ?? '—' }}</td>
                            <td>
                                <strong>{{ $type->students_count }}</strong>
                            </td>
                            <td>
                                <span class="badge status-{{ $type->is_active ? 'active' : 'inactive' }}">
                                    {{ $type->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                    <a href="{{ route('student-types.edit', $type) }}" class="text-link">Edit</a>
                                    <form method="POST" action="{{ route('student-types.destroy', $type) }}" onsubmit="return confirm('Delete this student type?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-link" style="color: var(--danger); background: none; border: none; padding: 0; cursor: pointer;">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem; color: var(--muted);">
                                No student types configured yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($studentTypes->hasPages())
            <div style="margin-top: 1.5rem;">
                {{ $studentTypes->links() }}
            </div>
        @endif
    </section>
@endsection
