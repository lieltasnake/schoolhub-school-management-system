@extends('layouts.app')

@section('title', $bookType ? 'Edit Book Type' : 'Create Book Type')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Library Management</p>
            <h1>{{ $bookType ? 'Edit book type' : 'Create book type' }}</h1>
            <p class="intro-copy">Configure book classification category and abbreviation code.</p>
        </div>
        <a href="{{ route('book-types.index') }}" class="secondary-link">Cancel</a>
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

        <form method="POST" action="{{ $bookType ? route('book-types.update', $bookType) : route('book-types.store') }}" class="form-grid">
            @csrf
            @if ($bookType)
                @method('PUT')
            @endif

            <div>
                <label for="name">Category name</label>
                <input id="name" name="name" value="{{ old('name', $bookType?->name) }}" maxlength="255" placeholder="e.g. Science Fiction, Reference, Textbook" required>
            </div>

            <div>
                <label for="code">Category code</label>
                <input id="code" name="code" value="{{ old('code', $bookType?->code) }}" maxlength="50" placeholder="e.g. SCI-FI, REF, TXT" required>
            </div>

            <div class="full-width">
                <label for="description">Description (optional)</label>
                <textarea id="description" name="description" rows="3" placeholder="Category description or Dewey decimal group...">{{ old('description', $bookType?->description) }}</textarea>
            </div>

            <div class="full-width form-actions">
                <button type="submit" class="primary-button">{{ $bookType ? 'Update book type' : 'Create book type' }}</button>
            </div>
        </form>
    </section>
@endsection
