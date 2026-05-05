@extends('layouts.app')
@section('title', 'Payroll Records')

@section('content')
<div class="page-header">
    <h4><i class="bi bi-cash-coin me-2 text-success"></i>Payroll Records</h4>
    <a href="{{ route('admin.payrolls.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Generate Payroll
    </a>
</div>

<div class="card shadow-sm mb-4">
    <div class="table-responsive">
        <table class="table">
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
                    <td>
                        @if($payroll->status === 'pending')
                        <form action="{{ route('admin.payrolls.update', $payroll) }}" method="POST">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="paid">
                            <button type="submit" class="btn btn-sm btn-success">
                                <i class="bi bi-check-circle me-1"></i>Mark Paid
                            </button>
                        </form>
                        @else
                        <button class="btn btn-sm btn-light text-success" disabled>
                            <i class="bi bi-check-all"></i> Settled
                        </button>
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
