@extends('layouts.app')

@section('title', 'Issue Book')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Library Management</p>
            <h1>Issue book</h1>
            <p class="intro-copy">Loan a library book to an active member with specified return due date.</p>
        </div>
        <a href="{{ route('circulations.index') }}" class="secondary-link">Cancel</a>
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

        <form method="POST" action="{{ route('circulations.store') }}" class="form-grid">
            @csrf

            <div class="full-width">
                <label for="book_id">Select book to issue</label>
                <select id="book_id" name="book_id" required>
                    <option value="">Choose an available book</option>
                    @foreach ($books as $b)
                        <option value="{{ $b->id }}" @selected((string) old('book_id') === (string) $b->id)>
                            {{ $b->title }} &bull; By {{ $b->author }} ({{ $b->available_copies }} available &bull; {{ $b->library->name }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="full-width">
                <label for="library_member_id">Select borrowing member</label>
                <select id="library_member_id" name="library_member_id" required>
                    <option value="">Choose active member</option>
                    @foreach ($members as $m)
                        <option value="{{ $m->id }}" @selected((string) old('library_member_id') === (string) $m->id)>
                            {{ $m->member_number }} &bull; {{ $m->display_name }} ({{ ucfirst($m->member_type) }} &bull; Limit: {{ $m->max_borrow_limit }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="issue_date">Issue date</label>
                <input id="issue_date" name="issue_date" type="date" value="{{ old('issue_date', $defaultIssueDate) }}" required>
            </div>

            <div>
                <label for="due_date">Due return date</label>
                <input id="due_date" name="due_date" type="date" value="{{ old('due_date', $defaultDueDate) }}" required>
            </div>

            <div class="full-width">
                <label for="remarks">Remarks (optional)</label>
                <textarea id="remarks" name="remarks" rows="2" placeholder="Condition of book upon lending...">{{ old('remarks') }}</textarea>
            </div>

            <div class="full-width form-actions">
                <button type="submit" class="primary-button">Confirm book issue</button>
            </div>
        </form>
    </section>
@endsection
