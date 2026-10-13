@extends('layouts.app')

@section('title', 'Take Staff Attendance')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Attendance</p>
            <h1>Take staff attendance</h1>
            <p class="intro-copy">Record daily staff presence, check-in timestamps, and absence reasons.</p>
        </div>
        <a href="{{ route('staff-attendance.index') }}" class="secondary-link">Back to list</a>
    </section>

    <section class="content-card">
        <form method="GET" action="{{ route('staff-attendance.create') }}" class="filter-form" style="margin-bottom:1.5rem;">
            @if ($branches->count() > 1)
                <select name="branch_id" aria-label="Select campus" onchange="this.form.submit()">
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) $selectedBranchId === (string) $branch->id)>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            @endif
            <input type="date" name="attendance_date" value="{{ $attendanceDate }}" required onchange="this.form.submit()">
            <button type="submit" class="secondary-button">Load Staff</button>
        </form>

        @if ($errors->any())
            <div class="alert alert-error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($staffMembers->isNotEmpty())
            <form method="POST" action="{{ route('staff-attendance.store') }}">
                @csrf
                <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">
                <input type="hidden" name="attendance_date" value="{{ $attendanceDate }}">

                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Staff Member</th>
                                <th>Status</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($staffMembers as $index => $staff)
                                @php
                                    $existing = $existingRecords->get($staff->id);
                                    $currentStatus = $existing ? $existing->status : 'present';
                                    $currentCheckIn = $existing ? $existing->check_in_time : '08:00';
                                    $currentCheckOut = $existing ? $existing->check_out_time : '16:00';
                                    $currentRemarks = $existing ? $existing->remarks : '';
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <strong>{{ $staff->name }}</strong>
                                        <div><small style="color:var(--text-muted, #64748b);">{{ $staff->email }}</small></div>
                                        <input type="hidden" name="attendances[{{ $index }}][user_id]" value="{{ $staff->id }}">
                                    </td>
                                    <td>
                                        <div style="display:flex; flex-wrap:wrap; gap:0.75rem; align-items:center;">
                                            @foreach (['present', 'absent', 'late', 'on_leave', 'half_day'] as $opt)
                                                <label style="display:flex; align-items:center; gap:0.25rem; cursor:pointer;">
                                                    <input type="radio" name="attendances[{{ $index }}][status]" value="{{ $opt }}" @checked(old("attendances.{$index}.status", $currentStatus) === $opt)>
                                                    <span>{{ ucfirst(str_replace('_', ' ', $opt)) }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        <input type="time" name="attendances[{{ $index }}][check_in_time]" value="{{ old("attendances.{$index}.check_in_time", $currentCheckIn) }}" style="width:110px;">
                                    </td>
                                    <td>
                                        <input type="time" name="attendances[{{ $index }}][check_out_time]" value="{{ old("attendances.{$index}.check_out_time", $currentCheckOut) }}" style="width:110px;">
                                    </td>
                                    <td>
                                        <input type="text" name="attendances[{{ $index }}][remarks]" value="{{ old("attendances.{$index}.remarks", $currentRemarks) }}" placeholder="e.g. Approved leave" style="width:100%; max-width:200px;">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="margin-top:1.5rem; display:flex; justify-content:flex-end;">
                    <button type="submit" class="primary-button">Save staff attendance</button>
                </div>
            </form>
        @else
            <p style="text-align:center; padding:3rem 1rem; color:var(--text-muted, #64748b);">
                No staff members found for the selected campus.
            </p>
        @endif
    </section>
@endsection
