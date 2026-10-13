@extends('layouts.app')

@section('title', $member ? 'Edit Library Member' : 'Register Library Member')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Library Management</p>
            <h1>{{ $member ? 'Edit library member' : 'Register library member' }}</h1>
            <p class="intro-copy">Create library card account for a student or staff member and set borrowing quotas.</p>
        </div>
        <a href="{{ route('library-members.index') }}" class="secondary-link">Cancel</a>
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

        <form method="POST" action="{{ $member ? route('library-members.update', $member) : route('library-members.store') }}" class="form-grid">
            @csrf
            @if ($member)
                @method('PUT')
            @endif

            <div class="full-width">
                <label for="branch_id">Campus</label>
                <select id="branch_id" name="branch_id" required>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) old('branch_id', $member?->branch_id ?? $selectedBranchId) === (string) $branch->id)>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="member_type">Member type</label>
                <select id="member_type" name="member_type" required onchange="toggleMemberType(this.value)">
                    <option value="student" @selected(old('member_type', $member?->member_type ?? 'student') === 'student')>Student</option>
                    <option value="staff" @selected(old('member_type', $member?->member_type) === 'staff')>Staff Member</option>
                </select>
            </div>

            <div>
                <label for="member_number">Member card number</label>
                <input id="member_number" name="member_number" value="{{ old('member_number', $member?->member_number ?? $suggestedMemberNumber) }}" maxlength="50" required>
            </div>

            <div id="student_select_wrap">
                <label for="student_id">Select student</label>
                <select id="student_id" name="student_id">
                    <option value="">Choose student</option>
                    @foreach ($students as $stu)
                        <option value="{{ $stu->id }}" @selected((string) old('student_id', $member?->student_id) === (string) $stu->id)>
                            {{ $stu->full_name }} ({{ $stu->admission_number }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div id="staff_select_wrap" style="display:none;">
                <label for="user_id">Select staff user</label>
                <select id="user_id" name="user_id">
                    <option value="">Choose staff user</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}" @selected((string) old('user_id', $member?->user_id) === (string) $u->id)>
                            {{ $u->name }} ({{ $u->email }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="max_borrow_limit">Max borrow limit (books)</label>
                <input id="max_borrow_limit" name="max_borrow_limit" type="number" min="1" max="20" value="{{ old('max_borrow_limit', $member?->max_borrow_limit ?? 3) }}" required>
            </div>

            <div>
                <label for="status">Membership status</label>
                <select id="status" name="status" required>
                    @foreach (['active', 'suspended', 'expired'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $member?->status ?? 'active') === $status)>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="full-width form-actions">
                <button type="submit" class="primary-button">{{ $member ? 'Update member' : 'Register member' }}</button>
            </div>
        </form>
    </section>

    <script>
        function toggleMemberType(val) {
            const studentWrap = document.getElementById('student_select_wrap');
            const staffWrap = document.getElementById('staff_select_wrap');
            if (val === 'student') {
                studentWrap.style.display = 'block';
                staffWrap.style.display = 'none';
            } else {
                studentWrap.style.display = 'none';
                staffWrap.style.display = 'block';
            }
        }
        document.addEventListener('DOMContentLoaded', () => {
            const select = document.getElementById('member_type');
            if (select) toggleMemberType(select.value);
        });
    </script>
@endsection
