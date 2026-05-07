<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index()
    {
        $payrolls = \App\Models\Payroll::with('employee.user')->latest()->paginate(10);
        return view('admin.payrolls.index', compact('payrolls'));
    }

    public function create()
    {
        $employees = \App\Models\Employee::with('user')->get();
        return view('admin.payrolls.create', compact('employees'));
    }

    public function calculate(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|string',
            'month' => 'required|integer',
            'year' => 'required|integer'
        ]);

        $employee = \App\Models\Employee::find($request->employee_id);
        if (!$employee) return response()->json(['error' => 'Not found'], 404);

        // Get basic salary from contract
        $contract = \App\Models\Contract::where('employee_id', $employee->id)->where('status', 'active')->first();
        $basicSalary = $contract ? $contract->basic_salary : 0;

        $monthStr = str_pad($request->month, 2, '0', STR_PAD_LEFT);
        $prefix = $request->year . '-' . $monthStr . '-';

        // Calculate attendance stats (using string comparison for Y-m-d)
        $attendances = \App\Models\Attendance::where('employee_id', $employee->id)
            ->where('date', '>=', $prefix . '01')
            ->where('date', '<=', $prefix . '31')
            ->get();

        $overtimeHours = 0;
        foreach ($attendances as $att) {
            if ($att->total_hours > 8) {
                $overtimeHours += ($att->total_hours - 8);
            }
        }

        // Calculate absences
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $request->month, $request->year);
        $workingDays = 0;
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = \Carbon\Carbon::create($request->year, $request->month, $d);
            if (!$date->isWeekend()) {
                $workingDays++;
            }
        }

        $presentDays = $attendances->count();
        $approvedLeaves = \App\Models\Leave::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where('start_date', '>=', $prefix . '01')
            ->where('start_date', '<=', $prefix . '31')
            ->count(); // Simplified count

        $absences = max(0, $workingDays - ($presentDays + $approvedLeaves));

        // Deductions & Pay
        $perDaySalary = $basicSalary / 30;
        $absentDeductions = $absences * $perDaySalary;
        
        $overtimePay = $overtimeHours * ($perDaySalary / 8) * 1.5;
        
        // Let's add 10% tax automatically
        $gross = $basicSalary + $overtimePay - $absentDeductions;
        $tax = $gross > 0 ? $gross * 0.10 : 0;
        $totalDeductions = $absentDeductions + $tax;

        return response()->json([
            'basic_salary' => round($basicSalary, 2),
            'overtime_hours' => round($overtimeHours, 1),
            'overtime_pay' => round($overtimePay, 2),
            'deductions' => round($totalDeductions, 2)
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id'   => 'required',
            'month'         => 'required|integer|min:1|max:12',
            'year'          => 'required|integer|min:2020|max:2099',
            'basic_salary'  => 'required|numeric|min:0',
            'overtime_hours'=> 'nullable|numeric|min:0',
            'overtime_pay'  => 'nullable|numeric|min:0',
            'deductions'    => 'nullable|numeric|min:0',
            'status'        => 'required|in:pending,approved,paid',
        ]);

        // Manual check (MongoDB doesn't support exists: rule)
        if (!\App\Models\Employee::find($request->employee_id)) {
            return back()->withErrors(['employee_id' => 'Invalid employee selected.'])->withInput();
        }

        $basic      = $request->basic_salary;
        $overtime   = $request->overtime_pay ?? 0;
        $deductions = $request->deductions ?? 0;
        $net_salary = ($basic + $overtime) - $deductions;

        \App\Models\Payroll::create([
            'employee_id'    => $request->employee_id,
            'month'          => $request->month,
            'year'           => $request->year,
            'basic_salary'   => $basic,
            'overtime_hours' => $request->overtime_hours ?? 0,
            'overtime_pay'   => $overtime,
            'deductions'     => $deductions,
            'net_salary'     => $net_salary,
            'status'         => $request->status,
        ]);

        return redirect()->route('admin.payrolls.index')
            ->with('success', 'Payroll created successfully.');
    }

    public function edit(\App\Models\Payroll $payroll)
    {
        return view('admin.payrolls.edit', compact('payroll'));
    }

    public function update(Request $request, \App\Models\Payroll $payroll)
    {
        if ($request->has('basic_salary')) {
            $request->validate([
                'basic_salary'  => 'required|numeric|min:0',
                'overtime_hours'=> 'nullable|numeric|min:0',
                'overtime_pay'  => 'nullable|numeric|min:0',
                'deductions'    => 'nullable|numeric|min:0',
                'status'        => 'required|in:pending,approved,paid',
            ]);

            $basic      = $request->basic_salary;
            $overtime   = $request->overtime_pay ?? 0;
            $deductions = $request->deductions ?? 0;
            $net_salary = ($basic + $overtime) - $deductions;

            $payroll->update([
                'basic_salary'   => $basic,
                'overtime_hours' => $request->overtime_hours ?? 0,
                'overtime_pay'   => $overtime,
                'deductions'     => $deductions,
                'net_salary'     => $net_salary,
                'status'         => $request->status,
            ]);
            return redirect()->route('admin.payrolls.index')->with('success', 'Payroll updated successfully.');
        }

        $request->validate(['status' => 'required|in:pending,approved,paid']);
        $payroll->update(['status' => $request->status]);

        // Notify employee when approved or paid
        $employee = $payroll->employee;
        if ($employee && $employee->user) {
            $msg = $request->status === 'approved'
                ? "Your payslip has been approved by admin."
                : "Your salary for " . \DateTime::createFromFormat('!m', $payroll->month)->format('F') . " {$payroll->year} has been paid. ₹" . number_format($payroll->net_salary, 2);
            \App\Models\Alert::create([
                'user_id' => $employee->user->id,
                'type'    => $request->status === 'approved' ? 'info' : 'success',
                'message' => $msg,
                'is_read' => false,
                'link'    => '#',
            ]);
        }

        return redirect()->route('admin.payrolls.index')->with('success', 'Payroll status updated.');
    }

    public function downloadPdf(\App\Models\Payroll $payroll)
    {
        $payroll->load('employee.user', 'employee.department');
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.payrolls.pdf', compact('payroll'));
        return $pdf->download('Payslip_' . $payroll->employee->employee_code . '_' . $payroll->month . '_' . $payroll->year . '.pdf');
    }

    public function downloadAll()
    {
        $payrolls = \App\Models\Payroll::with('employee.user', 'employee.department')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        $filename = 'payroll_records_' . now()->format('Y_m_d_His') . '.csv';

        return response()->streamDownload(function () use ($payrolls) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Employee Code',
                'Employee Name',
                'Department',
                'Month',
                'Year',
                'Basic Salary',
                'Overtime Hours',
                'Overtime Pay',
                'Deductions',
                'Net Salary',
                'Status',
            ]);

            foreach ($payrolls as $payroll) {
                fputcsv($handle, [
                    $payroll->employee->employee_code ?? '',
                    $payroll->employee->user->name ?? 'Unknown',
                    $payroll->employee->department->name ?? '',
                    \DateTime::createFromFormat('!m', $payroll->month)->format('F'),
                    $payroll->year,
                    $payroll->basic_salary,
                    $payroll->overtime_hours,
                    $payroll->overtime_pay,
                    $payroll->deductions,
                    $payroll->net_salary,
                    ucfirst($payroll->status),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
