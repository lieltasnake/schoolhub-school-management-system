@extends('layouts.app')

@section('title', 'School Settings')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">School organization</p>
            <h1>School settings</h1>
            <p class="intro-copy">Manage your school profile and local time zone. SchoolHub branding remains unchanged.</p>
        </div>
    </section>

    <section class="content-card settings-card">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (! $settings)
            <div class="settings-empty-state">
                <h2>Complete your school profile</h2>
                <p>These details are used for school-facing information. They do not change the SchoolHub application name.</p>
            </div>
        @endif

        <form method="POST" action="{{ route('settings.school.update') }}" class="form-grid">
            @csrf
            @method('PUT')

            <div>
                <label for="school_name">Official school name</label>
                <input id="school_name" name="school_name" value="{{ old('school_name', $settings?->school_name) }}" maxlength="191" autocomplete="organization" required>
            </div>
            <div>
                <label for="school_code">School code</label>
                <input id="school_code" name="school_code" value="{{ old('school_code', $settings?->school_code) }}" maxlength="50">
            </div>
            <div>
                <label for="official_email">Official email</label>
                <input id="official_email" type="email" name="official_email" value="{{ old('official_email', $settings?->official_email) }}" maxlength="191" autocomplete="email">
            </div>
            <div>
                <label for="official_phone">Official phone</label>
                <input id="official_phone" type="tel" name="official_phone" value="{{ old('official_phone', $settings?->official_phone) }}" maxlength="50" autocomplete="tel">
            </div>
            <div class="full-width">
                <label for="address">School address</label>
                <textarea id="address" name="address" rows="3" maxlength="2000" autocomplete="street-address">{{ old('address', $settings?->address) }}</textarea>
            </div>
            <div>
                <label for="timezone">School time zone</label>
                <select id="timezone" name="timezone" required>
                    @foreach (timezone_identifiers_list() as $timezone)
                        <option value="{{ $timezone }}" @selected(old('timezone', $settings?->timezone ?? config('app.timezone')) === $timezone)>{{ $timezone }}</option>
                    @endforeach
                </select>
            </div>
            <div class="full-width form-actions">
                <button type="submit" class="primary-button">Save school settings</button>
            </div>
        </form>
    </section>
@endsection