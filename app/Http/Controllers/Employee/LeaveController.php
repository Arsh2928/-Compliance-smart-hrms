<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeaveRequest;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function index()
    {
        $employee = auth()->user()->employee;
        if (!$employee) return redirect()->route('login')->with('error', 'Employee record not found.');
        
        $leaves = \App\Models\Leave::where('employee_id', $employee->id)->latest()->paginate(10);
        return view('employee.leaves.index', compact('leaves'));
    }

    public function create()
    {
        return view('employee.leaves.create');
    }

    public function store(StoreLeaveRequest $request)
    {
        // Validation is now handled by StoreLeaveRequest

        $employee = auth()->user()->employee;
        if (!$employee) return redirect()->route('login')->with('error', 'Employee record not found.');

        \App\Models\Leave::create([
            'employee_id' => $employee->id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'type' => $request->type,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return redirect()->route('employee.leaves.index')->with('success', 'Leave application submitted successfully.');
    }
}
