@extends('layouts.app')

@section('content')
    <section class="content-card">
        <div class="section-header">
            <div>
                <p class="eyebrow">User detail</p>
                <h2>{{ $user->name }}</h2>
            </div>
            @can('update', $user)
                <a href="{{ route('users.edit', $user) }}" class="primary-link">Edit user</a>
            @endcan
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <dl class="detail-list">
            <div>
                <dt>Email</dt>
                <dd>{{ $user->email }}</dd>
            </div>
            <div>
                <dt>Role</dt>
                <dd>{{ $user->role?->name ?? 'Unassigned' }}</dd>
            </div>
            <div>
                <dt>Branch</dt>
                <dd>{{ $user->branch?->name ?? 'No branch assigned' }}</dd>
            </div>
            <div>
                <dt>Status</dt>
                <dd><span class="status-pill {{ $user->status }}">{{ ucfirst($user->status) }}</span></dd>
            </div>
            <div>
                <dt>Created</dt>
                <dd>{{ $user->created_at->format('M d, Y') }}</dd>
            </div>
        </dl>
    </section>
@endsection
