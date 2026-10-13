@extends('layouts.app')

@section('title', "Application: {$registration->registration_number}")

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Registration / Applications</p>
            <h1>{{ $registration->full_name }}</h1>
            <p class="intro-copy">Application Ref: <strong>{{ $registration->registration_number }}</strong> &bull; Submitted {{ $registration->registration_date->format('M d, Y') }}</p>
        </div>
        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
            <a href="{{ route('registrations.index') }}" class="secondary-link">Back to list</a>
            <a href="{{ route('registrations.edit', $registration) }}" class="secondary-link">Edit application</a>

            @if ($registration->status === 'pending')
                <form method="POST" action="{{ route('registrations.approve', $registration) }}">
                    @csrf
                    <button type="submit" class="primary-button">Approve application</button>
                </form>
                <form method="POST" action="{{ route('registrations.reject', $registration) }}">
                    @csrf
                    <button type="submit" class="secondary-button" style="color: var(--danger); border-color: var(--danger);" onclick="return confirm('Reject this application?')">Reject</button>
                </form>
            @endif

            @if ($registration->status === 'approved')
                <a href="{{ route('registrations.promotion', ['branch_id' => $registration->branch_id, 'grade_id' => $registration->grade_id]) }}" class="primary-link">Promote to Student</a>
            @endif
        </div>
    </section>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <div class="dashboard-grid" style="margin-bottom: 1.5rem;">
        <article class="metric-card">
            <span class="metric-label">Status</span>
            <strong style="text-transform: capitalize;">{{ $registration->status }}</strong>
            <small>Current review state</small>
        </article>
        <article class="metric-card">
            <span class="metric-label">Target placement</span>
            <strong>{{ $registration->grade->name }}</strong>
            <small>{{ $registration->branch->name }} &bull; {{ $registration->academicYear->name }}</small>
        </article>
        <article class="metric-card">
            <span class="metric-label">Application fee</span>
            <strong>{{ $registration->application_fee_paid ? 'Paid' : 'Unpaid' }}</strong>
            <small>{{ $registration->setup ? 'Fee: $' . number_format($registration->setup->application_fee, 2) : 'Standard intake' }}</small>
        </article>
        @if ($registration->convertedStudent)
            <article class="metric-card">
                <span class="metric-label">Enrolled student record</span>
                <strong>{{ $registration->convertedStudent->admission_number }}</strong>
                <small><a href="{{ route('students.show', $registration->convertedStudent) }}" class="text-link">View student profile &rarr;</a></small>
            </article>
        @endif
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
        <section class="content-card">
            <h2 style="font-size: 1.15rem; margin-bottom: 1rem;">Applicant Details</h2>
            <div style="display: grid; gap: 0.75rem;">
                <div>
                    <span style="color: var(--muted); font-size: 0.85rem; display: block;">Full Name</span>
                    <strong>{{ $registration->full_name }}</strong>
                </div>
                <div>
                    <span style="color: var(--muted); font-size: 0.85rem; display: block;">Gender</span>
                    <span style="text-transform: capitalize;">{{ $registration->gender }}</span>
                </div>
                <div>
                    <span style="color: var(--muted); font-size: 0.85rem; display: block;">Date of Birth</span>
                    <span>{{ $registration->date_of_birth->format('M d, Y') }} ({{ $registration->date_of_birth->age }} years old)</span>
                </div>
                <div>
                    <span style="color: var(--muted); font-size: 0.85rem; display: block;">Email</span>
                    <span>{{ $registration->email ?? 'Not provided' }}</span>
                </div>
                <div>
                    <span style="color: var(--muted); font-size: 0.85rem; display: block;">Phone</span>
                    <span>{{ $registration->phone ?? 'Not provided' }}</span>
                </div>
                <div>
                    <span style="color: var(--muted); font-size: 0.85rem; display: block;">Previous School</span>
                    <span>{{ $registration->previous_school ?? 'None listed' }}</span>
                </div>
                <div>
                    <span style="color: var(--muted); font-size: 0.85rem; display: block;">Residential Address</span>
                    <span>{{ $registration->address ?? 'Not provided' }}</span>
                </div>
            </div>
        </section>

        <section class="content-card">
            <h2 style="font-size: 1.15rem; margin-bottom: 1rem;">Guardian & Placement Info</h2>
            <div style="display: grid; gap: 0.75rem;">
                <div>
                    <span style="color: var(--muted); font-size: 0.85rem; display: block;">Primary Guardian</span>
                    <strong>{{ $registration->guardian_name }}</strong>
                    <small style="color: var(--muted); display: block;">Relationship: {{ $registration->guardian_relationship ?? 'Guardian' }}</small>
                </div>
                <div>
                    <span style="color: var(--muted); font-size: 0.85rem; display: block;">Guardian Contact</span>
                    <span>Phone: {{ $registration->guardian_phone }}</span>
                    @if ($registration->guardian_email)
                        <span style="display: block;">Email: {{ $registration->guardian_email }}</span>
                    @endif
                </div>
                <div>
                    <span style="color: var(--muted); font-size: 0.85rem; display: block;">Student Category</span>
                    <span>{{ $registration->studentType?->name ?? 'Standard' }}</span>
                </div>
                @if ($registration->setup)
                    <div>
                        <span style="color: var(--muted); font-size: 0.85rem; display: block;">Admission Campaign</span>
                        <span>{{ $registration->setup->title }}</span>
                    </div>
                @endif
                <div>
                    <span style="color: var(--muted); font-size: 0.85rem; display: block;">Administrative Notes</span>
                    <p style="margin: 0.25rem 0 0; font-size: 0.9rem; color: var(--text);">{{ $registration->notes ?? 'No internal notes recorded.' }}</p>
                </div>
            </div>

            <div style="margin-top: 1.5rem; border-top: 1px solid var(--edge); padding-top: 1rem;">
                <form method="POST" action="{{ route('registrations.destroy', $registration) }}" onsubmit="return confirm('Delete this registration application?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-link" style="color: var(--danger);">Delete application permanently</button>
                </form>
            </div>
        </section>
    </div>
@endsection
