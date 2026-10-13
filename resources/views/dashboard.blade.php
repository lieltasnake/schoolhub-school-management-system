@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">School overview</p>
            <h1>Welcome back, {{ $user->name }}</h1>
            <p class="intro-copy">
                @if ($schoolSettings?->school_name)
                    Here is what is happening at {{ $schoolSettings->school_name }} today.
                @else
                    Here is what is happening across your school today.
                @endif
            </p>
        </div>
        <p class="intro-date">{{ now($schoolSettings?->timezone ?? config('app.timezone'))->format('l, F j, Y') }}</p>
    </section>

    <section class="stats-grid" aria-label="School statistics">
        <article class="stat-card stat-card-teal">
            <div class="stat-card-heading">
                <span>Total Students</span>
                <span class="stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M3.5 20v-1.5a5.5 5.5 0 0 1 11 0V20m2-9.5a3.5 3.5 0 0 0 0-6.8M18 14a4 4 0 0 1 2.5 3.7V20"/></svg></span>
            </div>
            <strong>{{ $studentCount ?? 0 }}</strong>
            <small>Active students in authorized scope</small>
        </article>

        <article class="stat-card stat-card-blue">
            <div class="stat-card-heading">
                <span>Total Branches</span>
                <span class="stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3.5 20.5h17M5.5 20.5v-13l6.5-4 6.5 4v13M9 9.5h1m4 0h1M9 13.5h1m4 0h1M10 20.5v-4h4v4"/></svg></span>
            </div>
            <strong>{{ $totalBranches }}</strong>
            <small>Campuses you are authorized to view</small>
        </article>

        <article class="stat-card stat-card-mint">
            <div class="stat-card-heading">
                <span>Total Staff</span>
                <span class="stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="8" cy="8" r="3"/><circle cx="17" cy="9" r="2.5"/><path d="M2.5 20v-1.2a5.5 5.5 0 0 1 11 0V20m2-4.5a4.2 4.2 0 0 1 6 3.8v.7"/></svg></span>
            </div>
            <strong>{{ $totalStaff }}</strong>
            <small>Staff accounts in your authorized scope</small>
        </article>

        <article class="stat-card stat-card-white">
            <div class="stat-card-heading">
                <span>Active Academic Year</span>
                <span class="stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3.5" y="5.5" width="17" height="15" rx="2"/><path d="M7.5 3.5v4m9-4v4m-13 2h17m-12 4h3m-3 3h6"/></svg></span>
            </div>
            <strong class="stat-value-text">{{ $activeAcademicYear ?? 'Not configured' }}</strong>
            <small>{{ $activeTerm ?? 'Terms and semesters are not configured' }}</small>
        </article>
    </section>

    <div class="dashboard-columns">
        <section class="content-card organization-card">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">School organization</p>
                    <h2>Branches and campuses</h2>
                </div>
                @can('viewAny', App\Models\Branch::class)
                    <a href="{{ route('branches.index') }}" class="text-link">View branches</a>
                @endcan
            </div>

            @if ($branches->isNotEmpty())
                <ul class="branch-list">
                    @foreach ($branches as $branch)
                        <li class="branch-list-item">
                            <span class="branch-mark" aria-hidden="true">{{ strtoupper(substr($branch->name, 0, 1)) }}</span>
                            <span class="branch-details">
                                <strong>{{ $branch->name }}</strong>
                                <small>{{ $branch->code }}</small>
                            </span>
                            <span class="status-pill {{ $branch->status }}">{{ ucfirst($branch->status) }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="empty-state">
                    <h3>No branches are available</h3>
                    @if ($user->canManageBranches())
                        <p>Create the first campus to begin organizing SchoolHub.</p>
                        <a href="{{ route('branches.create') }}" class="text-link">Create a branch</a>
                    @else
                        <p>No branch is currently assigned to your account.</p>
                    @endif
                </div>
            @endif
        </section>

        <section class="content-card setup-card">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Getting started</p>
                    <h2>School setup</h2>
                </div>
                <span class="setup-indicator" aria-label="{{ $activeAcademicYear && $activeTerm ? 'Academic setup complete' : 'Academic setup incomplete' }}"></span>
            </div>

            <div class="setup-row">
                <span class="setup-number">01</span>
                <div>
                    <strong>Active academic year</strong>
                    <p>{{ $activeAcademicYear ?? 'Not configured' }}</p>
                </div>
            </div>
            <div class="setup-row">
                <span class="setup-number">02</span>
                <div>
                    <strong>Active term or semester</strong>
                    <p>{{ $activeTerm ?? 'Not configured' }}</p>
                </div>
            </div>
            <div class="setup-row">
                <span class="setup-number">03</span>
                <div>
                    <strong>School profile</strong>
                    <p>{{ $schoolSettings?->school_name ?? 'Not configured' }}</p>
                </div>
            </div>

            @if ($user->canManageSchoolSettings() || $user->canManageAcademicCalendar())
                <div class="setup-actions">
                    @if ($user->canManageSchoolSettings())
                        <a href="{{ route('settings.school.edit') }}" class="text-link">{{ $schoolSettings ? 'Edit school profile' : 'Configure school profile' }}</a>
                    @endif
                    @if ($user->canManageAcademicCalendar())
                        <a href="{{ route('academic-years.index') }}" class="text-link">Manage academic calendar</a>
                    @endif
                </div>
            @else
                <p class="setup-note">Your administrator can configure school details and the academic calendar.</p>
            @endif
        </section>
    </div>

    <section class="content-card finance-card">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Finance</p>
                <h2>Fees overview</h2>
            </div>
            @if ($user->canViewFinance())
                <a href="{{ route('invoices.index') }}" class="text-link">Manage finance</a>
            @endif
        </div>
        <div class="finance-summary">
            <div>
                <span>Fees collected</span>
                <strong style="color: #0f766e;">${{ number_format($feesCollected ?? 0, 2) }}</strong>
            </div>
            <div>
                <span>Outstanding balances</span>
                <strong style="color: {{ ($outstandingBalance ?? 0) > 0 ? '#b91c1c' : '#15803d' }};">${{ number_format($outstandingBalance ?? 0, 2) }}</strong>
            </div>
            <p>Real-time revenue collections and unpaid student fee balances across your authorized campus scope.</p>
        </div>
    </section>
@endsection
