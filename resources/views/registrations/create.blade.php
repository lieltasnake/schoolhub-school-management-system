@extends('layouts.app')

@section('title', 'New Student Registration')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Registration</p>
            <h1>New student registration</h1>
            <p class="intro-copy">Submit a new applicant admission form for review and prospective enrollment.</p>
        </div>
        <a href="{{ route('registrations.index') }}" class="secondary-link">Cancel</a>
    </section>

    <section class="content-card">
        @if ($errors->any())
            <div class="alert alert-error">
                <strong>Please resolve the following issues:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('registrations.store') }}" class="form-grid">
            @csrf

            <div class="full-width">
                <h3 style="margin-bottom: 0.25rem;">1. Placement & Session</h3>
                <p style="color: var(--muted); font-size: 0.875rem;">Choose the campus, target academic year, and grade.</p>
            </div>

            <div>
                <label for="branch_id">Campus <span style="color: #ef4444;">*</span></label>
                <select id="branch_id" name="branch_id" required>
                    <option value="">Select campus</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) old('branch_id', $selectedBranchId) === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="registration_number">Registration #</label>
                <input id="registration_number" name="registration_number" value="{{ old('registration_number', $generatedRegistrationNumber) }}" placeholder="Leave blank to auto-generate">
                <small style="color: var(--muted);">Auto-generated default</small>
            </div>

            <div>
                <label for="academic_year_id">Target Academic Year <span style="color: #ef4444;">*</span></label>
                <select id="academic_year_id" name="academic_year_id" required>
                    <option value="">Select academic year</option>
                    @foreach ($academicYears as $year)
                        <option value="{{ $year->id }}" @selected((string) old('academic_year_id') === (string) $year->id)>{{ $year->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="grade_id">Target Grade <span style="color: #ef4444;">*</span></label>
                <select id="grade_id" name="grade_id" required>
                    <option value="">Select grade</option>
                    @foreach ($grades as $grade)
                        <option value="{{ $grade->id }}" @selected((string) old('grade_id') === (string) $grade->id)>{{ $grade->name }} ({{ $grade->code }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="student_type_id">Student Type / Category</label>
                <select id="student_type_id" name="student_type_id">
                    <option value="">Standard / General</option>
                    @foreach ($studentTypes as $type)
                        <option value="{{ $type->id }}" @selected((string) old('student_type_id') === (string) $type->id)>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="registration_setup_id">Registration Session / Setup</label>
                <select id="registration_setup_id" name="registration_setup_id">
                    <option value="">Standard intake</option>
                    @foreach ($setups as $setup)
                        <option value="{{ $setup->id }}" @selected((string) old('registration_setup_id') === (string) $setup->id)>{{ $setup->title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="full-width" style="margin-top: 1rem; border-top: 1px solid var(--edge); padding-top: 1rem;">
                <h3 style="margin-bottom: 0.25rem;">2. Applicant Information</h3>
                <p style="color: var(--muted); font-size: 0.875rem;">Personal details of the prospective student.</p>
            </div>

            <div>
                <label for="first_name">First Name <span style="color: #ef4444;">*</span></label>
                <input id="first_name" name="first_name" value="{{ old('first_name') }}" required>
            </div>

            <div>
                <label for="middle_name">Middle Name</label>
                <input id="middle_name" name="middle_name" value="{{ old('middle_name') }}">
            </div>

            <div>
                <label for="last_name">Last Name <span style="color: #ef4444;">*</span></label>
                <input id="last_name" name="last_name" value="{{ old('last_name') }}" required>
            </div>

            <div>
                <label for="gender">Gender <span style="color: #ef4444;">*</span></label>
                <select id="gender" name="gender" required>
                    <option value="">Select gender</option>
                    <option value="male" @selected(old('gender') === 'male')>Male</option>
                    <option value="female" @selected(old('gender') === 'female')>Female</option>
                    <option value="other" @selected(old('gender') === 'other')>Other</option>
                </select>
            </div>

            <div>
                <label for="date_of_birth">Date of Birth <span style="color: #ef4444;">*</span></label>
                <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" required>
            </div>

            <div>
                <label for="registration_date">Registration Date <span style="color: #ef4444;">*</span></label>
                <input type="date" id="registration_date" name="registration_date" value="{{ old('registration_date', now()->toDateString()) }}" required>
            </div>

            <div>
                <label for="email">Applicant Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}">
            </div>

            <div>
                <label for="phone">Applicant Phone</label>
                <input id="phone" name="phone" value="{{ old('phone') }}">
            </div>

            <div>
                <label for="previous_school">Previous School Attended</label>
                <input id="previous_school" name="previous_school" value="{{ old('previous_school') }}" placeholder="e.g. St. George Academy">
            </div>

            <div class="full-width">
                <label for="address">Residential Address</label>
                <textarea id="address" name="address" rows="2">{{ old('address') }}</textarea>
            </div>

            <div class="full-width" style="margin-top: 1rem; border-top: 1px solid var(--edge); padding-top: 1rem;">
                <h3 style="margin-bottom: 0.25rem;">3. Guardian Contact</h3>
                <p style="color: var(--muted); font-size: 0.875rem;">Parent or primary guardian details.</p>
            </div>

            <div>
                <label for="guardian_name">Guardian Full Name <span style="color: #ef4444;">*</span></label>
                <input id="guardian_name" name="guardian_name" value="{{ old('guardian_name') }}" required>
            </div>

            <div>
                <label for="guardian_phone">Guardian Phone <span style="color: #ef4444;">*</span></label>
                <input id="guardian_phone" name="guardian_phone" value="{{ old('guardian_phone') }}" required>
            </div>

            <div>
                <label for="guardian_email">Guardian Email</label>
                <input type="email" id="guardian_email" name="guardian_email" value="{{ old('guardian_email') }}">
            </div>

            <div>
                <label for="guardian_relationship">Relationship</label>
                <input id="guardian_relationship" name="guardian_relationship" value="{{ old('guardian_relationship', 'Parent') }}" placeholder="Father, Mother, Legal Guardian">
            </div>

            <div class="full-width" style="margin-top: 1rem; border-top: 1px solid var(--edge); padding-top: 1rem;">
                <h3 style="margin-bottom: 0.25rem;">4. Status & Review</h3>
            </div>

            <div>
                <label for="status">Registration Status <span style="color: #ef4444;">*</span></label>
                <select id="status" name="status" required>
                    <option value="pending" @selected(old('status', 'pending') === 'pending')>Pending Review</option>
                    <option value="approved" @selected(old('status') === 'approved')>Approved</option>
                    <option value="enrolled" @selected(old('status') === 'enrolled')>Enrolled</option>
                    <option value="rejected" @selected(old('status') === 'rejected')>Rejected</option>
                </select>
            </div>

            <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 1.5rem;">
                <input type="checkbox" id="application_fee_paid" name="application_fee_paid" value="1" @checked(old('application_fee_paid'))>
                <label for="application_fee_paid" style="margin: 0; font-weight: 500;">Application fee received</label>
            </div>

            <div class="full-width">
                <label for="notes">Administrative Notes</label>
                <textarea id="notes" name="notes" rows="2" placeholder="Interview remarks, documentation status, etc.">{{ old('notes') }}</textarea>
            </div>

            <div class="full-width" style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem;">
                <a href="{{ route('registrations.index') }}" class="secondary-link">Cancel</a>
                <button type="submit" class="primary-button">Submit registration</button>
            </div>
        </form>
    </section>
@endsection
