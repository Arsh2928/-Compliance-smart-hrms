@extends('layouts.app')

@section('title', 'Edit Employee')

@section('content')
    <div class="mb-4">
@php $authRole = auth()->user()->role; @endphp
        <x-button href="{{ $authRole === 'hr' ? route('hr.employees.index') : route('admin.employees.index') }}" type="secondary" icon="bi bi-arrow-left">Back</x-button>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <x-card title="Edit Details: {{ $employee->user->name }}" icon="bi bi-pencil-square">
                <form action="{{ $authRole === 'hr' ? route('hr.employees.update', $employee) : route('admin.employees.update', $employee) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Full Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $employee->user->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $employee->user->email) }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Employee Code</label>
                            <input type="text" class="form-control" value="{{ $employee->employee_code }}" readonly>
                            <small class="text-muted">Code cannot be changed.</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Department</label>
                            <select name="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id', $employee->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            @error('department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">System Role</label>
                            <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                <option value="employee" {{ old('role', $employee->user->role ?? 'employee') == 'employee' ? 'selected' : '' }}>Employee</option>
                                <option value="hr" {{ old('role', $employee->user->role ?? 'employee') == 'hr' ? 'selected' : '' }}>HR Manager</option>
                                <option value="admin" {{ old('role', $employee->user->role ?? 'employee') == 'admin' ? 'selected' : '' }}>System Admin</option>
                            </select>
                            @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Phone Number</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $employee->user->phone ?? '') }}">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Joined Date</label>
                            <input type="date" class="form-control" value="{{ \Carbon\Carbon::parse($employee->joined_date)->format('Y-m-d') }}" readonly>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Address</label>
                        <textarea name="address" rows="3" class="form-control @error('address') is-invalid @enderror">{{ old('address', $employee->user->address ?? '') }}</textarea>
                        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-grid">
                        <x-button type="primary" icon="bi bi-save">Update Employee</x-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
@endsection
