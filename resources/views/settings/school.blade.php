@extends('layouts.app')

@section('title', 'School Settings')

@section('content')
<section class="page-intro">
    <div>
        <p class="eyebrow">Administration</p>
        <h1>School Settings</h1>
        <p class="intro-copy">Manage your school profile, notifications, and system backup.</p>
    </div>
</section>

{{-- Settings Tabs Navigation --}}
<div class="settings-tabs-wrapper">
    <nav class="settings-tabs" aria-label="Settings sections">
        <a href="{{ route('settings.school.edit', ['tab' => 'profile']) }}"
           class="settings-tab {{ $tab === 'profile' ? 'active' : '' }}"
           id="tab-profile" aria-controls="panel-profile">
            <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            School Profile
        </a>
        <a href="{{ route('settings.school.edit', ['tab' => 'notifications']) }}"
           class="settings-tab {{ $tab === 'notifications' ? 'active' : '' }}"
           id="tab-notifications" aria-controls="panel-notifications">
            <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            Notifications
        </a>
        <a href="{{ route('settings.school.edit', ['tab' => 'backup']) }}"
           class="settings-tab {{ $tab === 'backup' ? 'active' : '' }}"
           id="tab-backup" aria-controls="panel-backup">
            <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.75"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
            Backup &amp; Restore
        </a>
    </nav>
</div>

{{-- Flash Messages --}}
@if (session('success'))
    <div class="alert alert-success settings-alert">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        {{ session('success') }}
    </div>
@endif
@if (session('error'))
    <div class="alert alert-error settings-alert">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        {{ session('error') }}
    </div>
@endif
@if ($errors->any())
    <div class="alert alert-error settings-alert">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <ul class="m-0 pl-4">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- ─────────────────────── TAB: SCHOOL PROFILE ─────────────────────── --}}
