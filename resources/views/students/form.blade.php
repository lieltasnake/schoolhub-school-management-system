@extends('layouts.app')

@section('title', $student ? 'Edit Student Profile' : 'Register New Student')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">People / Students</p>
            <h1>{{ $student ? "Edit {$student->full_name}" : 'Student registration' }}</h1>
            <p class="intro-copy">Enter student personal information, academic placement, and guardian contact details.</p>
        </div>
        <a href="{{ $student ? route('students.show', $student) : route('students.index') }}" class="secondary-link">Cancel</a>
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

        <form method="POST" action="{{ $student ? route('students.update', $student) : route('students.store') }}" class="form-grid">
            @csrf
            @if ($student)
                @method('PUT')
            @endif

            <!-- 1. School & Campus Placement -->
            <div class="full-width">
                <h3 style="margin-bottom: 0.25rem;">1. School & Campus Placement</h3>
                <p style="color: var(--muted, #64748b); font-size: 0.875rem;">Assign student to a campus and academic tier.</p>
            </div>

            @if ($student)
                <div>
                    <label>Campus</label>
                    <input type="text" value="{{ $student->branch->name }}" disabled>
                    <input type="hidden" name="branch_id" value="{{ $student->branch_id }}">
                </div>
            @else
                <div>
                    <label for="branch_id">Campus <span style="color: #ef4444;">*</span></label>
                    <select id="branch_id" name="branch_id" required>
                        <option value="">Choose campus</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected((string) old('branch_id', $selectedBranchId) === (string) $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label for="admission_number">Admission Number</label>
                <input id="admission_number" name="admission_number" value="{{ old('admission_number', $student?->admission_number ?? $generatedAdmissionNumber) }}" placeholder="Leave blank to auto-generate">
                <small style="color: var(--muted, #64748b);">Auto-generated if left blank</small>
            </div>

            <div>
                <label for="academic_year_id">Academic Year</label>
                <select id="academic_year_id" name="academic_year_id">
                    <option value="">Select academic year</option>
                    @foreach ($academicYears as $year)
                        <option value="{{ $year->id }}" @selected((string) old('academic_year_id', $currentEnrollment?->academic_year_id) === (string) $year->id)>{{ $year->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="grade_id">Grade / Class <span style="color: #ef4444;">*</span></label>
                <select id="grade_id" name="grade_id" required>
                    <option value="">Select grade</option>
                    @foreach ($grades as $grade)
                        <option value="{{ $grade->id }}" data-branch="{{ $grade->branch_id }}" @selected((string) old('grade_id', $currentEnrollment?->grade_id) === (string) $grade->id)>{{ $grade->name }} ({{ $grade->code }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="section_id">Section / Classroom</label>
                <select id="section_id" name="section_id">
                    <option value="">Select section</option>
                    @foreach ($sections as $section)
                        <option value="{{ $section->id }}" data-grade="{{ $section->grade_id }}" @selected((string) old('section_id', $currentEnrollment?->section_id) === (string) $section->id)>{{ $section->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="roll_number">Roll / Class Number (optional)</label>
                <input id="roll_number" name="roll_number" value="{{ old('roll_number', $currentEnrollment?->roll_number) }}" placeholder="e.g. 15">
            </div>

            <div>
                <label for="student_type_id">Student Type / Category</label>
                <select id="student_type_id" name="student_type_id">
                    <option value="">Standard / General</option>
                    @if (isset($studentTypes))
                        @foreach ($studentTypes as $type)
                            <option value="{{ $type->id }}" @selected((string) old('student_type_id', $student?->student_type_id) === (string) $type->id)>{{ $type->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>

            <!-- 2. Personal Information -->
            <div class="full-width" style="margin-top: 1rem; border-top: 1px solid var(--border, #334155); padding-top: 1rem;">
                <h3 style="margin-bottom: 0.25rem;">2. Personal Information</h3>
                <p style="color: var(--muted, #64748b); font-size: 0.875rem;">Official student identification.</p>
            </div>

            <div>
                <label for="first_name">First Name <span style="color: #ef4444;">*</span></label>
                <input id="first_name" name="first_name" value="{{ old('first_name', $student?->first_name) }}" required placeholder="e.g. Abebe">
            </div>

            <div>
                <label for="middle_name">Middle / Father's Name</label>
                <input id="middle_name" name="middle_name" value="{{ old('middle_name', $student?->middle_name) }}" placeholder="e.g. Kebede">
            </div>

            <div>
                <label for="last_name">Last / Grandfather's Name <span style="color: #ef4444;">*</span></label>
                <input id="last_name" name="last_name" value="{{ old('last_name', $student?->last_name) }}" required placeholder="e.g. Tesfaye">
            </div>

            <div>
                <label for="gender">Gender <span style="color: #ef4444;">*</span></label>
                <select id="gender" name="gender" required>
                    <option value="">Select gender</option>
                    <option value="male" @selected(old('gender', $student?->gender) === 'male')>Male</option>
                    <option value="female" @selected(old('gender', $student?->gender) === 'female')>Female</option>
                    <option value="other" @selected(old('gender', $student?->gender) === 'other')>Other</option>
                </select>
            </div>

            <div>
                <label for="date_of_birth">Date of Birth <span style="color: #ef4444;">*</span></label>
                <input id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth', $student?->date_of_birth?->format('Y-m-d')) }}" required>
            </div>

            <div>
                <label for="admission_date">Admission Date <span style="color: #ef4444;">*</span></label>
                <input id="admission_date" type="date" name="admission_date" value="{{ old('admission_date', $student?->admission_date?->format('Y-m-d') ?? date('Y-m-d')) }}" required>
            </div>

            <div>
                <label for="blood_group">Blood Group</label>
                <select id="blood_group" name="blood_group">
                    <option value="">Unknown / None</option>
                    @foreach (['A+', 'B+', 'O+', 'AB+', 'A-', 'B-', 'O-', 'AB-'] as $bg)
                        <option value="{{ $bg }}" @selected(old('blood_group', $student?->blood_group) === $bg)>{{ $bg }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="religion">Religion</label>
                <input id="religion" name="religion" value="{{ old('religion', $student?->religion) }}" placeholder="e.g. Orthodox, Protestant, Islam">
            </div>

            <div>
                <label for="national_id">National ID / Birth Cert No</label>
                <input id="national_id" name="national_id" value="{{ old('national_id', $student?->national_id) }}" placeholder="e.g. BC-123456">
            </div>

            <div>
                <label for="status">Enrollment Status <span style="color: #ef4444;">*</span></label>
                <select id="status" name="status" required>
                    @foreach (['active', 'inactive', 'graduated', 'suspended', 'transferred'] as $stat)
                        <option value="{{ $stat }}" @selected(old('status', $student?->status ?? 'active') === $stat)>{{ ucfirst($stat) }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 3. Contact & Address -->
            <div class="full-width" style="margin-top: 1rem; border-top: 1px solid var(--border, #334155); padding-top: 1rem;">
                <h3 style="margin-bottom: 0.25rem;">3. Contact & Address</h3>
                <p style="color: var(--muted, #64748b); font-size: 0.875rem;">Residential and communication details.</p>
            </div>

            <div>
                <label for="email">Student Email (optional)</label>
                <input id="email" type="email" name="email" value="{{ old('email', $student?->email) }}" placeholder="student@example.com">
            </div>

            <div>
                <label for="phone">Student Mobile (optional)</label>
                <input id="phone" name="phone" value="{{ old('phone', $student?->phone) }}" placeholder="+251 91 123 4567">
            </div>

            <div>
                <label for="city">City / Sub-city</label>
                <input id="city" name="city" value="{{ old('city', $student?->city) }}" placeholder="e.g. Addis Ababa / Bole">
            </div>

            <div class="full-width">
                <label for="address">Full Street Address</label>
                <input id="address" name="address" value="{{ old('address', $student?->address) }}" placeholder="House #, Woreda, Street">
            </div>

            <!-- 4. Primary Guardian Information -->
            <div class="full-width" style="margin-top: 1rem; border-top: 1px solid var(--border, #334155); padding-top: 1rem;">
                <h3 style="margin-bottom: 0.25rem;">4. Primary Guardian Information</h3>
                <p style="color: var(--muted, #64748b); font-size: 0.875rem;">Contact person for tuition notices, attendance alerts, and emergencies.</p>
            </div>

            <div>
                <label for="guardian_first_name">Guardian First Name @unless($student) <span style="color: #ef4444;">*</span> @endunless</label>
                <input id="guardian_first_name" name="guardian_first_name" value="{{ old('guardian_first_name', $primaryGuardian?->first_name) }}" @unless($student) required @endunless placeholder="e.g. Kebede">
            </div>

            <div>
                <label for="guardian_last_name">Guardian Last Name @unless($student) <span style="color: #ef4444;">*</span> @endunless</label>
                <input id="guardian_last_name" name="guardian_last_name" value="{{ old('guardian_last_name', $primaryGuardian?->last_name) }}" @unless($student) required @endunless placeholder="e.g. Tesfaye">
            </div>

            <div>
                <label for="guardian_phone">Guardian Phone Number @unless($student) <span style="color: #ef4444;">*</span> @endunless</label>
                <input id="guardian_phone" name="guardian_phone" value="{{ old('guardian_phone', $primaryGuardian?->phone) }}" @unless($student) required @endunless placeholder="+251 91 234 5678">
            </div>

            <div>
                <label for="guardian_relationship">Relationship to Student</label>
                <select id="guardian_relationship" name="guardian_relationship">
                    @php $rel = old('guardian_relationship', $student?->guardians->first()?->pivot->relationship ?? 'father'); @endphp
                    <option value="father" @selected($rel === 'father')>Father</option>
                    <option value="mother" @selected($rel === 'mother')>Mother</option>
                    <option value="legal_guardian" @selected($rel === 'legal_guardian')>Legal Guardian</option>
                    <option value="relative" @selected($rel === 'relative')>Relative</option>
                    <option value="other" @selected($rel === 'other')>Other</option>
                </select>
            </div>

            <div>
                <label for="guardian_email">Guardian Email</label>
                <input id="guardian_email" type="email" name="guardian_email" value="{{ old('guardian_email', $primaryGuardian?->email) }}" placeholder="parent@example.com">
            </div>

            <div>
                <label for="guardian_occupation">Guardian Occupation</label>
                <input id="guardian_occupation" name="guardian_occupation" value="{{ old('guardian_occupation', $primaryGuardian?->occupation) }}" placeholder="e.g. Engineer, Doctor">
            </div>

            <!-- 5. Emergency & Health -->
            <div class="full-width" style="margin-top: 1rem; border-top: 1px solid var(--border, #334155); padding-top: 1rem;">
                <h3 style="margin-bottom: 0.25rem;">5. Emergency & Medical Information</h3>
                <p style="color: var(--muted, #64748b); font-size: 0.875rem;">Secondary emergency contacts and medical precautions.</p>
            </div>

            <div>
                <label for="emergency_contact_name">Emergency Contact Name</label>
                <input id="emergency_contact_name" name="emergency_contact_name" value="{{ old('emergency_contact_name', $student?->emergency_contact_name) }}" placeholder="Alternate contact name">
            </div>

            <div>
                <label for="emergency_contact_phone">Emergency Contact Phone</label>
                <input id="emergency_contact_phone" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $student?->emergency_contact_phone) }}" placeholder="+251 91 345 6789">
            </div>

            <div class="full-width">
                <label for="medical_notes">Medical / Dietary Notes</label>
                <textarea id="medical_notes" name="medical_notes" rows="3" placeholder="Allergies, chronic conditions, special accommodations...">{{ old('medical_notes', $student?->medical_notes) }}</textarea>
            </div>

            <div class="full-width form-actions">
                <button type="submit" class="primary-button">{{ $student ? 'Save student profile' : 'Complete registration' }}</button>
            </div>
        </form>
    </section>
@endsection
