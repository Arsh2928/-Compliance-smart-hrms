@extends('layouts.app')
@section('title', 'Edit Payroll')

@section('content')
<div class="mb-4">
    <a href="{{ url()->previous() }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h5 class="mb-0 fw-bold"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Payroll</h5>
                <p class="text-muted small mt-1">Update payroll details for {{ $payroll->employee->user->name ?? 'Unknown' }} ({{ DateTime::createFromFormat('!m', $payroll->month)->format('F') }} {{ $payroll->year }})</p>
            </div>
            <div class="card-body">
                @php
                    $updateRoute = auth()->user()->role === 'admin' 
                        ? route('admin.payrolls.update', $payroll) 
                        : route('hr.payrolls.update', $payroll);
                @endphp
                <form action="{{ $updateRoute }}" method="POST">
                    @csrf @method('PUT')

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Basic Salary</label>
                            <input type="number" name="basic_salary" class="form-control @error('basic_salary') is-invalid @enderror" value="{{ old('basic_salary', $payroll->basic_salary) }}" min="0" step="0.01" required>
                            @error('basic_salary') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Overtime Hours</label>
                            <input type="number" name="overtime_hours" class="form-control @error('overtime_hours') is-invalid @enderror" value="{{ old('overtime_hours', $payroll->overtime_hours) }}" min="0" step="0.5">
                            @error('overtime_hours') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Overtime Pay</label>
                            <input type="number" name="overtime_pay" class="form-control @error('overtime_pay') is-invalid @enderror" value="{{ old('overtime_pay', $payroll->overtime_pay) }}" min="0" step="0.01">
                            @error('overtime_pay') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Deductions</label>
                            <input type="number" name="deductions" class="form-control @error('deductions') is-invalid @enderror" value="{{ old('deductions', $payroll->deductions) }}" min="0" step="0.01">
                            @error('deductions') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Status</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="pending" {{ old('status', $payroll->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ old('status', $payroll->status) == 'paid' ? 'selected' : '' }}>Paid</option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
