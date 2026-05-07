@extends('layouts.guest')
@section('title', 'Reset Password — ComplianceSys')

@section('content')
    <h1 class="auth-title">Reset Password</h1>
    <p class="auth-subtitle">Choose a new password for your account.</p>

    <a href="{{ route('login') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="bi bi-arrow-left"></i> Back to Login
    </a>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ session('reset_email') }}" required readonly autocomplete="username">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">New Password</label>
            <div class="input-group auth-password-group">
                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="Min. 8 characters">
                <button class="btn btn-outline-secondary toggle-password auth-password-toggle" type="button" data-target="password">
                    <i class="bi bi-eye"></i>
                </button>
                @error('password')<div class="invalid-feedback" style="display:block;">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <div class="input-group auth-password-group">
                <input id="password_confirmation" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password" placeholder="Re-enter password">
                <button class="btn btn-outline-secondary toggle-password auth-password-toggle" type="button" data-target="password_confirmation">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="btn bg-gradient-primary w-100 py-2 mt-3 ui-auth-btn">
            Reset Password
        </button>
    </form>

<script>
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    });
</script>
@endsection
