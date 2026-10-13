@extends('layouts.app')

@section('title', 'Fee Categories')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">School finance</p>
            <h1>Fee categories</h1>
            <p class="intro-copy">Define fee classifications such as tuition, transport, uniforms, and laboratory fees across your campuses.</p>
        </div>
        @can('create', App\Models\FeeCategory::class)
            <a href="{{ route('fee-categories.create') }}" class="primary-link">Add fee category</a>
        @endcan
    </section>

    <section class="content-card">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <form method="GET" action="{{ route('fee-categories.index') }}" class="filter-form">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by name or code" aria-label="Search fee categories">
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
                        <th>Category</th>
                        <th>Code</th>
                        <th>Campus</th>
                        <th>Mandatory</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($feeCategories as $category)
                        <tr>
                            <td>
                                <strong>{{ $category->name }}</strong>
                                @if ($category->description)
                                    <small class="meta-line">{{ Str::limit($category->description, 50) }}</small>
                                @endif
                            </td>
                            <td><code>{{ $category->code }}</code></td>
                            <td>{{ $category->branch->name }}</td>
                            <td>
                                @if ($category->is_mandatory)
                                    <span class="status-pill active">Yes</span>
                                @else
                                    <span class="status-pill inactive">Optional</span>
                                @endif
                            </td>
                            <td><span class="status-pill {{ $category->status }}">{{ ucfirst($category->status) }}</span></td>
                            <td>
                                @can('update', $category)
                                    <a href="{{ route('fee-categories.edit', $category) }}" class="table-link">Edit</a>
                                @endcan
                                @can('delete', $category)
                                    <form method="POST" action="{{ route('fee-categories.destroy', $category) }}" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this fee category?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="table-link text-danger" style="background:none;border:none;padding:0;cursor:pointer;">Delete</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No fee categories found for the selected scope.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $feeCategories->links() }}
    </section>
@endsection
