@extends('layouts.app')

@section('title', 'Book Details — ' . $book->title)

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Library Management</p>
            <h1>{{ $book->title }}</h1>
            <p class="intro-copy">By {{ $book->author }} &bull; {{ $book->library->name }} &bull; {{ $book->bookType->name }}</p>
        </div>
        <div style="display:flex; gap:0.5rem;">
            <a href="{{ route('books.edit', $book) }}" class="primary-link">Edit book</a>
            <a href="{{ route('books.index') }}" class="secondary-link">Back to catalog</a>
        </div>
    </section>

    <div style="display:grid; grid-template-columns: 1fr 2fr; gap:1.5rem; align-items:start;">
        <section class="content-card">
            <h2>Book Information</h2>
            <dl style="display:grid; grid-template-columns: 1fr 1fr; gap: 0.75rem 1rem; margin-top: 1rem; font-size: 0.95rem;">
                <dt style="color:var(--text-muted, #64748b);">ISBN</dt>
                <dd><code>{{ $book->isbn ?? '—' }}</code></dd>

                <dt style="color:var(--text-muted, #64748b);">Publisher</dt>
                <dd>{{ $book->publisher ?? '—' }}</dd>

                <dt style="color:var(--text-muted, #64748b);">Edition</dt>
                <dd>{{ $book->edition ?? '—' }}</dd>

                <dt style="color:var(--text-muted, #64748b);">Shelf</dt>
                <dd>{{ $book->shelf_location ?? '—' }}</dd>

                <dt style="color:var(--text-muted, #64748b);">Total copies</dt>
                <dd>{{ $book->total_copies }}</dd>

                <dt style="color:var(--text-muted, #64748b);">Available</dt>
                <dd><strong>{{ $book->available_copies }}</strong></dd>

                <dt style="color:var(--text-muted, #64748b);">Status</dt>
                <dd><span class="status-pill {{ $book->status === 'available' ? 'active' : 'pending' }}">{{ ucfirst($book->status) }}</span></dd>
            </dl>

            <form method="POST" action="{{ route('books.destroy', $book) }}" style="margin-top:2rem;" onsubmit="return confirm('Delete this book from catalog?');">
                @csrf
                @method('DELETE')
                <button type="submit" style="color:var(--danger, #ef4444); background:none; border:none; cursor:pointer; font-weight:600; padding:0;">Delete book</button>
            </form>
        </section>

        <section class="content-card">
            <h2>Circulation & Borrow History</h2>
            <div class="table-wrap" style="margin-top:1rem;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Issue Date</th>
                            <th>Due Date</th>
                            <th>Return Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentCirculations as $circ)
                            <tr>
                                <td>
                                    <strong>{{ $circ->libraryMember->display_name }}</strong>
                                    <div><small style="color:var(--text-muted, #64748b);">{{ $circ->libraryMember->member_number }}</small></div>
                                </td>
                                <td>{{ $circ->issue_date->format('M d, Y') }}</td>
                                <td>{{ $circ->due_date->format('M d, Y') }}</td>
                                <td>{{ $circ->return_date?->format('M d, Y') ?? '—' }}</td>
                                <td>
                                    <span class="status-pill {{ $circ->status === 'returned' ? 'active' : ($circ->isOverdue() ? 'inactive' : 'pending') }}">
                                        {{ $circ->status === 'issued' && $circ->isOverdue() ? 'Overdue' : ucfirst($circ->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5">No circulation records for this book.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
