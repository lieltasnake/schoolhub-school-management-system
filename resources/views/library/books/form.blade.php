@extends('layouts.app')

@section('title', $book ? 'Edit Book' : 'Add Book')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Library Management</p>
            <h1>{{ $book ? 'Edit book' : 'Add book to catalog' }}</h1>
            <p class="intro-copy">Enter book bibliographic details, ISBN, shelf location, and copy stock.</p>
        </div>
        <a href="{{ $book ? route('books.show', $book) : route('books.index') }}" class="secondary-link">Cancel</a>
    </section>

    <section class="content-card">
        @if ($errors->any())
            <div class="alert alert-error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $book ? route('books.update', $book) : route('books.store') }}" class="form-grid">
            @csrf
            @if ($book)
                @method('PUT')
            @endif

            <div>
                <label for="library_id">Library</label>
                <select id="library_id" name="library_id" required>
                    @foreach ($libraries as $lib)
                        <option value="{{ $lib->id }}" @selected((string) old('library_id', $book?->library_id) === (string) $lib->id)>
                            {{ $lib->name }} ({{ $lib->branch->name }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="book_type_id">Category / Genre</label>
                <select id="book_type_id" name="book_type_id" required>
                    @foreach ($bookTypes as $bt)
                        <option value="{{ $bt->id }}" @selected((string) old('book_type_id', $book?->book_type_id) === (string) $bt->id)>
                            {{ $bt->name }} ({{ $bt->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="full-width">
                <label for="title">Book title</label>
                <input id="title" name="title" value="{{ old('title', $book?->title) }}" maxlength="255" placeholder="e.g. To Kill a Mockingbird" required>
            </div>

            <div>
                <label for="author">Author(s)</label>
                <input id="author" name="author" value="{{ old('author', $book?->author) }}" maxlength="255" placeholder="e.g. Harper Lee" required>
            </div>

            <div>
                <label for="isbn">ISBN (optional)</label>
                <input id="isbn" name="isbn" value="{{ old('isbn', $book?->isbn) }}" maxlength="50" placeholder="e.g. 978-0061120084">
            </div>

            <div>
                <label for="publisher">Publisher (optional)</label>
                <input id="publisher" name="publisher" value="{{ old('publisher', $book?->publisher) }}" maxlength="255" placeholder="e.g. Penguin Books">
            </div>

            <div>
                <label for="edition">Edition (optional)</label>
                <input id="edition" name="edition" value="{{ old('edition', $book?->edition) }}" maxlength="50" placeholder="e.g. 3rd Edition, Revised">
            </div>

            <div>
                <label for="shelf_location">Shelf location (optional)</label>
                <input id="shelf_location" name="shelf_location" value="{{ old('shelf_location', $book?->shelf_location) }}" maxlength="100" placeholder="e.g. Shelf 4B, Row 2">
            </div>

            <div>
                <label for="total_copies">Total copies</label>
                <input id="total_copies" name="total_copies" type="number" min="1" max="1000" value="{{ old('total_copies', $book?->total_copies ?? 1) }}" required>
            </div>

            <div>
                <label for="available_copies">Available copies</label>
                <input id="available_copies" name="available_copies" type="number" min="0" max="1000" value="{{ old('available_copies', $book?->available_copies ?? 1) }}" required>
            </div>

            <div>
                <label for="status">Catalog status</label>
                <select id="status" name="status" required>
                    @foreach (['available', 'borrowed', 'reserved', 'lost', 'damaged'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $book?->status ?? 'available') === $status)>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="full-width form-actions">
                <button type="submit" class="primary-button">{{ $book ? 'Update book' : 'Add book' }}</button>
            </div>
        </form>
    </section>
@endsection
