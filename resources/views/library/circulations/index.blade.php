@extends('layouts.app')

@section('title', 'Book Circulations')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Library Management</p>
            <h1>Book circulations</h1>
            <p class="intro-copy">Track loaned books, issue new loans, calculate fines, and record book returns.</p>
        </div>
        <a href="{{ route('circulations.create') }}" class="primary-link">Issue book</a>
    </section>

    <section class="content-card">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <form method="GET" action="{{ route('circulations.index') }}" class="filter-form">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by book title or member..." aria-label="Search circulations">
            <select name="status" aria-label="Filter by status">
                <option value="">All statuses</option>
                <option value="issued" @selected(request('status') === 'issued')>Currently Issued</option>
                <option value="returned" @selected(request('status') === 'returned')>Returned</option>
                <option value="overdue" @selected(request('status') === 'overdue')>Overdue Only</option>
            </select>
            <button type="submit" class="secondary-button">Apply filters</button>
        </form>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Book</th>
                        <th>Member</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Return Date</th>
                        <th>Fine ($)</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($circulations as $circ)
                        <tr>
                            <td>
                                <strong>{{ $circ->book->title }}</strong>
                                <div><small style="color:var(--text-muted, #64748b);">{{ $circ->book->isbn ?? 'No ISBN' }}</small></div>
                            </td>
                            <td>
                                <strong>{{ $circ->libraryMember->display_name }}</strong>
                                <div><small style="color:var(--text-muted, #64748b);">{{ $circ->libraryMember->member_number }} ({{ ucfirst($circ->libraryMember->member_type) }})</small></div>
                            </td>
                            <td>{{ $circ->issue_date->format('M d, Y') }}</td>
                            <td>
                                {{ $circ->due_date->format('M d, Y') }}
                                @if ($circ->status === 'issued' && $circ->isOverdue())
                                    <div style="color:var(--danger, #ef4444); font-size:0.75rem; font-weight:600;">OVERDUE</div>
                                @endif
                            </td>
                            <td>{{ $circ->return_date?->format('M d, Y') ?? '—' }}</td>
                            <td>{{ $circ->fine_amount > 0 ? '$' . number_format($circ->fine_amount, 2) : '—' }}</td>
                            <td>
                                <span class="status-pill {{ $circ->status === 'returned' ? 'active' : ($circ->isOverdue() ? 'inactive' : 'pending') }}">
                                    {{ $circ->status === 'issued' && $circ->isOverdue() ? 'Overdue' : ucfirst($circ->status) }}
                                </span>
                            </td>
                            <td>
                                @if ($circ->status === 'issued')
                                    <form method="POST" action="{{ route('circulations.return', $circ) }}" style="display:inline;" onsubmit="return confirm('Confirm book return for {{ addslashes($circ->book->title) }}?');">
                                        @csrf
                                        <input type="hidden" name="return_date" value="{{ now()->toDateString() }}">
                                        <button type="submit" class="table-link" style="background:none; border:none; cursor:pointer; font-weight:600; color:var(--primary, #0f172a); padding:0;">Mark Returned</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('circulations.destroy', $circ) }}" onsubmit="return confirm('Delete circulation record?');" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="table-link" style="background:none; border:none; cursor:pointer; color:var(--danger, #ef4444); padding:0;">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8">No circulation records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $circulations->links() }}
    </section>
@endsection
