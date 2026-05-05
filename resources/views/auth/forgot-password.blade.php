@extends('layouts.guest')
@section('title', 'Forgot Password — ComplianceSys')

@section('content')
    <h1 class="auth-title">Reset your password</h1>
    <p class="auth-subtitle">Enter your email and we will send you a password reset link.</p>

    <a href="{{ route('login') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="bi bi-arrow-left"></i> Back to Login
    </a>

    @if(session('status'))
        <div class="alert alert-success mb-3">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autofocus placeholder="you@company.com">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn bg-gradient-primary w-100 py-2 mt-3 ui-auth-btn">
            Email Password Reset Link
        </button>
    </form>
@endsection
