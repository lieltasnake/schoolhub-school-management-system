@extends('layouts.app')

@section('title', 'Grade Promotion')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Students</p>
            <h1>Grade & academic year promotion</h1>
            <p class="intro-copy">Promote classes to the next grade level, retain students, or graduate completing cohorts.</p>
        </div>
        <a href="{{ route('grade-promotions.create') }}" class="primary-link">Run promotion wizard</a>
    </section>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <section class="content-card">
        @if ($branches->count() > 1)
            <form method="GET" action="{{ route('grade-promotions.index') }}" class="filter-form">
                <select name="branch_id" aria-label="Filter by campus">
                    <option value="">All campuses</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) request('branch_id', $selectedBranchId) === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="secondary-button">Filter</button>
            </form>
        @endif

        <h2 style="font-size: 1.15rem; margin-bottom: 1rem;">Promotion History Log</h2>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date executed</th>
                        <th>Campus</th>
                        <th>Source session & grade</th>
                        <th>Target session & grade</th>
                        <th>Total students</th>
                        <th>Promoted</th>
                        <th>Repeated</th>
                        <th>Graduated</th>
                        <th>Processed by</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($promotions as $promotion)
                        <tr>
                            <td>
                                <strong>{{ $promotion->created_at->format('M d, Y H:i') }}</strong>
                            </td>
                            <td>{{ $promotion->branch->name }}</td>
                            <td>
                                <strong>{{ $promotion->fromGrade->name }}</strong>
                                <small style="display: block; color: var(--muted);">{{ $promotion->fromAcademicYear->name }}</small>
                            </td>
                            <td>
                                <strong>{{ $promotion->toGrade->name }}</strong>
                                <small style="display: block; color: var(--muted);">{{ $promotion->toAcademicYear->name }}</small>
                            </td>
                            <td>
                                <strong>{{ $promotion->total_students }}</strong>
                            </td>
                            <td>
                                <span class="badge status-active">{{ $promotion->promoted_count }}</span>
                            </td>
                            <td>
                                <span class="badge" style="background: #fed7aa; color: #9a3412;">{{ $promotion->repeated_count }}</span>
                            </td>
                            <td>
                                <span class="badge" style="background: #e0e7ff; color: #3730a3;">{{ $promotion->graduated_count }}</span>
                            </td>
                            <td>{{ $promotion->processor->name }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 2rem; color: var(--muted);">
                                No promotion batches executed yet. Click "Run promotion wizard" to promote students between academic years.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($promotions->hasPages())
            <div style="margin-top: 1.5rem;">
                {{ $promotions->links() }}
            </div>
        @endif
    </section>
@endsection
