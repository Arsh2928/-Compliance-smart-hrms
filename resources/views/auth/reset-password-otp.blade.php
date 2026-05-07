@extends('layouts.guest')
@section('title', 'Verify OTP — ComplianceSys')

@section('content')
    <h1 class="auth-title">Verify OTP</h1>
    <p class="auth-subtitle">We have sent a 6-digit code to {{ session('reset_email') }}.</p>

    @if(session('status'))
        <div class="alert alert-success mb-3">{{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mb-3">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('password.otp.verify') }}">
        @csrf

        <div class="mb-3">
            <label for="otp" class="form-label">6-Digit Code</label>
            <input id="otp" type="text" class="form-control text-center @error('otp') is-invalid @enderror" name="otp" required autofocus placeholder="------" maxlength="6" style="letter-spacing: 0.5rem; font-size: 1.5rem;">
            @error('otp')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn bg-gradient-primary w-100 py-2 mt-3 ui-auth-btn">
            Verify Code
        </button>
    </form>

    <div class="mt-3 text-center">
        <form method="POST" action="{{ route('password.otp.resend') }}">
            @csrf
            <button type="submit" class="btn btn-link text-primary p-0 m-0 align-baseline">
                Didn't receive the code? Resend OTP
            </button>
        </form>
    </div>

    <div class="mt-4 text-center">
        <a href="{{ route('login') }}" class="text-sm font-weight-bold text-secondary text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i> Back to Login
        </a>
    </div>
@endsection
