<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>SchoolHub | @yield('title', 'School Management System')</title>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="app-body">
        @php
            $currentUser = auth()->user();
        @endphp
        <div class="app-shell">
            <aside class="sidebar" id="school-sidebar" aria-label="Main navigation">
                <a href="{{ route('dashboard') }}" class="brand-block">
                    <span class="brand-mark" aria-hidden="true">S</span>
                    <span class="brand-copy">
                        <span class="brand-title">SchoolHub</span>
                        <span class="brand-subtitle">School Management System</span>
                    </span>
                </a>

                <nav class="nav-links" aria-label="School management">
                    <p class="nav-section-label">Workspace</p>
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" @if (request()->routeIs('dashboard')) aria-current="page" @endif>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="3.5" y="13.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="13.5" width="7" height="7" rx="1.5"/></svg>
                        <span>Dashboard</span>
                    </a>

                    @if ($currentUser && ($currentUser->canManageBranches() || $currentUser->canManageUsers()))
                        <p class="nav-section-label">School organization</p>
                        <a href="{{ route('branches.index') }}" class="nav-link {{ request()->routeIs('branches.*') ? 'active' : '' }}" @if (request()->routeIs('branches.*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3.5 20.5h17M5.5 20.5v-13l6.5-4 6.5 4v13M9 9.5h1m4 0h1M9 13.5h1m4 0h1M10 20.5v-4h4v4"/></svg>
                            <span>Branches / Campuses</span>
                        </a>
                    @endif

                    @if ($currentUser && $currentUser->canManageSchoolSettings())
                        <a href="{{ route('settings.school.edit') }}" class="nav-link {{ request()->routeIs('settings.school.*') ? 'active' : '' }}" @if (request()->routeIs('settings.school.*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="m19.4 15 .1.1a1.8 1.8 0 1 1-2.5 2.5l-.1-.1a1.8 1.8 0 0 0-3 .9v.2a1.8 1.8 0 1 1-3.6 0v-.2a1.8 1.8 0 0 0-3-.9l-.1.1a1.8 1.8 0 1 1-2.5-2.5l.1-.1a1.8 1.8 0 0 0-.9-3h-.2a1.8 1.8 0 1 1 0-3.6h.2a1.8 1.8 0 0 0 .9-3l-.1-.1a1.8 1.8 0 1 1 2.5-2.5l.1.1a1.8 1.8 0 0 0 3-.9v-.2a1.8 1.8 0 1 1 3.6 0v.2a1.8 1.8 0 0 0 3 .9l.1-.1a1.8 1.8 0 1 1 2.5 2.5l-.1.1a1.8 1.8 0 0 0 .9 3h.2a1.8 1.8 0 1 1 0 3.6h-.2a1.8 1.8 0 0 0-.9 3Z"/></svg>
                            <span>School Settings</span>
                        </a>
                    @endif

                    @if ($currentUser && $currentUser->canManageAcademicCalendar())
                        <p class="nav-section-label">School setup</p>
                        <a href="{{ route('academic-years.index') }}" class="nav-link {{ request()->routeIs('academic-years.*') ? 'active' : '' }}" @if (request()->routeIs('academic-years.*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="5.5" width="17" height="15" rx="2"/><path d="M7.5 3.5v4m9-4v4m-13 2h17m-12 4h3m-3 3h6"/></svg>
                            <span>Academic Years</span>
                        </a>
                        <a href="{{ route('terms.index') }}" class="nav-link {{ request()->routeIs('terms.*') ? 'active' : '' }}" @if (request()->routeIs('terms.*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4.5h14v15H5zM8 8h8m-8 4h8m-8 4h5"/></svg>
                            <span>Terms / Semesters</span>
                        </a>
                        <a href="{{ route('grades.index') }}" class="nav-link {{ request()->routeIs('grades.*') ? 'active' : '' }}" @if (request()->routeIs('grades.*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/><path d="M6 6h10M6 10h10M6 14h6"/></svg>
                            <span>Grades / Classes</span>
                        </a>
                        <a href="{{ route('sections.index') }}" class="nav-link {{ request()->routeIs('sections.*') ? 'active' : '' }}" @if (request()->routeIs('sections.*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18M10 4v16"/></svg>
                            <span>Sections / Streams</span>
                        </a>
                        <a href="{{ route('classrooms.index') }}" class="nav-link {{ request()->routeIs('classrooms.*') ? 'active' : '' }}" @if (request()->routeIs('classrooms.*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                            <span>Classrooms</span>
                        </a>
                        <a href="{{ route('periods.index') }}" class="nav-link {{ request()->routeIs('periods.*') ? 'active' : '' }}" @if (request()->routeIs('periods.*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <span>Period Setup</span>
                        </a>
                        <a href="{{ route('school-hours.index') }}" class="nav-link {{ request()->routeIs('school-hours.*') ? 'active' : '' }}" @if (request()->routeIs('school-hours.*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48 2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48 2.83-2.83"/></svg>
                            <span>School Hours</span>
                        </a>
                    @endif

                    @if ($currentUser && ($currentUser->canManageRegistration() || $currentUser->canManageStudents()))
                        <p class="nav-section-label">Registration</p>
                        <a href="{{ route('registrations.index') }}" class="nav-link {{ request()->routeIs('registrations.*') && !request()->routeIs('registrations.promotion*') && !request()->routeIs('registrations.setup*') ? 'active' : '' }}" @if (request()->routeIs('registrations.*') && !request()->routeIs('registrations.promotion*') && !request()->routeIs('registrations.setup*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><path d="M16 11h6m-3-3v6"/></svg>
                            <span>Student Registration</span>
                        </a>
                        <a href="{{ route('registrations.promotion') }}" class="nav-link {{ request()->routeIs('registrations.promotion*') ? 'active' : '' }}" @if (request()->routeIs('registrations.promotion*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m16 6 4 4-4 4M20 10H4M8 18l-4-4 4-4"/></svg>
                            <span>Registration Promotion</span>
                        </a>
                        <a href="{{ route('registrations.setup') }}" class="nav-link {{ request()->routeIs('registrations.setup*') ? 'active' : '' }}" @if (request()->routeIs('registrations.setup*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                            <span>Registration Setup</span>
                        </a>
                    @endif

                    @if ($currentUser && ($currentUser->canViewStudents() || $currentUser->canManageStudents()))
                        <p class="nav-section-label">Students</p>
                        <a href="{{ route('students.index') }}" class="nav-link {{ request()->routeIs('students.*') ? 'active' : '' }}" @if (request()->routeIs('students.*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 14c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5Zm0 2c-3.33 0-10 1.67-10 5v2h20v-2c0-3.33-6.67-5-10-5Z"/></svg>
                            <span>All Students</span>
                        </a>
                        <a href="{{ route('enrollments.index') }}" class="nav-link {{ request()->routeIs('enrollments.*') ? 'active' : '' }}" @if (request()->routeIs('enrollments.*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/><path d="M10 2v20M14 6h3M14 10h3"/></svg>
                            <span>Enrollment</span>
                        </a>
                        <a href="{{ route('student-types.index') }}" class="nav-link {{ request()->routeIs('student-types.*') ? 'active' : '' }}" @if (request()->routeIs('student-types.*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82zM7 7h.01"/></svg>
                            <span>Student Type</span>
                        </a>
                        <a href="{{ route('student-groups.index') }}" class="nav-link {{ request()->routeIs('student-groups.*') ? 'active' : '' }}" @if (request()->routeIs('student-groups.*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            <span>Student Group</span>
                        </a>
                        @if ($currentUser->canManageStudents())
                            <a href="{{ route('grade-promotions.index') }}" class="nav-link {{ request()->routeIs('grade-promotions.*') ? 'active' : '' }}" @if (request()->routeIs('grade-promotions.*')) aria-current="page" @endif>
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2v20M17 7l-5-5-5 5M5 17l7 5 7-5"/></svg>
                                <span>Grade Promotion</span>
                            </a>
                        @endif
                    @endif

                    @if ($currentUser && ($currentUser->canViewStudents() || $currentUser->canManageUsers() || $currentUser->canManageStudents()))
                        <p class="nav-section-label">Teachers</p>
                        <a href="{{ route('teachers.index') }}" class="nav-link {{ request()->routeIs('teachers.*') ? 'active' : '' }}" @if (request()->routeIs('teachers.*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <span>Teacher Management</span>
                        </a>
                        <a href="{{ route('teacher-assignments.index') }}" class="nav-link {{ request()->routeIs('teacher-assignments.*') ? 'active' : '' }}" @if (request()->routeIs('teacher-assignments.*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg>
                            <span>Class & Subject Assign</span>
                        </a>
                        <a href="{{ route('subjects.index') }}" class="nav-link {{ request()->routeIs('subjects.*') ? 'active' : '' }}" @if (request()->routeIs('subjects.*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                            <span>Subjects</span>
                        </a>
                    @endif

                    @if ($currentUser && ($currentUser->canManageAcademicCalendar() || $currentUser->canViewStudents()))
                        <p class="nav-section-label">Timetable</p>
                        <a href="{{ route('timetables.index') }}" class="nav-link {{ request()->routeIs('timetables.*') ? 'active' : '' }}" @if (request()->routeIs('timetables.*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <span>Timetable Schedule</span>
                        </a>
                    @endif

                    @if ($currentUser && ($currentUser->canViewStudents() || $currentUser->canManageStudents()))
                        <p class="nav-section-label">Roster</p>
                        <a href="{{ route('roster.students') }}" class="nav-link {{ request()->routeIs('roster.students*') ? 'active' : '' }}" @if (request()->routeIs('roster.students*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            <span>Student Roster</span>
                        </a>
                        <a href="{{ route('roster.teachers') }}" class="nav-link {{ request()->routeIs('roster.teachers*') ? 'active' : '' }}" @if (request()->routeIs('roster.teachers*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg>
                            <span>Teachers Roster</span>
                        </a>
                        <a href="{{ route('roster.timetable') }}" class="nav-link {{ request()->routeIs('roster.timetable*') ? 'active' : '' }}" @if (request()->routeIs('roster.timetable*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                            <span>Class Timetable</span>
                        </a>
                        <a href="{{ route('roster.grades') }}" class="nav-link {{ request()->routeIs('roster.grades*') ? 'active' : '' }}" @if (request()->routeIs('roster.grades*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            <span>Student Grades</span>
                        </a>
                    @endif

                    @if ($currentUser && ($currentUser->canViewStudents() || $currentUser->canManageStudents()))
                        <p class="nav-section-label">Attendance</p>
                        <a href="{{ route('student-attendance.index') }}" class="nav-link {{ request()->routeIs('student-attendance.*') ? 'active' : '' }}" @if (request()->routeIs('student-attendance.*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                            <span>Student Attendance</span>
                        </a>
                        <a href="{{ route('staff-attendance.index') }}" class="nav-link {{ request()->routeIs('staff-attendance.*') ? 'active' : '' }}" @if (request()->routeIs('staff-attendance.*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="m9 16 2 2 4-4"/></svg>
                            <span>Staff Attendance</span>
                        </a>
                    @endif

                    @if ($currentUser && ($currentUser->canViewStudents() || $currentUser->canManageStudents()))
                        <p class="nav-section-label">Library</p>
                        <a href="{{ route('libraries.index') }}" class="nav-link {{ request()->routeIs('libraries.*') ? 'active' : '' }}" @if (request()->routeIs('libraries.*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                            <span>Libraries</span>
                        </a>
                        <a href="{{ route('books.index') }}" class="nav-link {{ request()->routeIs('books.*') ? 'active' : '' }}" @if (request()->routeIs('books.*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                            <span>Books Catalog</span>
                        </a>
                        <a href="{{ route('book-types.index') }}" class="nav-link {{ request()->routeIs('book-types.*') ? 'active' : '' }}" @if (request()->routeIs('book-types.*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/><line x1="10" y1="6" x2="16" y2="6"/><line x1="10" y1="10" x2="16" y2="10"/></svg>
                            <span>Book Types</span>
                        </a>
                        <a href="{{ route('library-members.index') }}" class="nav-link {{ request()->routeIs('library-members.*') ? 'active' : '' }}" @if (request()->routeIs('library-members.*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><circle cx="8" cy="10" r="2"/><path d="M4 17c0-2 2-3 4-3s4 1 4 3"/><line x1="15" y1="9" x2="19" y2="9"/><line x1="15" y1="13" x2="19" y2="13"/></svg>
                            <span>Members</span>
                        </a>
                        <a href="{{ route('circulations.index') }}" class="nav-link {{ request()->routeIs('circulations.*') ? 'active' : '' }}" @if (request()->routeIs('circulations.*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                            <span>Circulations</span>
                        </a>
                    @endif

                    @if ($currentUser && ($currentUser->canManageExams() || $currentUser->canViewStudents()))
                        <p class="nav-section-label">Exams & Assessments</p>
                        <a href="{{ route('assessments.index') }}" class="nav-link {{ request()->routeIs('assessments.*') ? 'active' : '' }}" @if (request()->routeIs('assessments.*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>
                            <span>Assessment Setup</span>
                        </a>
                        <a href="{{ route('assessment-types.index') }}" class="nav-link {{ request()->routeIs('assessment-types.*') ? 'active' : '' }}" @if (request()->routeIs('assessment-types.*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="m9 12 2 2 4-4"/></svg>
                            <span>Assessment Type</span>
                        </a>
                        <a href="{{ route('grade-scales.index') }}" class="nav-link {{ request()->routeIs('grade-scales.*') ? 'active' : '' }}" @if (request()->routeIs('grade-scales.*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3v18h18M7 16l4-4 4 4 5-6"/></svg>
                            <span>Grade Scale</span>
                        </a>
                        <a href="{{ route('grade-entries.index') }}" class="nav-link {{ request()->routeIs('grade-entries.*') ? 'active' : '' }}" @if (request()->routeIs('grade-entries.*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg>
                            <span>Grade Entry</span>
                        </a>
                    @endif

                    @if ($currentUser && $currentUser->canManageUsers())
                        <p class="nav-section-label">Access & Organization</p>
                        <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" @if (request()->routeIs('users.*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M3.5 20v-1.5a5.5 5.5 0 0 1 11 0V20m2-9.5a3.5 3.5 0 0 0 0-6.8M18 14a4 4 0 0 1 2.5 3.7V20"/></svg>
                            <span>Users and Roles</span>
                        </a>
                    @endif

                    @if ($currentUser && $currentUser->canViewFinance())
                        <p class="nav-section-label">Finance</p>
                        <a href="{{ route('fee-categories.index') }}" class="nav-link {{ request()->routeIs('fee-categories.*') ? 'active' : '' }}" @if (request()->routeIs('fee-categories.*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 10h20M6 15h3m4 0h5"/></svg>
                            <span>Fee Categories</span>
                        </a>
                        <a href="{{ route('fee-structures.index') }}" class="nav-link {{ request()->routeIs('fee-structures.*') ? 'active' : '' }}" @if (request()->routeIs('fee-structures.*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                            <span>Fee Structures</span>
                        </a>
                        <a href="{{ route('fee-discounts.index') }}" class="nav-link {{ request()->routeIs('fee-discounts.*') ? 'active' : '' }}" @if (request()->routeIs('fee-discounts.*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="m9 15 6-6M9 9h.01M15 15h.01"/></svg>
                            <span>Discounts & Waivers</span>
                        </a>
                        <a href="{{ route('invoices.index') }}" class="nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}" @if (request()->routeIs('invoices.*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>
                            <span>Student Invoices</span>
                        </a>
                        <a href="{{ route('payments.index') }}" class="nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}" @if (request()->routeIs('payments.*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20M6 14h4"/></svg>
                            <span>Payments & Receipts</span>
                        </a>
                    @endif

                    @if ($currentUser && $currentUser->canManageRoles())
                        <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}" @if (request()->routeIs('roles.*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3.5 19 7v5c0 4.2-2.9 7-7 8.5C7.9 19 5 16.2 5 12V7l7-3.5Z"/><path d="m9 12 2 2 4-4"/></svg>
                            <span>Roles and Permissions</span>
                        </a>
                    @endif
                </nav>

                <div class="sidebar-footer">
                    <span class="sidebar-footer-dot" aria-hidden="true"></span>
                    <span>SchoolHub workspace</span>
                </div>
            </aside>

            <button class="sidebar-scrim" type="button" aria-label="Close navigation" data-sidebar-close></button>

            <main class="content-panel">
                <header class="topbar">
                    <div class="topbar-leading">
                        <button class="menu-toggle" type="button" aria-label="Open navigation" aria-controls="school-sidebar" aria-expanded="false" data-sidebar-toggle>
                            <span></span><span></span><span></span>
                        </button>
                        <a href="{{ route('dashboard') }}" class="topbar-brand">
                            <span>SchoolHub</span>
                            <small>School Management System</small>
                        </a>
                    </div>

                    <div class="account-actions">
                        @if (isset($accessibleBranches) && $currentUser?->canManageBranches() && $accessibleBranches->count() > 1)
                            <form method="GET" action="{{ route('dashboard') }}" class="branch-switcher">
                                <label class="sr-only" for="dashboard-branch">Campus</label>
                                <select id="dashboard-branch" name="branch_id">
                                    <option value="">All campuses</option>
                                    @foreach ($accessibleBranches as $branchOption)
                                        <option value="{{ $branchOption->id }}" @selected($selectedBranchId === $branchOption->id)>{{ $branchOption->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit">Apply</button>
                            </form>
                        @endif
                        <div class="account-summary">
                            <span class="account-avatar" aria-hidden="true">{{ strtoupper(substr($currentUser?->name ?? 'S', 0, 1)) }}</span>
                            <span class="account-copy">
                                <strong>{{ $currentUser?->name ?? 'SchoolHub user' }}</strong>
                                <small>{{ $currentUser?->role?->name ?? 'User' }}</small>
                            </span>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="logout-button">Sign out</button>
                        </form>
                    </div>
                </header>

                <div class="page-content">
                    @yield('content')
                </div>
            </main>
        </div>
    </body>
</html>
