@extends('layouts.app')

@section('title', 'Roles and Permissions')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">People and access</p>
            <h1>Roles and permissions</h1>
            <p class="intro-copy">System roles are protected. Custom permissions remain limited to the selected user's branch scope.</p>
        </div>
        <a href="{{ route('roles.create') }}" class="primary-link">Create custom role</a>
    </section>

    <section class="content-card">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="GET" action="{{ route('roles.index') }}" class="filter-form">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by role name or key" aria-label="Search roles">
            <button type="submit" class="secondary-button">Search</button>
        </form>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr><th>Role</th><th>Key</th><th>Type</th><th>Permissions</th><th>Assigned users</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr>
                            <td>
                                <strong>{{ $role->name }}</strong>
                                @if ($role->description)<br><small>{{ $role->description }}</small>@endif
                            </td>
                            <td>{{ $role->slug }}</td>
                            <td>
                                <span class="status-pill {{ $role->isSystemRole() ? 'completed' : 'active' }}">
                                    {{ $role->isSystemRole() ? 'System' : 'Custom' }}
                                </span>
                            </td>
                            <td>{{ count($role->permissions()) }}</td>
                            <td>{{ $role->users_count }}</td>
                            <td>
                                @unless ($role->isSystemRole())
                                    <a href="{{ route('roles.edit', $role) }}" class="table-link">Edit</a>
                                    @if ($role->users_count === 0)
                                        <form method="POST" action="{{ route('roles.destroy', $role) }}" class="inline-action" onsubmit="return confirm('Delete the unused custom role {{ $role->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="table-link danger-link">Delete</button>
                                        </form>
                                    @endif
                                @else
                                    <span class="muted-text">Protected</span>
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No roles match this search.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $roles->links() }}
    </section>
@endsection