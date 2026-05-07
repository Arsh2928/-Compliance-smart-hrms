@extends('layouts.app')

@section('title', 'Generate Payroll')

@section('content')
    <div class="mb-4">
        <x-button href="{{ route('admin.payrolls.index') }}" type="secondary" icon="bi bi-arrow-left">Back</x-button>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <x-card title="Create Payroll Record" icon="bi bi-cash-stack">
                <form action="{{ route('admin.payrolls.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Employee</label>
                        <select name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                            <option value="">Choose Employee...</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->employee_code }} - {{ $emp->user->name ?? 'Unknown' }}
                                </option>
                            @endforeach
                        </select>
                        @error('employee_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Month</label>
                            <select name="month" class="form-select" required>
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ date('n') == $i ? 'selected' : '' }}>{{ DateTime::createFromFormat('!m', $i)->format('F') }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Year</label>
                            <input type="number" name="year" class="form-control" value="{{ date('Y') }}" required min="2020" max="2099">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Basic Salary (₹)</label>
                            <input type="number" step="0.01" name="basic_salary" class="form-control @error('basic_salary') is-invalid @enderror" value="{{ old('basic_salary') }}" required>
                            @error('basic_salary') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Deductions (₹)</label>
                            <input type="number" step="0.01" name="deductions" class="form-control @error('deductions') is-invalid @enderror" value="{{ old('deductions', 0) }}">
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Overtime Hours</label>
                            <input type="number" step="0.1" name="overtime_hours" class="form-control @error('overtime_hours') is-invalid @enderror" value="{{ old('overtime_hours', 0) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Overtime Pay (₹)</label>
                            <input type="number" step="0.01" name="overtime_pay" class="form-control @error('overtime_pay') is-invalid @enderror" value="{{ old('overtime_pay', 0) }}">
                        </div>
                    </div>

                    <input type="hidden" name="status" value="pending">

                    <div class="d-grid">
                        <x-button type="primary" icon="bi bi-save">Generate Payroll</x-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const empSelect = document.querySelector('select[name="employee_id"]');
    const monthSelect = document.querySelector('select[name="month"]');
    const yearInput = document.querySelector('input[name="year"]');
    
    const basicInput = document.querySelector('input[name="basic_salary"]');
    const deductionsInput = document.querySelector('input[name="deductions"]');
    const otHoursInput = document.querySelector('input[name="overtime_hours"]');
    const otPayInput = document.querySelector('input[name="overtime_pay"]');

    function calculatePayroll() {
        const empId = empSelect.value;
        const month = monthSelect.value;
        const year = yearInput.value;

        if (!empId || !month || !year) return;

        // Route URL works for both admin and HR as defined in web.php
        const rolePrefix = window.location.pathname.startsWith('/hr/') ? 'hr' : 'admin';
        const url = `/${rolePrefix}/payrolls/calculate?employee_id=${empId}&month=${month}&year=${year}`;

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.error) return;
                
                // If the user hasn't manually overridden things, we update it
                basicInput.value = data.basic_salary;
                deductionsInput.value = data.deductions;
                otHoursInput.value = data.overtime_hours;
                otPayInput.value = data.overtime_pay;
            })
            .catch(err => console.error('Error fetching payroll calculation:', err));
    }

    empSelect.addEventListener('change', calculatePayroll);
    monthSelect.addEventListener('change', calculatePayroll);
    yearInput.addEventListener('change', calculatePayroll);
});
</script>
@endpush
