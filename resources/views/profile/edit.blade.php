@extends('layouts.app')
@section('title', 'Profile Settings')

@section('content')
<div class="profile-shell">
    <div class="profile-hero">
        <div>
            <span class="profile-eyebrow">Account Center</span>
            <h4><i class="bi bi-person-circle me-2"></i>Profile Settings</h4>
            <p>Manage your account identity, contact details, and sign-in security.</p>
        </div>
        <div class="profile-hero-badge">
            <i class="bi bi-shield-check"></i>
            <span>{{ ucfirst($user->role) }} access</span>
        </div>
    </div>

    <div class="row g-4 align-items-start">
        <div class="col-xl-4 col-lg-5">
            <div class="card profile-card">
                <div class="profile-card-glow"></div>
                <div class="card-body">
                    <div class="profile-avatar">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <h5>{{ $user->name }}</h5>
                    <p>{{ $user->email }}</p>
                    @if($user->role === 'admin')
                        <span class="badge bg-danger">System Admin</span>
                    @elseif($user->role === 'hr')
                        <span class="badge bg-warning">HR Manager</span>
                    @else
                        <span class="badge bg-success">Employee</span>
                    @endif

                    @if($user->employee)
                    <div class="profile-meta">
                        <div>
                            <span>Code</span>
                            <strong>{{ $user->employee->employee_code }}</strong>
                        </div>
                        <div>
                            <span>Department</span>
                            <strong>{{ $user->employee->department->name ?? 'N/A' }}</strong>
                        </div>
                        <div>
                            <span>Joined</span>
                            <strong>{{ \Carbon\Carbon::parse($user->employee->joined_date)->format('M d, Y') }}</strong>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7">
            <div class="card profile-form-card mb-4">
                <div class="card-header">
                    <div class="profile-section-title">
                        <span><i class="bi bi-person-fill"></i></span>
                        <div>
                            <h6>Profile Information</h6>
                            <p>Keep your personal details accurate for HR and payroll records.</p>
                        </div>
                    </div>
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
                        <div class="profile-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card profile-form-card">
                <div class="card-header">
                    <div class="profile-section-title profile-section-warning">
                        <span><i class="bi bi-lock-fill"></i></span>
                        <div>
                            <h6>Update Password</h6>
                            <p>Use a strong password to keep your workspace secure.</p>
                        </div>
                    </div>
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
                        <div class="profile-actions">
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-key me-2"></i>Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
