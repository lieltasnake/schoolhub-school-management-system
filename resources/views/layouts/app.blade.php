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
                    @endif

                    @if ($currentUser && $currentUser->canManageUsers())
                        <p class="nav-section-label">People</p>
                        <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" @if (request()->routeIs('users.*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M3.5 20v-1.5a5.5 5.5 0 0 1 11 0V20m2-9.5a3.5 3.5 0 0 0 0-6.8M18 14a4 4 0 0 1 2.5 3.7V20"/></svg>
                            <span>Users and Roles</span>
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
