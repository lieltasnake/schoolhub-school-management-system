@extends('layouts.app')

@section('title', $grade ? 'Edit Grade' : 'Create Grade')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">School setup</p>
            <h1>{{ $grade ? 'Edit grade' : 'Create grade' }}</h1>
            <p class="intro-copy">Configure grade levels and classifications for your school campuses.</p>
        </div>
        <a href="{{ $grade ? route('grades.show', $grade) : route('grades.index') }}" class="secondary-link">Cancel</a>
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

        <form method="POST" action="{{ $grade ? route('grades.update', $grade) : route('grades.store') }}" class="form-grid">
            @csrf
            @if ($grade)
                @method('PUT')
            @endif

            @unless ($grade)
                <div class="full-width">
                    <label for="branch_id">Campus</label>
                    <select id="branch_id" name="branch_id" required>
                        <option value="">Choose campus</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected((string) old('branch_id', request('branch_id')) === (string) $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endunless

            <div>
                <label for="name">Grade name</label>
                <input id="name" name="name" value="{{ old('name', $grade?->name) }}" maxlength="100" placeholder="e.g. Grade 1 or Kindergarten" required>
            </div>

            <div>
                <label for="code">Grade code</label>
                <input id="code" name="code" value="{{ old('code', $grade?->code) }}" maxlength="30" placeholder="e.g. G1 or KG" required>
            </div>

            <div>
                <label for="level">Level / Order</label>
                <input id="level" type="number" name="level" value="{{ old('level', $grade?->level ?? 1) }}" min="1" max="100" placeholder="e.g. 1" required>
            </div>

            <div>
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="active" @selected(old('status', $grade?->status ?? 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $grade?->status) === 'inactive')>Inactive</option>
                </select>
            </div>

            <div class="full-width">
                <label for="description">Description (optional)</label>
                <textarea id="description" name="description" rows="3" placeholder="Brief notes about this grade level">{{ old('description', $grade?->description) }}</textarea>
            </div>

            <div class="full-width form-actions">
                <button type="submit" class="primary-button">{{ $grade ? 'Save changes' : 'Create grade' }}</button>
            </div>
        </form>
    </section>
@endsection
