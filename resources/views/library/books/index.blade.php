@extends('layouts.app')

@section('title', 'Books Catalog')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Library Management</p>
            <h1>Books catalog</h1>
            <p class="intro-copy">Catalog of library books, ISBN barcodes, shelf locations, and lending copies.</p>
        </div>
        <a href="{{ route('books.create') }}" class="primary-link">Add book</a>
    </section>

    <section class="content-card">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <form method="GET" action="{{ route('books.index') }}" class="filter-form">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by title, author, or ISBN..." aria-label="Search books">
            <select name="library_id" aria-label="Filter by library">
                <option value="">All libraries</option>
                @foreach ($libraries as $lib)
                    <option value="{{ $lib->id }}" @selected((string) request('library_id') === (string) $lib->id)>{{ $lib->name }}</option>
                @endforeach
            </select>
            <select name="book_type_id" aria-label="Filter by category">
                <option value="">All categories</option>
                @foreach ($bookTypes as $bt)
                    <option value="{{ $bt->id }}" @selected((string) request('book_type_id') === (string) $bt->id)>{{ $bt->name }}</option>
                @endforeach
            </select>
            <select name="status" aria-label="Filter by status">
                <option value="">All statuses</option>
                @foreach (['available', 'borrowed', 'reserved', 'lost', 'damaged'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <button type="submit" class="secondary-button">Apply filters</button>
        </form>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title & Author</th>
                        <th>ISBN</th>
                        <th>Library</th>
                        <th>Category</th>
                        <th>Shelf</th>
                        <th>Copies (Avail / Total)</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($books as $book)
                        <tr>
                            <td>
                                <strong>{{ $book->title }}</strong>
                                <div><small style="color:var(--text-muted, #64748b);">By {{ $book->author }}</small></div>
                            </td>
                            <td><code>{{ $book->isbn ?? '—' }}</code></td>
                            <td>{{ $book->library->name }}</td>
                            <td>{{ $book->bookType->name }}</td>
                            <td>{{ $book->shelf_location ?? '—' }}</td>
                            <td>
                                <strong>{{ $book->available_copies }}</strong> / {{ $book->total_copies }}
                            </td>
                            <td>
                                @php
                                    $pillClass = match($book->status) {
                                        'available' => 'active',
                                        'borrowed' => 'pending',
                                        'lost', 'damaged' => 'inactive',
                                        default => 'active',
                                    };
                                @endphp
                                <span class="status-pill {{ $pillClass }}">{{ ucfirst($book->status) }}</span>
                            </td>
                            <td>
                                <a href="{{ route('books.show', $book) }}" class="table-link">View</a>
                                <a href="{{ route('books.edit', $book) }}" class="table-link">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8">No books found in catalog.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $books->links() }}
    </section>
@endsection
