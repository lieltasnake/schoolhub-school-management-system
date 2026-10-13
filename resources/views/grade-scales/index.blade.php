@extends('layouts.app')

@section('title', 'Grade Scales')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Exams & Assessments</p>
            <h1>Grade scales & marks thresholds</h1>
            <p class="intro-copy">Configure grading rubrics, letter grades (A+, A, B, etc.), GPA grade points, and pass criteria.</p>
        </div>
        <a href="{{ route('grade-scales.create') }}" class="primary-link">New grade scale</a>
    </section>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <section class="content-card">
        <form method="GET" action="{{ route('grade-scales.index') }}" class="filter-form">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by scale name or code" aria-label="Search grade scales">

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
                <a href="{{ route('grade-scales.index') }}" class="text-link" style="align-self: center;">Reset</a>
            @endif
        </form>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Scale name</th>
                        <th>Campus</th>
                        <th>Grade tiers</th>
                        <th>Assessments</th>
                        <th>Default</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($scales as $scale)
                        <tr>
                            <td>
                                <code>{{ $scale->code }}</code>
                            </td>
                            <td>
                                <strong>{{ $scale->name }}</strong>
                                @if ($scale->description)
                                    <small style="display: block; color: var(--muted);">{{ $scale->description }}</small>
                                @endif
                            </td>
                            <td>{{ $scale->branch->name }}</td>
                            <td>
                                <div style="display: flex; gap: 0.25rem; flex-wrap: wrap;">
                                    @foreach ($scale->items as $item)
                                        <span class="badge" style="font-size: 0.75rem; background: {{ $item->is_pass ? 'var(--panel-soft)' : '#fee2e2' }}; color: {{ $item->is_pass ? 'var(--text)' : '#b91c1c' }};" title="{{ $item->min_percentage }}% - {{ $item->max_percentage }}% ({{ $item->grade_point ?? '—' }} pts)">
                                            {{ $item->letter_grade }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td>{{ $scale->assessments_count }}</td>
                            <td>
                                @if ($scale->is_default)
                                    <span class="badge status-active">Default</span>
                                @else
                                    <span style="color: var(--muted);">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge status-{{ $scale->status }}">
                                    {{ ucfirst($scale->status) }}
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                    <a href="{{ route('grade-scales.edit', $scale) }}" class="text-link">Edit</a>
                                    <form method="POST" action="{{ route('grade-scales.destroy', $scale) }}" onsubmit="return confirm('Delete this grade scale?')">
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
                                No grade scales configured yet. Click "New grade scale" to create one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($scales->hasPages())
            <div style="margin-top: 1.5rem;">
                {{ $scales->links() }}
            </div>
        @endif
    </section>
@endsection
