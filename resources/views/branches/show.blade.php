@extends('layouts.app')

@section('content')
    <section class="content-card">
        <div class="section-header">
            <div>
                <p class="eyebrow">Branch detail</p>
                <h2>{{ $branch->name }}</h2>
            </div>
            @can('update', $branch)
                <a href="{{ route('branches.edit', $branch) }}" class="primary-link">Edit branch</a>
            @endcan
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <dl class="detail-list">
            <div>
                <dt>Code</dt>
                <dd>{{ $branch->code }}</dd>
            </div>
            <div>
                <dt>Status</dt>
                <dd><span class="status-pill {{ $branch->status }}">{{ ucfirst($branch->status) }}</span></dd>
            </div>
            <div>
                <dt>Phone</dt>
                <dd>{{ $branch->phone ?: 'Not provided' }}</dd>
            </div>
            <div>
                <dt>Email</dt>
                <dd>{{ $branch->email ?: 'Not provided' }}</dd>
            </div>
            <div class="full-width">
                <dt>Address</dt>
                <dd>{{ $branch->address ?: 'Not provided' }}</dd>
            </div>
        </dl>
    </section>
@endsection
