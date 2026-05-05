@extends('layouts.app')
@section('title', 'Payroll Records')

@section('content')
<div class="page-header">
    <h4><i class="bi bi-cash-coin me-2 text-success"></i>Payroll Records</h4>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ auth()->user()->role === 'admin' ? route('admin.payrolls.downloadAll') : route('hr.payrolls.downloadAll') }}" class="btn btn-outline-primary">
            <i class="bi bi-download me-2"></i>Download All
        </a>
        <a href="{{ auth()->user()->role === 'admin' ? route('admin.payrolls.create') : route('hr.payrolls.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Generate Payroll
        </a>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="table-responsive">
        <table class="table payroll-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Period</th>
                    <th>Basic Salary</th>
                    <th>Overtime</th>
                    <th>Deductions</th>
                    <th>Net Salary</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payrolls as $payroll)
                <tr>
                    <td>
                        <div class="fw-semibold" style="font-size:0.87rem;">
                            {{ $payroll->employee->user->name ?? 'Unknown' }}
                        </div>
                        <div class="text-muted" style="font-size:0.75rem;">
                            {{ $payroll->employee->employee_code ?? '' }}
                        </div>
                    </td>
                    <td class="fw-medium">
                        {{ DateTime::createFromFormat('!m', $payroll->month)->format('F') }}
                        {{ $payroll->year }}
                    </td>
                    <td>{{ \App\Support\Money::inr($payroll->basic_salary) }}</td>
                    <td>{{ \App\Support\Money::inr($payroll->overtime_pay) }}</td>
                    <td class="text-danger">-{{ \App\Support\Money::inr($payroll->deductions) }}</td>
                    <td><strong class="text-success">{{ \App\Support\Money::inr($payroll->net_salary) }}</strong></td>
                    <td>
                        @if($payroll->status === 'paid')
                            <span class="badge bg-success">Paid</span>
                        @else
                            <span class="badge bg-warning">Pending</span>
                        @endif
                    </td>
                    <td class="payroll-actions-cell">
                        @if($payroll->status === 'pending')
                        <div class="payroll-row-actions">
                            <form action="{{ auth()->user()->role === 'admin' ? route('admin.payrolls.update', $payroll) : route('hr.payrolls.update', $payroll) }}" method="POST">
                                @csrf @method('PUT')
                                <input type="hidden" name="status" value="paid">
                                <button type="submit" class="btn btn-sm btn-success" title="Mark Paid">
                                    <i class="bi bi-check-circle"></i>
                                </button>
                            </form>
                            <a href="{{ auth()->user()->role === 'admin' ? route('admin.payrolls.edit', $payroll) : route('hr.payrolls.edit', $payroll) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </div>
                        @else
                        <div class="payroll-row-actions">
                            <span class="badge bg-light text-success border border-success payroll-settled-badge"><i class="bi bi-check-all me-1"></i> Settled</span>
                            <a href="{{ auth()->user()->role === 'admin' ? route('admin.payrolls.download', $payroll) : route('hr.payrolls.download', $payroll) }}" class="btn btn-sm btn-outline-secondary" title="Download Payslip">
                                <i class="bi bi-download"></i>
                            </a>
                        </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <i class="bi bi-cash-stack"></i>
                            <p>No payroll records yet. <a href="{{ route('admin.payrolls.create') }}">Generate one now</a>.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($payrolls->hasPages())
    <div class="card-body pt-2">{{ $payrolls->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
