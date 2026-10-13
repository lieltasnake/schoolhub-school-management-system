@extends('layouts.app')

@section('title', 'Assessment Types')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Exams & Assessments</p>
            <h1>Assessment types</h1>
            <p class="intro-copy">Define examination and assessment categories (e.g. Quizzes, Midterms, Final Exams, Projects).</p>
        </div>
        <a href="{{ route('assessment-types.create') }}" class="primary-link">New assessment type</a>
    </section>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <section class="content-card">
        <form method="GET" action="{{ route('assessment-types.index') }}" class="filter-form">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by name or code" aria-label="Search assessment types">

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
                <a href="{{ route('assessment-types.index') }}" class="text-link" style="align-self: center;">Reset</a>
            @endif
        </form>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Type name</th>
                        <th>Campus</th>
                        <th>Default weight</th>
                        <th>Description</th>
                        <th>Assessments using</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($types as $type)
                        <tr>
                            <td>
                                <code>{{ $type->code }}</code>
                            </td>
                            <td>
                                <strong>{{ $type->name }}</strong>
                            </td>
                            <td>{{ $type->branch->name }}</td>
                            <td>{{ number_format($type->weight_percentage, 1) }}%</td>
                            <td>{{ $type->description ?? '—' }}</td>
                            <td>
                                <strong>{{ $type->assessments_count }}</strong>
                            </td>
                            <td>
                                <span class="badge status-{{ $type->is_active ? 'active' : 'inactive' }}">
                                    {{ $type->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                    <a href="{{ route('assessment-types.edit', $type) }}" class="text-link">Edit</a>
                                    <form method="POST" action="{{ route('assessment-types.destroy', $type) }}" onsubmit="return confirm('Delete this assessment type?')">
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
                                No assessment types configured yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($types->hasPages())
            <div style="margin-top: 1.5rem;">
                {{ $types->links() }}
            </div>
        @endif
    </section>
@endsection
