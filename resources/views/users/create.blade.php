@extends('layouts.app')

@section('content')
    <section class="content-card">
        <div class="section-header">
            <div>
                <p class="eyebrow">Users</p>
                <h2>Create User</h2>
            </div>
            <a href="{{ route('users.index') }}" class="secondary-link">Back to list</a>
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

        <form method="POST" action="{{ route('users.store') }}" class="form-grid">
            @csrf
            <div>
                <label for="name">Full name</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required>
            </div>
            <div>
                <label for="email">Email address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required>
            </div>
            <div>
                <label for="role_id">Role</label>
                <select id="role_id" name="role_id" required>
                    <option value="">Select role</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="branch_id">Branch</label>
                <select id="branch_id" name="branch_id">
                    <option value="">No branch</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div>
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required>
            </div>
            <div>
                <label for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required>
            </div>
            <div class="full-width form-actions">
                <button type="submit" class="primary-button">Create User</button>
            </div>
        </form>
    </section>
@endsection
