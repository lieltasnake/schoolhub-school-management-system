@extends('layouts.app')

@section('title', 'Book Types / Categories')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Library Management</p>
            <h1>Book types & categories</h1>
            <p class="intro-copy">Classify library books by genre, medium, and classification codes.</p>
        </div>
        <a href="{{ route('book-types.create') }}" class="primary-link">Add book type</a>
    </section>

    <section class="content-card">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <form method="GET" action="{{ route('book-types.index') }}" class="filter-form">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by name or code..." aria-label="Search book types">
            <button type="submit" class="secondary-button">Search</button>
        </form>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Category Name</th>
                        <th>Code</th>
                        <th>Description</th>
                        <th>Books in Catalog</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bookTypes as $type)
                        <tr>
                            <td><strong>{{ $type->name }}</strong></td>
                            <td><code>{{ $type->code }}</code></td>
                            <td>{{ $type->description ?? '—' }}</td>
                            <td>{{ $type->books_count }}</td>
                            <td>
                                <a href="{{ route('book-types.edit', $type) }}" class="table-link">Edit</a>
                                <form method="POST" action="{{ route('book-types.destroy', $type) }}" onsubmit="return confirm('Delete this book category?');" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="table-link" style="background:none; border:none; cursor:pointer; color:var(--danger, #ef4444); padding:0;">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5">No book types configured.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $bookTypes->links() }}
    </section>
@endsection
