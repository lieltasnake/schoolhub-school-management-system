@extends('layouts.app')

@section('title', $student->full_name)

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">{{ $student->branch->name }} / Student profile</p>
            <h1>{{ $student->full_name }}</h1>
            <p class="intro-copy">Admission No: <strong>{{ $student->admission_number }}</strong> &bull; Admitted: {{ $student->admission_date->format('M d, Y') }}</p>
        </div>
        <div class="intro-actions" style="display: flex; gap: 0.5rem; align-items: center;">
            <a href="{{ route('students.index') }}" class="secondary-link">Back to list</a>
            @can('update', $student)
                <a href="{{ route('students.edit', $student) }}" class="primary-link">Edit profile</a>
                <form method="POST" action="{{ route('students.toggle-status', $student) }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="secondary-button" style="cursor: pointer;">
                        {{ $student->status === 'active' ? 'Deactivate student' : 'Activate student' }}
                    </button>
                </form>
            @endcan
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
            <span class="status-pill {{ $student->status }}" style="display: inline-block; margin-top: 0.25rem;">{{ ucfirst($student->status) }}</span>
            <small>{{ $student->branch->name }}</small>
        </article>

        <article class="metric-card">
            <span class="metric-label">Current class</span>
            <strong>{{ $student->latestEnrollment?->grade->name ?? 'Unassigned' }}</strong>
            <small>{{ $student->latestEnrollment?->section ? 'Section ' . $student->latestEnrollment->section->name : 'No section' }}</small>
        </article>

        <article class="metric-card">
            <span class="metric-label">Primary contact</span>
            <strong>{{ $student->primary_guardian?->full_name ?? 'None' }}</strong>
            <small>{{ $student->primary_guardian?->phone ?? 'No phone listed' }}</small>
        </article>
    </div>

    <!-- Student Details -->
    <section class="content-card" style="margin-bottom: 1.5rem;">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Personal profile</p>
                <h2>Student details</h2>
            </div>
        </div>

        <dl class="detail-list">
            <div><dt>Full name</dt><dd><strong>{{ $student->full_name }}</strong></dd></div>
            <div><dt>Admission number</dt><dd>{{ $student->admission_number }}</dd></div>
            <div><dt>Gender</dt><dd>{{ ucfirst($student->gender) }}</dd></div>
            <div><dt>Date of birth</dt><dd>{{ $student->date_of_birth->format('F d, Y') }} ({{ $student->date_of_birth->age }} years old)</dd></div>
            <div><dt>Admission date</dt><dd>{{ $student->admission_date->format('F d, Y') }}</dd></div>
            <div><dt>Campus</dt><dd>{{ $student->branch->name }}</dd></div>
            <div><dt>Student Type / Category</dt><dd><strong>{{ $student->studentType?->name ?? 'Standard' }}</strong></dd></div>
            <div><dt>Blood group</dt><dd>{{ $student->blood_group ?? '—' }}</dd></div>
            <div><dt>Religion</dt><dd>{{ $student->religion ?? '—' }}</dd></div>
            <div><dt>National ID / Cert</dt><dd>{{ $student->national_id ?? '—' }}</dd></div>
            <div><dt>Email</dt><dd>{{ $student->email ?? '—' }}</dd></div>
            <div><dt>Phone</dt><dd>{{ $student->phone ?? '—' }}</dd></div>
            <div><dt>City</dt><dd>{{ $student->city ?? '—' }}</dd></div>
            <div class="full-width"><dt>Residential address</dt><dd>{{ $student->address ?? '—' }}</dd></div>
            @if ($student->groups->isNotEmpty())
                <div class="full-width">
                    <dt>Student Groups & Houses</dt>
                    <dd style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.35rem;">
                        @foreach ($student->groups as $group)
                            <a href="{{ route('student-groups.show', $group) }}" class="badge" style="background: var(--panel-soft); text-decoration: none;">
                                {{ $group->name }} ({{ ucfirst($group->pivot->role) }})
                            </a>
                        @endforeach
                    </dd>
                </div>
            @endif
            @if ($student->medical_notes)
                <div class="full-width"><dt>Medical & Health notes</dt><dd>{{ $student->medical_notes }}</dd></div>
            @endif
        </dl>
    </section>

    <!-- Guardian Details -->
    <section class="content-card" style="margin-bottom: 1.5rem;">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Parents & Guardians</p>
                <h2>Family contacts</h2>
            </div>
        </div>

        @if ($student->guardians->isNotEmpty())
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Guardian name</th>
                            <th>Relationship</th>
                            <th>Primary?</th>
                            <th>Phone number</th>
                            <th>Email</th>
                            <th>Occupation</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($student->guardians as $guardian)
                            <tr>
                                <td><strong>{{ $guardian->full_name }}</strong></td>
                                <td>{{ ucfirst(str_replace('_', ' ', $guardian->pivot->relationship)) }}</td>
                                <td>
                                    @if ($guardian->pivot->is_primary)
                                        <span class="status-pill active">Primary</span>
                                    @else
                                        <span style="color: var(--muted, #64748b);">Secondary</span>
                                    @endif
                                </td>
                                <td>{{ $guardian->phone }}</td>
                                <td>{{ $guardian->email ?? '—' }}</td>
                                <td>{{ $guardian->occupation ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p style="color: var(--muted, #64748b);">No parent or guardian records attached to this student.</p>
        @endif

        @if ($student->emergency_contact_name || $student->emergency_contact_phone)
            <div style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--border, #334155);">
                <p class="eyebrow">Alternate emergency contact</p>
                <p><strong>{{ $student->emergency_contact_name ?? 'Unnamed' }}</strong> &bull; {{ $student->emergency_contact_phone ?? 'No phone' }}</p>
            </div>
        @endif
    </section>

    <!-- Enrollment History -->
    <section class="content-card">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Academic progression</p>
                <h2>Enrollment history</h2>
            </div>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Academic year</th>
                        <th>Grade / Class</th>
                        <th>Section</th>
                        <th>Roll #</th>
                        <th>Enrolled date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($student->enrollments as $enrollment)
                        <tr>
                            <td><strong>{{ $enrollment->academicYear->name }}</strong></td>
                            <td>{{ $enrollment->grade->name }}</td>
                            <td>{{ $enrollment->section?->name ?? 'Unassigned' }}</td>
                            <td>{{ $enrollment->roll_number ?? '—' }}</td>
                            <td>{{ $enrollment->enrolled_at->format('M d, Y') }}</td>
                            <td><span class="status-pill {{ $enrollment->status }}">{{ ucfirst($enrollment->status) }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">No enrollment records logged yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <!-- Student Finance & Billing Statements -->
    <section class="content-card" style="margin-top: 1.5rem;">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Finance & Billing</p>
                <h2>Fee statements & invoices</h2>
            </div>
            @can('create', App\Models\Invoice::class)
                <a href="{{ route('invoices.create', ['branch_id' => $student->branch_id, 'student_id' => $student->id]) }}" class="primary-link">Issue invoice</a>
            @endcan
        </div>

        @php
            $totalBilled = (float) $student->invoices->where('status', '!=', 'cancelled')->sum('total_amount');
            $totalPaid = (float) $student->invoices->where('status', '!=', 'cancelled')->sum('paid_amount');
            $outstanding = (float) $student->invoices->where('status', '!=', 'cancelled')->sum('balance');
        @endphp

        <div class="dashboard-grid" style="margin-bottom: 1.5rem;">
            <article class="metric-card">
                <span class="metric-label">Total Billed</span>
                <strong style="font-size: 1.25rem;">${{ number_format($totalBilled, 2) }}</strong>
                <small>All valid invoices</small>
            </article>

            <article class="metric-card">
                <span class="metric-label">Total Paid</span>
                <strong style="font-size: 1.25rem; color: #0f766e;">${{ number_format($totalPaid, 2) }}</strong>
                <small>Confirmed payments</small>
            </article>

            <article class="metric-card">
                <span class="metric-label">Current Balance Due</span>
                <strong style="font-size: 1.25rem; color: {{ $outstanding > 0 ? '#b91c1c' : '#15803d' }};">
                    ${{ number_format($outstanding, 2) }}
                </strong>
                <small>{{ $outstanding > 0 ? 'Pending payment' : 'No balance due' }}</small>
            </article>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Academic Session</th>
                        <th>Total Amount</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($student->invoices as $invoice)
                        <tr>
                            <td><strong><code>{{ $invoice->invoice_number }}</code></strong></td>
                            <td>{{ $invoice->academicYear->name }} {{ $invoice->term ? '— ' . $invoice->term->name : '' }}</td>
                            <td>${{ number_format((float) $invoice->total_amount, 2) }}</td>
                            <td>${{ number_format((float) $invoice->paid_amount, 2) }}</td>
                            <td>
                                <strong style="color: {{ (float) $invoice->balance > 0 ? '#b91c1c' : '#15803d' }};">
                                    ${{ number_format((float) $invoice->balance, 2) }}
                                </strong>
                            </td>
                            <td>{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : '—' }}</td>
                            <td><span class="status-pill {{ $invoice->status }}">{{ ucfirst(str_replace('_', ' ', $invoice->status)) }}</span></td>
                            <td>
                                <a href="{{ route('invoices.show', $invoice) }}" class="table-link">View statement</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">No invoices have been issued for this student yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
