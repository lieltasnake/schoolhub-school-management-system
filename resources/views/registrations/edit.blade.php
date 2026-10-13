@extends('layouts.app')

@section('title', "Edit Application: {$registration->registration_number}")

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Registration</p>
            <h1>Edit registration: {{ $registration->full_name }}</h1>
            <p class="intro-copy">Application Ref: <strong>{{ $registration->registration_number }}</strong> &bull; Campus: {{ $registration->branch->name }}</p>
        </div>
        <a href="{{ route('registrations.show', $registration) }}" class="secondary-link">Cancel</a>
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

        <form method="POST" action="{{ route('registrations.update', $registration) }}" class="form-grid">
            @csrf
            @method('PUT')

            <div class="full-width">
                <h3 style="margin-bottom: 0.25rem;">1. Placement & Session</h3>
            </div>

            <div>
                <label>Campus</label>
                <input type="text" value="{{ $registration->branch->name }}" disabled>
            </div>

            <div>
                <label>Registration Number</label>
                <input type="text" value="{{ $registration->registration_number }}" disabled>
            </div>

            <div>
                <label for="academic_year_id">Target Academic Year <span style="color: #ef4444;">*</span></label>
                <select id="academic_year_id" name="academic_year_id" required>
                    @foreach ($academicYears as $year)
                        <option value="{{ $year->id }}" @selected((string) old('academic_year_id', $registration->academic_year_id) === (string) $year->id)>{{ $year->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="grade_id">Target Grade <span style="color: #ef4444;">*</span></label>
                <select id="grade_id" name="grade_id" required>
                    @foreach ($grades as $grade)
                        <option value="{{ $grade->id }}" @selected((string) old('grade_id', $registration->grade_id) === (string) $grade->id)>{{ $grade->name }} ({{ $grade->code }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="student_type_id">Student Type / Category</label>
                <select id="student_type_id" name="student_type_id">
                    <option value="">Standard / General</option>
                    @foreach ($studentTypes as $type)
                        <option value="{{ $type->id }}" @selected((string) old('student_type_id', $registration->student_type_id) === (string) $type->id)>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="registration_setup_id">Registration Session / Setup</label>
                <select id="registration_setup_id" name="registration_setup_id">
                    <option value="">Standard intake</option>
                    @foreach ($setups as $setup)
                        <option value="{{ $setup->id }}" @selected((string) old('registration_setup_id', $registration->registration_setup_id) === (string) $setup->id)>{{ $setup->title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="full-width" style="margin-top: 1rem; border-top: 1px solid var(--edge); padding-top: 1rem;">
                <h3 style="margin-bottom: 0.25rem;">2. Applicant Information</h3>
            </div>

            <div>
                <label for="first_name">First Name <span style="color: #ef4444;">*</span></label>
                <input id="first_name" name="first_name" value="{{ old('first_name', $registration->first_name) }}" required>
            </div>

            <div>
                <label for="middle_name">Middle Name</label>
                <input id="middle_name" name="middle_name" value="{{ old('middle_name', $registration->middle_name) }}">
            </div>

            <div>
                <label for="last_name">Last Name <span style="color: #ef4444;">*</span></label>
                <input id="last_name" name="last_name" value="{{ old('last_name', $registration->last_name) }}" required>
            </div>

            <div>
                <label for="gender">Gender <span style="color: #ef4444;">*</span></label>
                <select id="gender" name="gender" required>
                    <option value="male" @selected(old('gender', $registration->gender) === 'male')>Male</option>
                    <option value="female" @selected(old('gender', $registration->gender) === 'female')>Female</option>
                    <option value="other" @selected(old('gender', $registration->gender) === 'other')>Other</option>
                </select>
            </div>

            <div>
                <label for="date_of_birth">Date of Birth <span style="color: #ef4444;">*</span></label>
                <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $registration->date_of_birth?->format('Y-m-d')) }}" required>
            </div>

            <div>
                <label for="registration_date">Registration Date <span style="color: #ef4444;">*</span></label>
                <input type="date" id="registration_date" name="registration_date" value="{{ old('registration_date', $registration->registration_date?->format('Y-m-d')) }}" required>
            </div>

            <div>
                <label for="email">Applicant Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $registration->email) }}">
            </div>

            <div>
                <label for="phone">Applicant Phone</label>
                <input id="phone" name="phone" value="{{ old('phone', $registration->phone) }}">
            </div>

            <div>
                <label for="previous_school">Previous School Attended</label>
                <input id="previous_school" name="previous_school" value="{{ old('previous_school', $registration->previous_school) }}">
            </div>

            <div class="full-width">
                <label for="address">Residential Address</label>
                <textarea id="address" name="address" rows="2">{{ old('address', $registration->address) }}</textarea>
            </div>

            <div class="full-width" style="margin-top: 1rem; border-top: 1px solid var(--edge); padding-top: 1rem;">
                <h3 style="margin-bottom: 0.25rem;">3. Guardian Contact</h3>
            </div>

            <div>
                <label for="guardian_name">Guardian Full Name <span style="color: #ef4444;">*</span></label>
                <input id="guardian_name" name="guardian_name" value="{{ old('guardian_name', $registration->guardian_name) }}" required>
            </div>

            <div>
                <label for="guardian_phone">Guardian Phone <span style="color: #ef4444;">*</span></label>
                <input id="guardian_phone" name="guardian_phone" value="{{ old('guardian_phone', $registration->guardian_phone) }}" required>
            </div>

            <div>
                <label for="guardian_email">Guardian Email</label>
                <input type="email" id="guardian_email" name="guardian_email" value="{{ old('guardian_email', $registration->guardian_email) }}">
            </div>

            <div>
                <label for="guardian_relationship">Relationship</label>
                <input id="guardian_relationship" name="guardian_relationship" value="{{ old('guardian_relationship', $registration->guardian_relationship) }}">
            </div>

            <div class="full-width" style="margin-top: 1rem; border-top: 1px solid var(--edge); padding-top: 1rem;">
                <h3 style="margin-bottom: 0.25rem;">4. Status & Review</h3>
            </div>

            <div>
                <label for="status">Registration Status <span style="color: #ef4444;">*</span></label>
                <select id="status" name="status" required>
                    <option value="pending" @selected(old('status', $registration->status) === 'pending')>Pending Review</option>
                    <option value="approved" @selected(old('status', $registration->status) === 'approved')>Approved</option>
                    <option value="enrolled" @selected(old('status', $registration->status) === 'enrolled')>Enrolled</option>
                    <option value="rejected" @selected(old('status', $registration->status) === 'rejected')>Rejected</option>
                </select>
            </div>

            <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 1.5rem;">
                <input type="checkbox" id="application_fee_paid" name="application_fee_paid" value="1" @checked(old('application_fee_paid', $registration->application_fee_paid))>
                <label for="application_fee_paid" style="margin: 0; font-weight: 500;">Application fee received</label>
            </div>

            <div class="full-width">
                <label for="notes">Administrative Notes</label>
                <textarea id="notes" name="notes" rows="2">{{ old('notes', $registration->notes) }}</textarea>
            </div>

            <div class="full-width" style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem;">
                <a href="{{ route('registrations.show', $registration) }}" class="secondary-link">Cancel</a>
                <button type="submit" class="primary-button">Update application</button>
            </div>
        </form>
    </section>
@endsection
