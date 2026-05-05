<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index()
    {
        $employee = auth()->user()->employee;
        if (!$employee) return redirect()->route('login')->with('error', 'Employee record not found.');
        
        $payrolls = \App\Models\Payroll::where('employee_id', $employee->id)->latest()->paginate(10);
        return view('employee.payrolls.index', compact('payrolls'));
    }
}
