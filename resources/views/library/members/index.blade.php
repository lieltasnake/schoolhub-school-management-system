@extends('layouts.app')

@section('title', 'Library Members')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Library Management</p>
            <h1>Library members</h1>
            <p class="intro-copy">Register and monitor student and staff library cards and borrowing privileges.</p>
        </div>
        <a href="{{ route('library-members.create') }}" class="primary-link">Register member</a>
    </section>

    <section class="content-card">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <form method="GET" action="{{ route('library-members.index') }}" class="filter-form">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by member # or name..." aria-label="Search members">
            @if ($branches->count() > 1)
                <select name="branch_id" aria-label="Filter by campus">
                    <option value="">All campuses</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            @endif
            <select name="member_type" aria-label="Filter by type">
                <option value="">All member types</option>
                <option value="student" @selected(request('member_type') === 'student')>Student</option>
                <option value="staff" @selected(request('member_type') === 'staff')>Staff</option>
            </select>
            <select name="status" aria-label="Filter by status">
                <option value="">All statuses</option>
                @foreach (['active', 'suspended', 'expired'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <button type="submit" class="secondary-button">Apply filters</button>
        </form>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Member #</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Campus</th>
                        <th>Borrowed Books</th>
                        <th>Borrow Limit</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($members as $member)
                        <tr>
                            <td><strong>{{ $member->member_number }}</strong></td>
                            <td>{{ $member->display_name }}</td>
                            <td><span class="status-pill active">{{ ucfirst($member->member_type) }}</span></td>
                            <td>{{ $member->branch->name }}</td>
                            <td>
                                <strong>{{ $member->active_circulations_count }}</strong>
                                @if ($member->active_circulations_count >= $member->max_borrow_limit)
                                    <small style="color:var(--danger, #ef4444);">(Max reached)</small>
                                @endif
                            </td>
                            <td>{{ $member->max_borrow_limit }} books</td>
                            <td><span class="status-pill {{ $member->status }}">{{ ucfirst($member->status) }}</span></td>
                            <td>
                                <a href="{{ route('library-members.edit', $member) }}" class="table-link">Edit</a>
                                <form method="POST" action="{{ route('library-members.destroy', $member) }}" onsubmit="return confirm('Delete this member?');" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="table-link" style="background:none; border:none; cursor:pointer; color:var(--danger, #ef4444); padding:0;">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8">No library members found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $members->links() }}
    </section>
@endsection
