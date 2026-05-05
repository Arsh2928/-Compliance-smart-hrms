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
            'status'        => 'required|in:pending,paid',
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

    public function update(Request $request, \App\Models\Payroll $payroll)
    {
        $request->validate(['status' => 'required|in:pending,paid']);
        $payroll->update(['status' => $request->status]);
        return redirect()->route('admin.payrolls.index')->with('success', 'Payroll status updated.');
    }
}
