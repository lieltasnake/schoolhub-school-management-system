@extends('layouts.app')

@section('content')
    <section class="content-card">
        <div class="section-header">
            <div>
                <p class="eyebrow">Administration</p>
                <h2>Branches</h2>
            </div>
            @can('create', App\Models\Branch::class)
                <a href="{{ route('branches.create') }}" class="primary-link">Add Branch</a>
            @endcan
        </div>

        <form method="GET" action="{{ route('branches.index') }}" class="filter-form">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or code">
            <select name="status">
                <option value="">All statuses</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
            </select>
            <button type="submit" class="secondary-button">Filter</button>
        </form>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Status</th>
                        <th>Contact</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($branches as $branch)
                        <tr>
                            <td>{{ $branch->name }}</td>
                            <td>{{ $branch->code }}</td>
                            <td><span class="status-pill {{ $branch->status }}">{{ ucfirst($branch->status) }}</span></td>
                            <td>{{ $branch->phone ?: $branch->email }}</td>
                            <td>
                                <a href="{{ route('branches.show', $branch) }}" class="table-link">View</a>
                                @can('update', $branch)
                                    <a href="{{ route('branches.edit', $branch) }}" class="table-link">Edit</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">No branches found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $branches->links() }}
    </section>
@endsection
