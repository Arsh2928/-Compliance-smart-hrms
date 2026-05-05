@extends('layouts.app')
@section('title', 'Profile Settings')

@section('content')
<div class="page-header">
    <h4><i class="bi bi-person-circle me-2 text-primary"></i>Profile Settings</h4>
</div>

<div class="row g-4">
    {{-- Avatar Card --}}
    <div class="col-lg-3">
        <div class="card text-center">
            <div class="card-body py-4">
                <div class="topbar-avatar mx-auto mb-3"
                     style="width:72px;height:72px;font-size:2rem;border-radius:50%;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <h6 class="fw-bold mb-1">{{ $user->name }}</h6>
                <p class="text-muted small mb-2">{{ $user->email }}</p>
                @if($user->role === 'admin')
                    <span class="badge bg-danger">System Admin</span>
                @elseif($user->role === 'hr')
                    <span class="badge bg-warning">HR Manager</span>
                @else
                    <span class="badge bg-success">Employee</span>
                @endif

                @if($user->employee)
                <hr>
                <div class="text-start small">
                    <div class="mb-1"><strong>Code:</strong> {{ $user->employee->employee_code }}</div>
                    <div class="mb-1"><strong>Dept:</strong> {{ $user->employee->department->name ?? 'N/A' }}</div>
                    <div><strong>Joined:</strong> {{ \Carbon\Carbon::parse($user->employee->joined_date)->format('M d, Y') }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Forms --}}
    <div class="col-lg-9">
        {{-- Profile Info --}}
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-person-fill me-2 text-primary"></i>Profile Information
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf @method('PATCH')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $user->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user->email) }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Update Password --}}
        <div class="card">
            <div class="card-header">
                <i class="bi bi-lock-fill me-2 text-warning"></i>Update Password
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password"
                                   class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                                   autocomplete="current-password">
                            @error('current_password', 'updatePassword')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password"
                                   class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                                   autocomplete="new-password">
                            @error('password', 'updatePassword')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirmation"
                                   class="form-control" autocomplete="new-password">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-key me-2"></i>Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
