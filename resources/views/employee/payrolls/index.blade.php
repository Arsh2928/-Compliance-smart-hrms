@extends('layouts.app')
@section('title', 'My Payslips')

@section('content')
<div class="page-header">
    <h4><i class="bi bi-receipt-cutoff me-2 text-success"></i>Salary History</h4>
</div>

<div class="card shadow-sm mb-4">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Period</th>
                    <th>Basic Salary</th>
                    <th>Overtime</th>
                    <th>Deductions</th>
                    <th>Net Salary</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payrolls as $payroll)
                <tr>
                    <td class="fw-semibold">
                        {{ DateTime::createFromFormat('!m', $payroll->month)->format('F') }}
                        {{ $payroll->year }}
                    </td>
                    <td>{{ \App\Support\Money::inr($payroll->basic_salary) }}</td>
                    <td>
                        {{ \App\Support\Money::inr($payroll->overtime_pay) }}
                        <small class="text-muted">({{ $payroll->overtime_hours }}h)</small>
                    </td>
                    <td class="text-danger">-{{ \App\Support\Money::inr($payroll->deductions) }}</td>
                    <td><strong class="text-success fs-6">{{ \App\Support\Money::inr($payroll->net_salary) }}</strong></td>
                    <td>
                        @if($payroll->status === 'paid')
                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Paid</span>
                        @else
                            <span class="badge bg-warning">Processing</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <i class="bi bi-cash-stack"></i>
                            <p>No payslips available yet. Contact HR for details.</p>
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
