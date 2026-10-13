@extends('layouts.app')

@section('title', 'Student Groups')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Students</p>
            <h1>Student groups & houses</h1>
            <p class="intro-copy">Organize students into houses, clubs, transport routes, sports teams, and extracurricular activities.</p>
        </div>
        <a href="{{ route('student-groups.create') }}" class="primary-link">New student group</a>
    </section>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <section class="content-card">
        <form method="GET" action="{{ route('student-groups.index') }}" class="filter-form">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by name or code" aria-label="Search student groups">

            @if ($branches->count() > 1)
                <select name="branch_id" aria-label="Filter by campus">
                    <option value="">All campuses</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) request('branch_id', $selectedBranchId) === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            @endif

            <select name="type" aria-label="Filter by group type">
                <option value="">All group types</option>
                <option value="house" @selected(request('type') === 'house')>House / Team</option>
                <option value="club" @selected(request('type') === 'club')>Club / Society</option>
                <option value="sports" @selected(request('type') === 'sports')>Sports Team</option>
                <option value="transport" @selected(request('type') === 'transport')>Transport Route</option>
                <option value="academic" @selected(request('type') === 'academic')>Academic Cohort</option>
            </select>

            <button type="submit" class="secondary-button">Filter</button>
            @if (request()->hasAny(['search', 'branch_id', 'type']))
                <a href="{{ route('student-groups.index') }}" class="text-link" style="align-self: center;">Reset</a>
            @endif
        </form>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Group name</th>
                        <th>Campus</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Members</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($groups as $group)
                        <tr>
                            <td>
                                <code>{{ $group->code }}</code>
                            </td>
                            <td>
                                <a href="{{ route('student-groups.show', $group) }}" class="table-link" style="font-weight: 600;">
                                    {{ $group->name }}
                                </a>
                            </td>
                            <td>{{ $group->branch->name }}</td>
                            <td>
                                <span class="badge" style="background: var(--panel-soft); text-transform: capitalize;">
                                    {{ $group->type }}
                                </span>
                            </td>
                            <td>{{ $group->description ?? '—' }}</td>
                            <td>
                                <strong>{{ $group->students_count }}</strong>
                            </td>
                            <td>
                                <span class="badge status-{{ $group->is_active ? 'active' : 'inactive' }}">
                                    {{ $group->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                    <a href="{{ route('student-groups.show', $group) }}" class="text-link">Manage</a>
                                    <a href="{{ route('student-groups.edit', $group) }}" class="text-link">Edit</a>
                                    <form method="POST" action="{{ route('student-groups.destroy', $group) }}" onsubmit="return confirm('Delete this student group?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-link" style="color: var(--danger); background: none; border: none; padding: 0; cursor: pointer;">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2rem; color: var(--muted);">
                                No student groups configured yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($groups->hasPages())
            <div style="margin-top: 1.5rem;">
                {{ $groups->links() }}
            </div>
        @endif
    </section>
@endsection
