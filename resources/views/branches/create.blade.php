@extends('layouts.app')

@section('content')
    <section class="content-card">
        <div class="section-header">
            <div>
                <p class="eyebrow">Branches</p>
                <h2>Create Branch</h2>
            </div>
            <a href="{{ route('branches.index') }}" class="secondary-link">Back to list</a>
        </div>

        @if ($errors->any())
            <div class="alert alert-error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('branches.store') }}" class="form-grid">
            @csrf
            <div>
                <label for="name">Branch name</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required>
            </div>
            <div>
                <label for="code">Branch code</label>
                <input id="code" type="text" name="code" value="{{ old('code') }}" required>
            </div>
            <div class="full-width">
                <label for="address">Address</label>
                <textarea id="address" name="address" rows="4">{{ old('address') }}</textarea>
            </div>
            <div>
                <label for="phone">Phone</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone') }}">
            </div>
            <div>
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}">
            </div>
            <div>
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="archived" {{ old('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                </select>
            </div>
            <div class="full-width form-actions">
                <button type="submit" class="primary-button">Create Branch</button>
            </div>
        </form>
    </section>
@endsection
