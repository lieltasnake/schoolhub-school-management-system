@extends('layouts.app')

@section('content')
    <section class="content-card">
        <div class="section-header">
            <div>
                <p class="eyebrow">Users</p>
                <h2>Edit User</h2>
            </div>
            <a href="{{ route('users.show', $user) }}" class="secondary-link">View user</a>
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

        <form method="POST" action="{{ route('users.update', $user) }}" class="form-grid">
            @csrf
            @method('PUT')
            <div>
                <label for="name">Full name</label>
                <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required>
            </div>
            <div>
                <label for="email">Email address</label>
                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required>
            </div>
            <div>
                <label for="role_id">Role</label>
                <select id="role_id" name="role_id" required>
                    <option value="">Select role</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="branch_id">Branch</label>
                <select id="branch_id" name="branch_id">
                    <option value="">No branch</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id', $user->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div>
                <label for="password">New password</label>
                <input id="password" type="password" name="password">
            </div>
            <div>
                <label for="password_confirmation">Confirm new password</label>
                <input id="password_confirmation" type="password" name="password_confirmation">
            </div>
            <div class="full-width form-actions">
                <button type="submit" class="primary-button">Update User</button>
            </div>
        </form>
    </section>
@endsection
