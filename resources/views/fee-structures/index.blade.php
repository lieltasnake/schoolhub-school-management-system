@extends('layouts.app')

@section('title', 'Fee Structures')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">School finance</p>
            <h1>Fee structures</h1>
            <p class="intro-copy">Define pricing, grade-level allocations, and term schedules for each fee category.</p>
        </div>
        @can('create', App\Models\FeeStructure::class)
            <a href="{{ route('fee-structures.create') }}" class="primary-link">Add fee structure</a>
        @endcan
    </section>

    <section class="content-card">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <form method="GET" action="{{ route('fee-structures.index') }}" class="filter-form">
            @if ($branches->count() > 1)
                <select name="branch_id" aria-label="Filter by campus" onchange="this.form.submit()">
                    <option value="">All campuses</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            @endif

            @if ($feeCategories->isNotEmpty())
                <select name="fee_category_id" aria-label="Filter by fee category">
                    <option value="">All categories</option>
                    @foreach ($feeCategories as $category)
                        <option value="{{ $category->id }}" @selected((string) request('fee_category_id') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            @endif

            @if ($grades->isNotEmpty())
                <select name="grade_id" aria-label="Filter by grade">
                    <option value="">All grades</option>
                    @foreach ($grades as $grade)
                        <option value="{{ $grade->id }}" @selected((string) request('grade_id') === (string) $grade->id)>{{ $grade->name }}</option>
                    @endforeach
                </select>
            @endif

            @if ($academicYears->isNotEmpty())
                <select name="academic_year_id" aria-label="Filter by academic year">
                    <option value="">All academic years</option>
                    @foreach ($academicYears as $year)
                        <option value="{{ $year->id }}" @selected((string) request('academic_year_id') === (string) $year->id)>{{ $year->name }}</option>
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
                        <th>Category</th>
                        <th>Grade</th>
                        <th>Academic Year</th>
                        <th>Term / Semester</th>
                        <th>Amount</th>
                        <th>Campus</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($feeStructures as $structure)
                        <tr>
                            <td>
                                <strong>{{ $structure->feeCategory->name }}</strong>
                                <small class="meta-line"><code>{{ $structure->feeCategory->code }}</code></small>
                            </td>
                            <td>{{ $structure->grade->name }}</td>
                            <td>{{ $structure->academicYear->name }}</td>
                            <td>
                                @if ($structure->term)
                                    {{ $structure->term->name }}
                                @else
                                    <span class="meta-line">Full Academic Year</span>
                                @endif
                            </td>
                            <td><strong>${{ number_format((float) $structure->amount, 2) }}</strong></td>
                            <td>{{ $structure->branch->name }}</td>
                            <td><span class="status-pill {{ $structure->status }}">{{ ucfirst($structure->status) }}</span></td>
                            <td>
                                <a href="{{ route('fee-structures.show', $structure) }}" class="table-link">View</a>
                                @can('update', $structure)
                                    <a href="{{ route('fee-structures.edit', $structure) }}" class="table-link">Edit</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8">No fee structures configured for the selected scope.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $feeStructures->links() }}
    </section>
@endsection
