@extends('layouts.guest')
@section('title', 'Sign In — ComplianceSys')

@section('content')
<h1 class="auth-title">Welcome back</h1>
<p class="auth-subtitle">Sign in to your compliance workspace</p>

@if(session('status'))
    <div class="alert alert-success mb-3">{{ session('status') }}</div>
@endif

<form method="POST" action="{{ route('login') }}">
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
        <input id="password" type="password"
               class="form-control @error('password') is-invalid @enderror"
               name="password" required placeholder="••••••••">
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="form-check mb-0">
            <input type="checkbox" class="form-check-input" id="remember_me" name="remember">
            <label class="form-check-label" for="remember_me" style="font-size:0.82rem;">Remember me</label>
        </div>
        @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" style="font-size:0.82rem;color:#d97706;text-decoration:none;font-weight:500;">
                Forgot password?
            </a>
        @endif
    </div>

    <button type="submit" id="login-btn" class="btn btn-primary w-100 py-2 mb-4" style="font-size:0.9rem;">
        <i class="bi bi-box-arrow-in-right me-2"></i> Sign In to Dashboard
    </button>

    <div class="text-center" style="font-size:0.82rem;color:#64748b;">
        Don't have an account?
        <a href="{{ route('register') }}" style="color:#d97706;font-weight:600;text-decoration:none;">
            Create one free →
        </a>
    </div>
</form>
@endsection
