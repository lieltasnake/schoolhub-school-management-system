@extends('layouts.app')

@section('title', 'Registration Promotion')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Registration</p>
            <h1>Registration promotion</h1>
            <p class="intro-copy">Admit and promote approved applicant registrations into active student enrollment records.</p>
        </div>
        <a href="{{ route('registrations.index') }}" class="secondary-link">All applications</a>
    </section>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <section class="content-card" style="margin-bottom: 1.5rem;">
        <form method="GET" action="{{ route('registrations.promotion') }}" class="filter-form">
            @if ($branches->count() > 1)
                <select name="branch_id" aria-label="Filter by campus">
                    <option value="">All campuses</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) request('branch_id', $selectedBranchId) === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            @endif

            <select name="academic_year_id" aria-label="Filter by academic year">
                <option value="">All academic years</option>
                @foreach ($academicYears as $year)
                    <option value="{{ $year->id }}" @selected((string) request('academic_year_id') === (string) $year->id)>{{ $year->name }}</option>
                @endforeach
            </select>

            <select name="grade_id" aria-label="Filter by grade">
                <option value="">All grades</option>
                @foreach ($grades as $grade)
                    <option value="{{ $grade->id }}" @selected((string) request('grade_id') === (string) $grade->id)>{{ $grade->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="secondary-button">Filter candidates</button>
            @if (request()->hasAny(['branch_id', 'academic_year_id', 'grade_id']))
                <a href="{{ route('registrations.promotion') }}" class="text-link" style="align-self: center;">Reset</a>
            @endif
        </form>
    </section>

    <section class="content-card">
        @if ($candidates->isEmpty())
            <div style="text-align: center; padding: 2.5rem 1rem; color: var(--muted);">
                <h3>No approved registration applications found</h3>
                <p>Only registration applications marked as <strong>Approved</strong> can be promoted to enrolled students.</p>
                <a href="{{ route('registrations.index') }}" class="primary-link" style="margin-top: 1rem; display: inline-block;">Browse applications</a>
            </div>
        @else
            <form method="POST" action="{{ route('registrations.promotion.promote') }}">
                @csrf

                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem; padding-bottom: 1rem; border-bottom: 1px solid var(--edge);">
                    <div>
                        <h2 style="font-size: 1.15rem; margin-bottom: 0.25rem;">Approved Candidates ({{ $candidates->count() }})</h2>
                        <p style="color: var(--muted); font-size: 0.875rem; margin: 0;">Select applicants and assign their initial section/classroom.</p>
                    </div>

                    <div style="display: flex; gap: 0.75rem; align-items: center;">
                        <label for="section_id" style="font-weight: 500; font-size: 0.9rem; margin: 0;">Assign Section:</label>
                        <select id="section_id" name="section_id" style="padding: 0.4rem 0.75rem;">
                            <option value="">Unassigned section</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="primary-button" onclick="return confirm('Promote selected candidates into active enrolled students?')">Promote Selected</button>
                    </div>
                </div>

                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" id="select-all" onclick="document.querySelectorAll('.reg-checkbox').forEach(c => c.checked = this.checked)" title="Select all">
                                </th>
                                <th>Reg #</th>
                                <th>Candidate name</th>
                                <th>Gender</th>
                                <th>Target placement</th>
                                <th>Guardian</th>
                                <th>Category</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($candidates as $reg)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="registration_ids[]" value="{{ $reg->id }}" class="reg-checkbox">
                                    </td>
                                    <td>
                                        <strong>{{ $reg->registration_number }}</strong>
                                    </td>
                                    <td>
                                        <a href="{{ route('registrations.show', $reg) }}" class="table-link" style="font-weight: 600;">
                                            {{ $reg->full_name }}
                                        </a>
                                        <small style="display: block; color: var(--muted);">DOB: {{ $reg->date_of_birth->format('M d, Y') }}</small>
                                    </td>
                                    <td style="text-transform: capitalize;">{{ $reg->gender }}</td>
                                    <td>
                                        <strong>{{ $reg->grade->name }}</strong>
                                        <small style="display: block; color: var(--muted);">{{ $reg->academicYear->name }} &bull; {{ $reg->branch->name }}</small>
                                    </td>
                                    <td>
                                        {{ $reg->guardian_name }}
                                        <small style="display: block; color: var(--muted);">{{ $reg->guardian_phone }}</small>
                                    </td>
                                    <td>{{ $reg->studentType?->name ?? 'Standard' }}</td>
                                    <td>
                                        <a href="{{ route('registrations.show', $reg) }}" class="text-link">Review</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </form>
        @endif
    </section>
@endsection
