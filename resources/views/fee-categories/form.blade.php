@extends('layouts.app')

@section('title', $feeCategory ? 'Edit Fee Category' : 'Create Fee Category')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">School finance</p>
            <h1>{{ $feeCategory ? 'Edit fee category' : 'Create fee category' }}</h1>
            <p class="intro-copy">Configure fee types and classifications for your school campuses.</p>
        </div>
        <a href="{{ route('fee-categories.index') }}" class="secondary-link">Cancel</a>
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

        <form method="POST" action="{{ $feeCategory ? route('fee-categories.update', $feeCategory) : route('fee-categories.store') }}" class="form-grid">
            @csrf
            @if ($feeCategory)
                @method('PUT')
            @endif

            @unless ($feeCategory)
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
                <label for="name">Category name</label>
                <input id="name" name="name" value="{{ old('name', $feeCategory?->name) }}" maxlength="100" placeholder="e.g. Tuition Fee or Transport" required>
            </div>

            <div>
                <label for="code">Category code</label>
                <input id="code" name="code" value="{{ old('code', $feeCategory?->code) }}" maxlength="30" placeholder="e.g. TUI or TRN" required>
            </div>

            <div>
                <label for="is_mandatory">Mandatory fee</label>
                <select id="is_mandatory" name="is_mandatory">
                    <option value="1" @selected(old('is_mandatory', $feeCategory ? ($feeCategory->is_mandatory ? '1' : '0') : '1') === '1')>Yes (Required for all enrolled students)</option>
                    <option value="0" @selected(old('is_mandatory', $feeCategory ? ($feeCategory->is_mandatory ? '1' : '0') : '1') === '0')>No (Optional / Per-student basis)</option>
                </select>
            </div>

            <div>
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="active" @selected(old('status', $feeCategory?->status ?? 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $feeCategory?->status) === 'inactive')>Inactive</option>
                </select>
            </div>

            <div class="full-width">
                <label for="description">Description (optional)</label>
                <textarea id="description" name="description" rows="3" placeholder="Brief notes about what this fee covers">{{ old('description', $feeCategory?->description) }}</textarea>
            </div>

            <div class="full-width form-actions">
                <button type="submit" class="primary-button">{{ $feeCategory ? 'Save changes' : 'Create category' }}</button>
            </div>
        </form>
    </section>
@endsection
