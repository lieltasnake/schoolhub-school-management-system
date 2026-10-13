@extends('layouts.app')

@section('title', 'Libraries')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Library Management</p>
            <h1>Libraries</h1>
            <p class="intro-copy">Manage campus libraries, physical facilities, and book collections.</p>
        </div>
        <a href="{{ route('libraries.create') }}" class="primary-link">Add library</a>
    </section>

    <section class="content-card">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <form method="GET" action="{{ route('libraries.index') }}" class="filter-form">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by library name or location..." aria-label="Search libraries">
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
                        <th>Library Name</th>
                        <th>Campus</th>
                        <th>Location</th>
                        <th>Contact Email</th>
                        <th>Total Titles</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($libraries as $lib)
                        <tr>
                            <td><strong>{{ $lib->name }}</strong></td>
                            <td>{{ $lib->branch->name }}</td>
                            <td>{{ $lib->location ?? '—' }}</td>
                            <td>{{ $lib->contact_email ?? '—' }}</td>
                            <td>{{ $lib->books_count }}</td>
                            <td><span class="status-pill {{ $lib->status }}">{{ ucfirst($lib->status) }}</span></td>
                            <td>
                                <a href="{{ route('libraries.edit', $lib) }}" class="table-link">Edit</a>
                                <form method="POST" action="{{ route('libraries.destroy', $lib) }}" onsubmit="return confirm('Delete this library?');" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="table-link" style="background:none; border:none; cursor:pointer; color:var(--danger, #ef4444); padding:0;">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No libraries found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $libraries->links() }}
    </section>
@endsection
