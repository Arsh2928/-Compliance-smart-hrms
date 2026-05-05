@extends('layouts.guest')
@section('title', 'Confirm Password — ComplianceSys')

@section('content')
    <h1 class="auth-title">Confirm Password</h1>
    <p class="auth-subtitle">This is a secure area of the application. Please confirm your password before continuing.</p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="Enter password">
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn bg-gradient-primary w-100 py-2 mt-3 ui-auth-btn">
            Confirm
        </button>
    </form>
@endsection
