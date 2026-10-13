@extends('layouts.app')

@section('title', 'Registration Setup')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Registration</p>
            <h1>Registration setup</h1>
            <p class="intro-copy">Configure admission campaigns, application windows, fees, and registration capacity.</p>
        </div>
        <a href="{{ route('registrations.setup.create') }}" class="primary-link">New registration setup</a>
    </section>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <section class="content-card">
        @if ($branches->count() > 1)
            <form method="GET" action="{{ route('registrations.setup') }}" class="filter-form">
                <select name="branch_id" aria-label="Filter by campus">
                    <option value="">All campuses</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) request('branch_id', $selectedBranchId) === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="secondary-button">Filter</button>
            </form>
        @endif

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Campaign title</th>
                        <th>Campus</th>
                        <th>Academic year</th>
                        <th>Window</th>
                        <th>Application fee</th>
                        <th>Applications</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($setups as $setup)
                        <tr>
                            <td>
                                <strong>{{ $setup->title }}</strong>
                                @if ($setup->description)
                                    <small style="display: block; color: var(--muted);">{{ Str::limit($setup->description, 60) }}</small>
                                @endif
                            </td>
                            <td>{{ $setup->branch->name }}</td>
                            <td>{{ $setup->academicYear->name }}</td>
                            <td>
                                {{ $setup->start_date->format('M d, Y') }} &ndash; {{ $setup->end_date->format('M d, Y') }}
                            </td>
                            <td>
                                ${{ number_format($setup->application_fee, 2) }}
                            </td>
                            <td>
                                <strong>{{ $setup->registrations_count }}</strong>
                                @if ($setup->capacity)
                                    <small style="color: var(--muted);">/ {{ $setup->capacity }} max</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge status-{{ $setup->status === 'open' ? 'active' : ($setup->status === 'closed' ? 'inactive' : 'planned') }}">
                                    {{ ucfirst($setup->status) }}
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                    <a href="{{ route('registrations.setup.edit', $setup) }}" class="text-link">Edit</a>
                                    <form method="POST" action="{{ route('registrations.setup.destroy', $setup) }}" onsubmit="return confirm('Delete this registration campaign?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-link" style="color: var(--danger); background: none; border: none; padding: 0; cursor: pointer;">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2rem; color: var(--muted);">
                                No registration setups configured yet. Click "New registration setup" to start an admission session.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($setups->hasPages())
            <div style="margin-top: 1.5rem;">
                {{ $setups->links() }}
            </div>
        @endif
    </section>
@endsection
