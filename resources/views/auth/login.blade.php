@extends('layouts.guest')
@section('title', 'Sign In - ComplianceSys')

@section('content')
<h1 class="auth-title">Welcome back</h1>
<p class="auth-subtitle">Sign in to manage attendance, payroll, alerts, and compliance records.</p>

@if(session('status'))
    <div class="alert alert-success mb-3">{{ session('status') }}</div>
@endif

<form method="POST" action="{{ route('login') }}" class="auth-form">
    @csrf

    <div class="mb-3">
        <label for="email" class="form-label">Email Address</label>
        <input id="email" type="email"
               class="form-control @error('email') is-invalid @enderror"
               name="email" value="{{ old('email') }}" required autofocus
               placeholder="you@company.com">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <div class="input-group auth-password-group">
            <input id="password" type="password"
                   class="form-control @error('password') is-invalid @enderror"
                   name="password" required placeholder="Enter your password">
            <button class="btn btn-outline-secondary auth-password-toggle" type="button" id="togglePassword" aria-label="Show password">
                <i class="bi bi-eye"></i>
            </button>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="d-flex align-items-center justify-content-between mb-4 gap-3">
        <div class="form-check mb-0">
            <input type="checkbox" class="form-check-input" id="remember_me" name="remember">
            <label class="form-check-label ui-auth-small" for="remember_me">Remember me</label>
        </div>
        @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="ui-auth-link ui-auth-small">
                Forgot password?
            </a>
        @endif
    </div>

    <button type="submit" id="login-btn" class="btn bg-gradient-primary w-100 py-2 mb-4 ui-auth-btn">
        <i class="bi bi-box-arrow-in-right me-2"></i> Sign In to Dashboard
    </button>

    <div class="text-center ui-auth-small text-secondary">
        Don't have an account?
        <a href="{{ route('register') }}" class="ui-auth-link fw-bold">
            Create one free
        </a>
    </div>
</form>

<script>
    document.getElementById('togglePassword').addEventListener('click', function () {
        const password = document.getElementById('password');
        const icon = this.querySelector('i');
        if (password.type === 'password') {
            password.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            password.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    });
</script>
@endsection
