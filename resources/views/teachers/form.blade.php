@extends('layouts.app')

@section('title', $teacher ? 'Edit Teacher' : 'Create Teacher')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Academic Staff</p>
            <h1>{{ $teacher ? 'Edit teacher' : 'Create teacher' }}</h1>
            <p class="intro-copy">Link a user account to teacher profile and assign qualification details.</p>
        </div>
        <a href="{{ $teacher ? route('teachers.show', $teacher) : route('teachers.index') }}" class="secondary-link">Cancel</a>
    </section>

    <section class="content-card">
        @if ($errors->any())
            <div class="alert alert-error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $teacher ? route('teachers.update', $teacher) : route('teachers.store') }}" class="form-grid">
            @csrf
            @if ($teacher)
                @method('PUT')
            @endif

            <div>
                <label for="branch_id">Campus</label>
                <select id="branch_id" name="branch_id" required>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) old('branch_id', $teacher?->branch_id) === (string) $branch->id)>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="user_id">User account</label>
                @if ($teacher)
                    <input type="text" value="{{ $teacher->user->name }} ({{ $teacher->user->email }})" disabled>
                    <input type="hidden" name="user_id" value="{{ $teacher->user_id }}">
                @else
                    <select id="user_id" name="user_id" required>
                        <option value="">Select a user account</option>
                        @foreach ($availableUsers as $u)
                            <option value="{{ $u->id }}" @selected((string) old('user_id') === (string) $u->id)>
                                {{ $u->name }} ({{ $u->email }})
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>

            <div>
                <label for="employee_id">Employee ID</label>
                <input id="employee_id" name="employee_id" value="{{ old('employee_id', $teacher?->employee_id) }}" maxlength="50" placeholder="e.g. TCH-001" required>
            </div>

            <div>
                <label for="phone">Phone number</label>
                <input id="phone" name="phone" type="tel" value="{{ old('phone', $teacher?->phone) }}" maxlength="50" placeholder="+123456789">
            </div>

            <div>
                <label for="qualification">Highest qualification</label>
                <input id="qualification" name="qualification" value="{{ old('qualification', $teacher?->qualification) }}" maxlength="255" placeholder="e.g. M.Sc. Mathematics, B.Ed.">
            </div>

            <div>
                <label for="specialization">Specialization / Subjects</label>
                <input id="specialization" name="specialization" value="{{ old('specialization', $teacher?->specialization) }}" maxlength="255" placeholder="e.g. Science, Advanced Math">
            </div>

            <div>
                <label for="joining_date">Joining date</label>
                <input id="joining_date" name="joining_date" type="date" value="{{ old('joining_date', $teacher?->joining_date?->format('Y-m-d')) }}">
            </div>

            <div>
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    @foreach (['active', 'on_leave', 'resigned', 'terminated'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $teacher?->status ?? 'active') === $status)>
                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="full-width form-actions">
                <button type="submit" class="primary-button">{{ $teacher ? 'Update teacher' : 'Create teacher' }}</button>
            </div>
        </form>
    </section>
@endsection
