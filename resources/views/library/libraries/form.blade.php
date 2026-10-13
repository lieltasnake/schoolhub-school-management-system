@extends('layouts.app')

@section('title', $library ? 'Edit Library' : 'Create Library')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Library Management</p>
            <h1>{{ $library ? 'Edit library' : 'Create library' }}</h1>
            <p class="intro-copy">Set up school library locations, contact details, and branches.</p>
        </div>
        <a href="{{ route('libraries.index') }}" class="secondary-link">Cancel</a>
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

        <form method="POST" action="{{ $library ? route('libraries.update', $library) : route('libraries.store') }}" class="form-grid">
            @csrf
            @if ($library)
                @method('PUT')
            @endif

            <div class="full-width">
                <label for="branch_id">Campus</label>
                <select id="branch_id" name="branch_id" required>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) old('branch_id', $library?->branch_id) === (string) $branch->id)>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="name">Library name</label>
                <input id="name" name="name" value="{{ old('name', $library?->name) }}" maxlength="255" placeholder="e.g. Main Campus Library, Junior Library" required>
            </div>

            <div>
                <label for="location">Location / Building</label>
                <input id="location" name="location" value="{{ old('location', $library?->location) }}" maxlength="255" placeholder="e.g. Block B, 2nd Floor">
            </div>

            <div>
                <label for="contact_email">Contact email (optional)</label>
                <input id="contact_email" name="contact_email" type="email" value="{{ old('contact_email', $library?->contact_email) }}" maxlength="255" placeholder="library@school.edu">
            </div>

            <div>
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    @foreach (['active', 'inactive'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $library?->status ?? 'active') === $status)>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="full-width form-actions">
                <button type="submit" class="primary-button">{{ $library ? 'Update library' : 'Create library' }}</button>
            </div>
        </form>
    </section>
@endsection
