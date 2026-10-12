@extends('layouts.app')

@section('content')
    <section class="content-card">
        <div class="section-header">
            <div>
                <p class="eyebrow">Administration</p>
                <h2>Users</h2>
            </div>
            @can('create', App\Models\User::class)
                <a href="{{ route('users.create') }}" class="primary-link">Add User</a>
            @endcan
        </div>

        <form method="GET" action="{{ route('users.index') }}" class="filter-form">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by name or email" aria-label="Search users">
            <select name="role_id" aria-label="Filter by role">
                <option value="">All roles</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}" @selected((string) request('role_id') === (string) $role->id)>{{ $role->name }}</option>
                @endforeach
            </select>
            @if ($canFilterByBranch)
                <select name="branch_id" aria-label="Filter by branch">
                    <option value="">All branches</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            @endif
            <select name="status" aria-label="Filter by status">
                <option value="">All statuses</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
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
                        <th>Email</th>
                        <th>Role</th>
                        <th>Branch</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $userItem)
                        <tr>
                            <td>{{ $userItem->name }}</td>
                            <td>{{ $userItem->email }}</td>
                            <td>{{ $userItem->role?->name ?? 'Unassigned' }}</td>
                            <td>{{ $userItem->branch?->name ?? 'N/A' }}</td>
                            <td><span class="status-pill {{ $userItem->status }}">{{ ucfirst($userItem->status) }}</span></td>
                            <td>
                                <a href="{{ route('users.show', $userItem) }}" class="table-link">View</a>
                                @can('update', $userItem)
                                    <a href="{{ route('users.edit', $userItem) }}" class="table-link">Edit</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $users->links() }}
    </section>
@endsection
