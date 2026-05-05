@extends('layouts.guest')
@section('title', 'Create Account — ComplianceSys')

@section('content')
<h1 class="auth-title">Create account</h1>
<p class="auth-subtitle">Set up your compliance workspace access</p>

@if($errors->any())
    <div class="alert alert-danger mb-3" style="font-size:0.82rem; border-radius:10px; padding:0.7rem 1rem;">
        {{ $errors->first() }}
    </div>
@endif

<form method="POST" action="{{ route('register') }}">
    @csrf

    <div class="mb-3">
        <label for="name" class="form-label">Full Name</label>
        <input id="name" type="text"
               class="form-control @error('name') is-invalid @enderror"
               name="name" value="{{ old('name') }}" required autofocus
               placeholder="Jane Smith">
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Work Email</label>
        <input id="email" type="email"
               class="form-control @error('email') is-invalid @enderror"
               name="email" value="{{ old('email') }}" required
               placeholder="you@company.com">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input id="password" type="password"
               class="form-control @error('password') is-invalid @enderror"
               name="password" required autocomplete="new-password"
               placeholder="Min. 8 characters">
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-4">
        <label for="password_confirmation" class="form-label">Confirm Password</label>
        <input id="password_confirmation" type="password"
               class="form-control"
               name="password_confirmation" required placeholder="Re-enter password">
    </div>

    <button type="submit" id="register-btn" class="btn btn-primary w-100 py-2 mb-4" style="font-size:0.9rem;">
        <i class="bi bi-person-check me-2"></i> Create Account
    </button>

    <div class="text-center" style="font-size:0.82rem;color:#64748b;">
        Already have an account?
        <a href="{{ route('login') }}" style="color:#d97706;font-weight:600;text-decoration:none;">
            Sign in →
        </a>
    </div>
</form>

{{-- Helpful note for pre-created employees --}}
<div class="mt-3 p-3 rounded" style="background:rgba(250,204,21,0.1);border:1px solid rgba(250,204,21,0.3);font-size:0.78rem;color:#92400e;">
    <i class="bi bi-info-circle me-1"></i>
    If your account was created by an admin, use your work email to activate it here.
</div>
@endsection
