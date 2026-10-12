<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>SchoolHub | Sign in</title>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="auth-page">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-brand">
                    <span class="brand-mark" aria-hidden="true">S</span>
                    <span>
                        <strong>SchoolHub</strong>
                        <small>School Management System</small>
                    </span>
                </div>
                <h1>Welcome back</h1>
                <p>Sign in to continue to the administration dashboard.</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-error">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="auth-form">
                @csrf

                <label for="email">Email address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>

                <label for="password">Password</label>
                <input id="password" type="password" name="password" required>

                <div class="auth-actions">
                    <label class="remember-me">
                        <input type="checkbox" name="remember" value="1">
                        <span>Remember me</span>
                    </label>
                    <button type="submit">Login</button>
                </div>
            </form>
        </div>
    </body>
</html>
