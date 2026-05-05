@extends('layouts.guest')
@section('title', 'Verify OTP — ComplianceSys')

@section('content')
    <h1 class="auth-title">Verify your email</h1>
    <p class="auth-subtitle">We have sent a 6-digit OTP to your email address.</p>

    @if(session('error'))
        <div class="alert alert-danger mb-3">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('otp.verify.post') }}">
        @csrf

        <div class="mb-3">
            <label for="otp" class="form-label">Enter OTP</label>
            <input id="otp" type="text" class="form-control @error('otp') is-invalid @enderror" name="otp" required autofocus placeholder="123456" maxlength="6">
            @error('otp')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn bg-gradient-primary w-100 py-2 mt-3 ui-auth-btn">
            Verify Email
        </button>
    </form>
@endsection