@if ($tab === 'profile')
<section class="content-card settings-card" id="panel-profile" aria-labelledby="tab-profile">
    <div class="settings-section-header">
        <h2>School Profile</h2>
        <p>Your school's official identity and contact information.</p>
    </div>

    <form method="POST" action="{{ route('settings.school.update') }}" enctype="multipart/form-data" class="settings-form">
        @csrf
        @method('PUT')
        <input type="hidden" name="_tab" value="profile">

        {{-- Logo Upload --}}
        <div class="full-width">
            <div class="logo-upload-area">
                <div class="logo-preview" id="logo-preview">
                    @if ($settings?->logo_path)
                        <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="School logo" id="logo-img">
                    @else
                        <div class="logo-placeholder" id="logo-placeholder">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                            <span>School Logo</span>
                        </div>
                    @endif
                </div>
                <div class="logo-upload-controls">
                    <label for="logo" class="logo-upload-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        Upload Logo
                    </label>
                    <input type="file" id="logo" name="logo" accept="image/*" class="sr-only" onchange="previewLogo(this)">
                    <p class="upload-hint">PNG, JPG, SVG or GIF up to 2 MB. Recommended: 200×200px square.</p>
                </div>
            </div>
        </div>

        {{-- Basic info --}}
        <div class="form-group">
            <label for="school_name">Official school name <span class="required">*</span></label>
            <input id="school_name" name="school_name" value="{{ old('school_name', $settings?->school_name) }}" maxlength="191" autocomplete="organization" required>
        </div>
        <div class="form-group">
            <label for="school_code">School code</label>
            <input id="school_code" name="school_code" value="{{ old('school_code', $settings?->school_code) }}" maxlength="50">
        </div>
        <div class="form-group">
            <label for="school_type">School type</label>
            <select id="school_type" name="school_type">
                <option value="">— Select type —</option>
                @foreach(['Primary', 'Secondary', 'High School', 'Primary & Secondary', 'University', 'College', 'Vocational', 'International', 'Special Needs', 'Other'] as $type)
                    <option value="{{ $type }}" @selected(old('school_type', $settings?->school_type) === $type)>{{ $type }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="established_year">Year established</label>
            <input id="established_year" name="established_year" type="number" min="1800" max="{{ date('Y') }}" value="{{ old('established_year', $settings?->established_year) }}" placeholder="{{ date('Y') }}">
        </div>
        <div class="form-group">
            <label for="motto">School motto</label>
            <input id="motto" name="motto" value="{{ old('motto', $settings?->motto) }}" maxlength="255" placeholder="e.g. Knowledge is Power">
        </div>
        <div class="form-group">
            <label for="website">Website</label>
            <div class="input-with-prefix">
                <span class="input-prefix">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                </span>
                <input id="website" name="website" type="url" value="{{ old('website', $settings?->website) }}" maxlength="191" placeholder="https://yourschool.edu">
            </div>
        </div>

        {{-- Contact --}}
        <div class="form-divider full-width"><span>Contact Information</span></div>

        <div class="form-group">
            <label for="official_email">Official email</label>
            <input id="official_email" type="email" name="official_email" value="{{ old('official_email', $settings?->official_email) }}" maxlength="191" autocomplete="email">
        </div>
        <div class="form-group">
            <label for="official_phone">Official phone</label>
            <input id="official_phone" type="tel" name="official_phone" value="{{ old('official_phone', $settings?->official_phone) }}" maxlength="50" autocomplete="tel">
        </div>
        <div class="form-group full-width">
            <label for="address">School address</label>
            <textarea id="address" name="address" rows="3" maxlength="2000" autocomplete="street-address">{{ old('address', $settings?->address) }}</textarea>
        </div>
        <div class="form-group">
            <label for="timezone">Time zone <span class="required">*</span></label>
            <select id="timezone" name="timezone" required>
                @foreach (timezone_identifiers_list() as $timezone)
                    <option value="{{ $timezone }}" @selected(old('timezone', $settings?->timezone ?? config('app.timezone')) === $timezone)>{{ $timezone }}</option>
                @endforeach
            </select>
        </div>

        {{-- Social links --}}
        <div class="form-divider full-width"><span>Social Media Links</span></div>

        @foreach ([
            'facebook_url'  => ['Facebook', 'M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z'],
            'twitter_url'   => ['Twitter / X', 'M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z'],
            'instagram_url' => ['Instagram', 'M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z M17.5 6.5h.01 M7.55 3.5h8.9A4.05 4.05 0 0 1 20.5 7.55v8.9a4.05 4.05 0 0 1-4.05 4.05h-8.9A4.05 4.05 0 0 1 3.5 16.45v-8.9A4.05 4.05 0 0 1 7.55 3.5z'],
            'youtube_url'   => ['YouTube', 'M22.54 6.42a2.78 2.78 0 0 0-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46A2.78 2.78 0 0 0 1.46 6.42 29 29 0 0 0 1 12a29 29 0 0 0 .46 5.58 2.78 2.78 0 0 0 1.95 1.96C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 0 0 1.95-1.96A29 29 0 0 0 23 12a29 29 0 0 0-.46-5.58z M9.75 15.02l5.75-3.02-5.75-3.02v6.04z'],
        ] as $field => [$label, $icon])
        <div class="form-group">
            <label for="{{ $field }}">{{ $label }}</label>
            <div class="input-with-prefix">
                <span class="input-prefix">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="{{ $icon }}"/></svg>
                </span>
                <input id="{{ $field }}" name="{{ $field }}" type="url" value="{{ old($field, $settings?->$field) }}" maxlength="255" placeholder="https://">
            </div>
        </div>
        @endforeach

        <div class="full-width form-actions">
            <button type="submit" class="primary-button">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save Profile
            </button>
        </div>
    </form>
</section>
@endif

{{-- ─────────────────────── TAB: NOTIFICATIONS ─────────────────────── --}}
@if ($tab === 'notifications')
<section class="content-card settings-card" id="panel-notifications" aria-labelledby="tab-notifications">
    <div class="settings-section-header">
        <h2>Notification Preferences</h2>
        <p>Choose which events trigger system notifications.</p>
    </div>

    <form method="POST" action="{{ route('settings.school.update') }}" class="settings-form">
        @csrf
        @method('PUT')
        <input type="hidden" name="_tab" value="notifications">

        {{-- Must pass all other required fields silently so the single update handler works --}}
        <input type="hidden" name="school_name" value="{{ $settings?->school_name ?? 'School' }}">
        <input type="hidden" name="timezone" value="{{ $settings?->timezone ?? config('app.timezone') }}">

        <div class="full-width">
            <div class="notification-setting-email">
                <label for="notification_email">Send notification alerts to</label>
                <div class="input-with-prefix">
                    <span class="input-prefix">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    </span>
                    <input id="notification_email" name="notification_email" type="email"
                           value="{{ old('notification_email', $settings?->notification_email ?? $settings?->official_email) }}"
                           maxlength="191" placeholder="alerts@yourschool.edu">
                </div>
            </div>
        </div>

        <div class="full-width">
            <div class="notification-list">
                @foreach ([
                    ['notify_new_registration',  'New Student Registration', 'Get notified when a new student registration is submitted.', 'M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2 M16 11h6m-3-3v6 M12 11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5Z'],
                    ['notify_payment_received',  'Payment Received',         'Get notified when a student payment is recorded.', 'M2 10h20 M6 14h4 M2 5h20v14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5z'],
                    ['notify_attendance_alert',  'Attendance Alert',         'Get notified when student attendance falls below threshold.', 'M9 11l3 3L22 4 M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11'],
                    ['notify_exam_results',      'Exam Results Published',   'Get notified when exam or assessment results are published.', 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z M14 2v6h6 M16 13H8M16 17H8M10 9H8'],
                    ['notify_fee_due',           'Fee Due Reminder',         'Get notified when student invoice fees are approaching due date.', 'M12 2v20 M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6'],
                ] as [$field, $title, $desc, $icon])
                <label class="notification-toggle" for="{{ $field }}">
                    <div class="notification-toggle-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path d="{{ $icon }}"/>
                        </svg>
                    </div>
                    <div class="notification-toggle-body">
                        <strong>{{ $title }}</strong>
                        <span>{{ $desc }}</span>
                    </div>
                    <div class="toggle-switch">
                        <input type="checkbox" id="{{ $field }}" name="{{ $field }}" value="1"
                            {{ old($field, $settings?->$field ?? true) ? 'checked' : '' }}>
                        <span class="toggle-track" aria-hidden="true"></span>
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        <div class="full-width form-actions">
            <button type="submit" class="primary-button">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save Preferences
            </button>
        </div>
    </form>
</section>
@endif

{{-- ─────────────────────── TAB: BACKUP & RESTORE ─────────────────────── --}}
@if ($tab === 'backup')
<section class="content-card settings-card" id="panel-backup" aria-labelledby="tab-backup">
    <div class="settings-section-header">
        <h2>Backup &amp; Restore</h2>
        <p>Download a copy of your database or restore from a previous backup.</p>
    </div>

    <div class="backup-grid">

        {{-- Download Backup --}}
        <div class="backup-card">
            <div class="backup-card-icon backup-icon-download">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            </div>
            <h3>Download Backup</h3>
            <p>Export the current database as a <code>.sqlite</code> file. Store it somewhere safe. You can use it to restore your data at any time.</p>
            <div class="backup-meta">
                <span class="backup-meta-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    Today, {{ now()->format('d M Y, H:i') }}
                </span>
                <span class="backup-meta-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
                    SQLite database
                </span>
            </div>
            <a href="{{ route('settings.backup.download') }}" class="primary-button backup-download-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download Backup
            </a>
        </div>

        {{-- Restore --}}
        <div class="backup-card backup-card-restore">
            <div class="backup-card-icon backup-icon-restore">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
            </div>
            <h3>Restore from Backup</h3>
            <p>Upload a previously downloaded <code>.sqlite</code> backup file to restore your data. <strong>Warning:</strong> this will replace your current database. A safety snapshot is created automatically before restoring.</p>

            <div class="restore-warning">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                This action cannot be undone easily. Ensure you have a current backup before restoring.
            </div>

            <form method="POST" action="{{ route('settings.backup.restore') }}" enctype="multipart/form-data" class="restore-form" id="restore-form">
                @csrf
                <div class="restore-drop-zone" id="restore-drop-zone" onclick="document.getElementById('backup_file').click()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <p id="drop-zone-text">Click to select or drag &amp; drop a <strong>.sqlite</strong> backup file</p>
                    <input type="file" id="backup_file" name="backup_file" accept=".sqlite,application/octet-stream" class="sr-only"
                           onchange="updateDropZone(this)">
                </div>
                <button type="submit" class="danger-button restore-btn" id="restore-btn" disabled
                        onclick="return confirm('Are you sure? This will replace your entire database. A backup snapshot will be created automatically.')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                    Restore Database
                </button>
            </form>
        </div>

    </div>
</section>
@endif

@push('styles')
<style>
/* ── Settings tab layout ─────────────────────────────────────────── */
.settings-tabs-wrapper {
    margin: 0 0 1.5rem;
}
.settings-tabs {
    display: flex;
    gap: .25rem;
    border-bottom: 2px solid var(--border-color, #e5e7eb);
    flex-wrap: wrap;
}
.settings-tab {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .65rem 1.1rem;
    font-size: .9rem;
    font-weight: 500;
    color: var(--text-muted, #6b7280);
    text-decoration: none;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    border-radius: 6px 6px 0 0;
    transition: color .15s, border-color .15s, background .15s;
}
.settings-tab svg { width: 1rem; height: 1rem; flex-shrink: 0; }
.settings-tab:hover { color: var(--primary, #4f46e5); background: var(--primary-bg, #eef2ff); }
.settings-tab.active {
    color: var(--primary, #4f46e5);
    border-bottom-color: var(--primary, #4f46e5);
    background: var(--primary-bg, #eef2ff);
}

/* ── Alert ─────────────────────────────────────────────────────────── */
.settings-alert {
    display: flex;
    align-items: flex-start;
    gap: .6rem;
    margin-bottom: 1.25rem;
    padding: .85rem 1rem;
    border-radius: 8px;
    font-size: .9rem;
}
.settings-alert svg { width: 1.1rem; height: 1.1rem; flex-shrink: 0; margin-top: .05rem; }

/* ── Card header ───────────────────────────────────────────────────── */
.settings-section-header {
    margin-bottom: 1.75rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border-color, #e5e7eb);
}
.settings-section-header h2 { margin: 0 0 .25rem; font-size: 1.1rem; }
.settings-section-header p  { margin: 0; color: var(--text-muted, #6b7280); font-size: .88rem; }

/* ── Form grid ─────────────────────────────────────────────────────── */
.settings-form {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.1rem 1.5rem;
}
@media (max-width: 640px) { .settings-form { grid-template-columns: 1fr; } }
.form-group { display: flex; flex-direction: column; gap: .35rem; }
.form-group.full-width, .full-width { grid-column: 1 / -1; }
.form-group label { font-size: .85rem; font-weight: 600; color: var(--text-secondary, #374151); }
.required { color: #ef4444; }
.form-group input,
.form-group select,
.form-group textarea {
    padding: .55rem .75rem;
    border: 1px solid var(--border-color, #d1d5db);
    border-radius: 7px;
    font-size: .9rem;
    background: var(--input-bg, #fff);
    color: var(--text, #111827);
    transition: border-color .15s, box-shadow .15s;
    width: 100%;
    box-sizing: border-box;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--primary, #4f46e5);
    box-shadow: 0 0 0 3px rgba(79,70,229,.12);
}

/* ── Input with prefix icon ────────────────────────────────────────── */
.input-with-prefix { position: relative; display: flex; }
.input-prefix {
    position: absolute;
    left: .7rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted, #9ca3af);
    pointer-events: none;
    display: flex;
    align-items: center;
}
.input-prefix svg { width: 1rem; height: 1rem; }
.input-with-prefix input { padding-left: 2.4rem; width: 100%; }

/* ── Divider labels ────────────────────────────────────────────────── */
.form-divider {
    display: flex;
    align-items: center;
    gap: .75rem;
    color: var(--text-muted, #9ca3af);
    font-size: .78rem;
    font-weight: 600;
    letter-spacing: .05em;
    text-transform: uppercase;
    margin: .5rem 0 -.25rem;
}
.form-divider::before, .form-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border-color, #e5e7eb);
}

/* ── Logo upload ───────────────────────────────────────────────────── */
.logo-upload-area {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 1rem;
    border: 1px dashed var(--border-color, #d1d5db);
    border-radius: 10px;
    background: var(--card-bg, #f9fafb);
}
.logo-preview {
    width: 90px; height: 90px;
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid var(--border-color, #e5e7eb);
    background: var(--input-bg, #fff);
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}
.logo-preview img { width: 100%; height: 100%; object-fit: cover; }
.logo-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: .25rem;
    color: var(--text-muted, #9ca3af);
    font-size: .72rem;
}
.logo-placeholder svg { width: 2rem; height: 2rem; }
.logo-upload-controls { display: flex; flex-direction: column; gap: .5rem; }
.logo-upload-btn {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .45rem .85rem;
    border: 1px solid var(--border-color, #d1d5db);
    border-radius: 6px;
    font-size: .85rem;
    font-weight: 500;
    cursor: pointer;
    background: var(--input-bg, #fff);
    color: var(--text, #374151);
    transition: background .15s, border-color .15s;
}
.logo-upload-btn:hover { background: var(--primary-bg, #eef2ff); border-color: var(--primary, #4f46e5); color: var(--primary, #4f46e5); }
.logo-upload-btn svg { width: .9rem; height: .9rem; }
.upload-hint { font-size: .78rem; color: var(--text-muted, #9ca3af); margin: 0; }

/* ── Form actions ──────────────────────────────────────────────────── */
.form-actions {
    display: flex;
    justify-content: flex-start;
    margin-top: .5rem;
}
.primary-button {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .6rem 1.2rem;
    background: var(--primary, #4f46e5);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: .9rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: background .15s, transform .1s, box-shadow .15s;
}
.primary-button svg { width: 1rem; height: 1rem; }
.primary-button:hover { background: var(--primary-dark, #4338ca); box-shadow: 0 4px 12px rgba(79,70,229,.35); transform: translateY(-1px); }
.danger-button {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .6rem 1.2rem;
    background: #ef4444;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: .9rem;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s, transform .1s, box-shadow .15s;
}
.danger-button:hover:not(:disabled) { background: #dc2626; box-shadow: 0 4px 12px rgba(239,68,68,.35); transform: translateY(-1px); }
.danger-button:disabled { opacity: .5; cursor: not-allowed; }
.danger-button svg { width: 1rem; height: 1rem; }

/* ── Notification toggles ──────────────────────────────────────────── */
.notification-setting-email { margin-bottom: 1.5rem; }
.notification-setting-email label { display: block; font-size: .85rem; font-weight: 600; margin-bottom: .4rem; color: var(--text-secondary, #374151); }
.notification-setting-email input { padding: .55rem .75rem .55rem 2.4rem; border: 1px solid var(--border-color, #d1d5db); border-radius: 7px; font-size: .9rem; width: 100%; box-sizing: border-box; }

.notification-list {
    display: flex;
    flex-direction: column;
    gap: .75rem;
}
.notification-toggle {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.1rem;
    border: 1px solid var(--border-color, #e5e7eb);
    border-radius: 10px;
    cursor: pointer;
    transition: background .15s, border-color .15s;
    background: var(--card-bg, #fff);
}
.notification-toggle:hover { background: var(--primary-bg, #f5f3ff); border-color: var(--primary, #a5b4fc); }
.notification-toggle-icon {
    width: 2.5rem; height: 2.5rem;
    border-radius: 8px;
    background: var(--primary-bg, #eef2ff);
    color: var(--primary, #4f46e5);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.notification-toggle-icon svg { width: 1.2rem; height: 1.2rem; }
.notification-toggle-body { flex: 1; }
.notification-toggle-body strong { display: block; font-size: .9rem; margin-bottom: .2rem; }
.notification-toggle-body span   { font-size: .82rem; color: var(--text-muted, #6b7280); }

/* Toggle switch pill */
.toggle-switch { position: relative; display: inline-flex; flex-shrink: 0; }
.toggle-switch input[type=checkbox] { position: absolute; opacity: 0; width: 0; height: 0; }
.toggle-track {
    display: block;
    width: 42px; height: 24px;
    background: var(--border-color, #d1d5db);
    border-radius: 12px;
    position: relative;
    transition: background .2s;
    cursor: pointer;
}
.toggle-track::after {
    content: '';
    position: absolute;
    left: 3px; top: 3px;
    width: 18px; height: 18px;
    border-radius: 50%;
    background: #fff;
    transition: transform .2s;
    box-shadow: 0 1px 3px rgba(0,0,0,.2);
}
.toggle-switch input:checked + .toggle-track { background: var(--primary, #4f46e5); }
.toggle-switch input:checked + .toggle-track::after { transform: translateX(18px); }

/* ── Backup cards ──────────────────────────────────────────────────── */
.backup-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}
@media (max-width: 700px) { .backup-grid { grid-template-columns: 1fr; } }
.backup-card {
    display: flex;
    flex-direction: column;
    gap: .9rem;
    padding: 1.5rem;
    border: 1px solid var(--border-color, #e5e7eb);
    border-radius: 14px;
    background: var(--card-bg, #fff);
}
.backup-card h3 { margin: 0; font-size: 1rem; }
.backup-card p  { margin: 0; font-size: .87rem; color: var(--text-muted, #6b7280); line-height: 1.55; }
.backup-card code { background: var(--code-bg, #f3f4f6); padding: .1em .4em; border-radius: 4px; font-size: .85em; }

.backup-card-icon {
    width: 3rem; height: 3rem;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.backup-card-icon svg { width: 1.5rem; height: 1.5rem; }
.backup-icon-download { background: #eef2ff; color: #4f46e5; }
.backup-icon-restore  { background: #fff7ed; color: #f59e0b; }

.backup-card-restore { border-color: #fcd34d; }

.backup-meta {
    display: flex;
    flex-wrap: wrap;
    gap: .75rem;
    font-size: .8rem;
    color: var(--text-muted, #6b7280);
}
.backup-meta-item {
    display: flex;
    align-items: center;
    gap: .3rem;
}
.backup-meta-item svg { width: .85rem; height: .85rem; }

.backup-download-btn { align-self: flex-start; }

.restore-warning {
    display: flex;
    align-items: flex-start;
    gap: .5rem;
    padding: .75rem .9rem;
    background: #fffbeb;
    border: 1px solid #fcd34d;
    border-radius: 8px;
    font-size: .83rem;
    color: #92400e;
    line-height: 1.5;
}
.restore-warning svg { width: 1rem; height: 1rem; flex-shrink: 0; margin-top: .1rem; }

.restore-form { display: flex; flex-direction: column; gap: .75rem; margin-top: .25rem; }
.restore-drop-zone {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: .6rem;
    padding: 1.75rem 1rem;
    border: 2px dashed var(--border-color, #d1d5db);
    border-radius: 10px;
    cursor: pointer;
    text-align: center;
    transition: border-color .15s, background .15s;
    color: var(--text-muted, #6b7280);
}
.restore-drop-zone:hover, .restore-drop-zone.drag-over {
    border-color: var(--primary, #4f46e5);
    background: var(--primary-bg, #eef2ff);
    color: var(--primary, #4f46e5);
}
.restore-drop-zone svg { width: 2rem; height: 2rem; }
.restore-drop-zone p { margin: 0; font-size: .85rem; }

.restore-btn { align-self: flex-start; }

.sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); border: 0; }
</style>
@endpush

@push('scripts')
<script>
function previewLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('logo-preview');
            preview.innerHTML = '<img src="' + e.target.result + '" alt="Logo preview" id="logo-img" style="width:100%;height:100%;object-fit:cover">';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function updateDropZone(input) {
    const zone = document.getElementById('restore-drop-zone');
    const text = document.getElementById('drop-zone-text');
    const btn  = document.getElementById('restore-btn');
    if (input.files && input.files[0]) {
        text.innerHTML = '<strong>' + input.files[0].name + '</strong> selected';
        zone.style.borderColor = '#4f46e5';
        zone.style.background  = '#eef2ff';
        btn.disabled = false;
    } else {
        btn.disabled = true;
    }
}

// Drag-and-drop support for restore zone
(function() {
    const zone = document.getElementById('restore-drop-zone');
    if (!zone) return;
    zone.addEventListener('dragover', function(e) {
        e.preventDefault();
        zone.classList.add('drag-over');
    });
    zone.addEventListener('dragleave', function() {
        zone.classList.remove('drag-over');
    });
    zone.addEventListener('drop', function(e) {
        e.preventDefault();
        zone.classList.remove('drag-over');
        const files = e.dataTransfer.files;
        if (files.length) {
            const input = document.getElementById('backup_file');
            input.files = files;
            updateDropZone(input);
        }
    });
})();
</script>
@endpush
@endsection