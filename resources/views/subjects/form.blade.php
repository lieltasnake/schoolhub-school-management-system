@extends('layouts.app')

@section('title', $subject ? 'Edit Subject' : 'Create Subject')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Curriculum</p>
            <h1>{{ $subject ? 'Edit subject' : 'Create subject' }}</h1>
            <p class="intro-copy">Define course subject details, codes, departments, and credit hours.</p>
        </div>
        <a href="{{ route('subjects.index') }}" class="secondary-link">Cancel</a>
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

        <form method="POST" action="{{ $subject ? route('subjects.update', $subject) : route('subjects.store') }}" class="form-grid">
            @csrf
            @if ($subject)
                @method('PUT')
            @endif

            <div class="full-width">
                <label for="branch_id">Campus</label>
                <select id="branch_id" name="branch_id" required>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) old('branch_id', $subject?->branch_id) === (string) $branch->id)>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="name">Subject name</label>
                <input id="name" name="name" value="{{ old('name', $subject?->name) }}" maxlength="255" placeholder="e.g. Mathematics, English Literature" required>
            </div>

            <div>
                <label for="code">Subject code</label>
                <input id="code" name="code" value="{{ old('code', $subject?->code) }}" maxlength="50" placeholder="e.g. MATH101, ENG201" required>
            </div>

            <div>
                <label for="department">Department (optional)</label>
                <input id="department" name="department" value="{{ old('department', $subject?->department) }}" maxlength="255" placeholder="e.g. Science, Humanities">
            </div>

            <div>
                <label for="credit_hours">Credit hours (optional)</label>
                <input id="credit_hours" name="credit_hours" type="number" step="0.5" min="0" max="20" value="{{ old('credit_hours', $subject?->credit_hours) }}" placeholder="e.g. 3.0">
            </div>

            <div>
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    @foreach (['active', 'inactive'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $subject?->status ?? 'active') === $status)>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="full-width form-actions">
                <button type="submit" class="primary-button">{{ $subject ? 'Update subject' : 'Create subject' }}</button>
            </div>
        </form>
    </section>
@endsection
