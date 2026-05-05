<!DOCTYPE html>
<html>
<head>
    <title>Payslip - {{ $payroll->month }}/{{ $payroll->year }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 14px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .company-name { font-size: 24px; font-weight: bold; margin: 0; }
        .payslip-title { font-size: 18px; margin: 5px 0; color: #555; }
        .details-table, .salary-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .details-table td { padding: 5px; }
        .salary-table th, .salary-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .salary-table th { background-color: #f5f5f5; }
        .net-salary { font-size: 18px; font-weight: bold; color: #000; }
        .footer { text-align: center; font-size: 12px; color: #777; margin-top: 50px; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="company-name">Smart HRMS</h1>
        <p class="payslip-title">Payslip for {{ DateTime::createFromFormat('!m', $payroll->month)->format('F') }} {{ $payroll->year }}</p>
    </div>

    <table class="details-table">
        <tr>
            <td><strong>Employee Name:</strong> {{ $payroll->employee->user->name ?? 'N/A' }}</td>
            <td><strong>Employee Code:</strong> {{ $payroll->employee->employee_code ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td><strong>Department:</strong> {{ $payroll->employee->department->name ?? 'N/A' }}</td>
            <td><strong>Date Generated:</strong> {{ $payroll->created_at->format('d M Y') }}</td>
        </tr>
    </table>

    <table class="salary-table">
        <thead>
            <tr>
                <th colspan="2">Earnings</th>
                <th colspan="2">Deductions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Basic Salary</td>
                <td>{{ \App\Support\Money::inr($payroll->basic_salary) }}</td>
                <td>Unpaid Leave / Other</td>
                <td>{{ \App\Support\Money::inr($payroll->deductions) }}</td>
            </tr>
            <tr>
                <td>Overtime Pay ({{ $payroll->overtime_hours ?? 0 }} hrs)</td>
                <td>{{ \App\Support\Money::inr($payroll->overtime_pay) }}</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td><strong>Total Earnings</strong></td>
                <td><strong>{{ \App\Support\Money::inr($payroll->basic_salary + $payroll->overtime_pay) }}</strong></td>
                <td><strong>Total Deductions</strong></td>
                <td><strong>{{ \App\Support\Money::inr($payroll->deductions) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <table class="salary-table" style="margin-top: 20px;">
        <tr>
            <th style="width: 50%;">Net Payable Salary</th>
            <td style="width: 50%; text-align: right;" class="net-salary">{{ \App\Support\Money::inr($payroll->net_salary) }}</td>
        </tr>
    </table>

    <div class="footer">
        This is a computer-generated document. No signature is required.
    </div>
</body>
</html>
