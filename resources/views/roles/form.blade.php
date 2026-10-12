@extends('layouts.app')

@section('title', $role ? 'Edit Custom Role' : 'Create Custom Role')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">People and access / Roles</p>
            <h1>{{ $role ? 'Edit custom role' : 'Create custom role' }}</h1>
            <p class="intro-copy">High-risk global permissions and built-in roles cannot be delegated here.</p>
        </div>
        <a href="{{ route('roles.index') }}" class="secondary-link">Back to roles</a>
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

        <form method="POST" action="{{ $role ? route('roles.update', $role) : route('roles.store') }}" class="form-grid">
            @csrf
            @if ($role)
                @method('PUT')
            @endif

            <div>
                <label for="name">Role name</label>
                <input id="name" name="name" value="{{ old('name', $role?->name) }}" maxlength="191" required>
            </div>
            <div>
                <label for="slug">Role key</label>
                <input id="slug" name="slug" value="{{ old('slug', $role?->slug) }}" pattern="[a-z0-9]+(-[a-z0-9]+)*" maxlength="191" required>
                <small class="field-note">Use lowercase letters, numbers, and hyphens.</small>
            </div>
            <div class="full-width">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="2" maxlength="191">{{ old('description', $role?->description) }}</textarea>
            </div>

            <fieldset class="full-width permission-fieldset">
                <legend>Permissions</legend>
                <div class="permission-grid">
                    @foreach ($permissions as $permissionKey => $label)
                        <label class="permission-option">
                            <input type="checkbox" name="permission_keys[]" value="{{ $permissionKey }}" @checked(in_array($permissionKey, old('permission_keys', $role?->permissions() ?? []), true))>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <div class="full-width form-actions">
                <button type="submit" class="primary-button">{{ $role ? 'Save custom role' : 'Create custom role' }}</button>
            </div>
        </form>
    </section>
@endsection