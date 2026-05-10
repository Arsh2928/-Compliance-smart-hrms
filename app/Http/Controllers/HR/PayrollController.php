<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $query = Payroll::with('employee.user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('month')) {
            $query->where('month', (int) $request->month);
        }
        if ($request->filled('year')) {
            $query->where('year', (int) $request->year);
        }

        $payrolls = $query->paginate(15);
        return view('admin.payrolls.index', compact('payrolls'));
    }

    public function create()
    {
        $employees = Employee::with('user')->get();
        return view('admin.payrolls.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id'    => 'required',
            'month'          => 'required|integer|min:1|max:12',
            'year'           => 'required|integer|min:2020|max:2099',
            'basic_salary'   => 'required|numeric|min:0',
            'overtime_hours' => 'nullable|numeric|min:0',
            'overtime_pay'   => 'nullable|numeric|min:0',
            'deductions'     => 'nullable|numeric|min:0',
            'status'         => 'required|in:pending,paid',
        ]);

        if (!Employee::find($request->employee_id)) {
            return back()->withErrors(['employee_id' => 'Invalid employee selected.'])->withInput();
        }

        $basic      = $request->basic_salary;
        $overtime   = $request->overtime_pay ?? 0;
        $deductions = $request->deductions ?? 0;
        $net_salary = ($basic + $overtime) - $deductions;

        Payroll::create([
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

        return redirect()->route('hr.payrolls.index')
            ->with('success', 'Payroll created successfully.');
    }

    public function edit(\App\Models\Payroll $payroll)
    {
        // HR cannot edit payroll details — redirect back with error
        return redirect()->route('hr.payrolls.index')->with('error', 'HR cannot edit payroll details. Only admins can edit.');
    }

    public function update(Request $request, \App\Models\Payroll $payroll)
    {
        // HR can ONLY change status (approve or mark paid), never edit financials
        $request->validate(['status' => 'required|in:pending,approved,paid']);
        $payroll->update(['status' => $request->status]);

        // Notify employee on status change
        $employee = $payroll->employee;
        if ($employee && $employee->user) {
            $msg = $request->status === 'approved'
                ? "Your payslip has been approved."
                : "Your salary for " . \DateTime::createFromFormat('!m', $payroll->month)->format('F') . " {$payroll->year} has been paid. ₹" . number_format($payroll->net_salary, 2);
            \App\Models\Alert::create([
                'user_id' => $employee->user->id,
                'type'    => $request->status === 'approved' ? 'info' : 'success',
                'message' => $msg,
                'is_read' => false,
                'link'    => '#',
            ]);
        }

        return redirect()->route('hr.payrolls.index')->with('success', 'Payroll status updated.');
    }

    public function downloadPdf(\App\Models\Payroll $payroll)
    {
        $payroll->load('employee.user', 'employee.department');
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.payrolls.pdf', compact('payroll'));
        return $pdf->download('Payslip_' . $payroll->employee->employee_code . '_' . $payroll->month . '_' . $payroll->year . '.pdf');
    }

    public function downloadAll()
    {
        $payrolls = Payroll::with('employee.user', 'employee.department')
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
