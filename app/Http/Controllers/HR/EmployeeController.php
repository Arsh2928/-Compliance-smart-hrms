<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with('user', 'department');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            })->orWhere('employee_code', 'like', "%$search%");
        }

        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }

        $employees   = $query->paginate(10);
        $departments = Department::all();
        return view('admin.employees.index', compact('employees', 'departments'));
    }

    public function show(Employee $employee)
    {
        $employee->load('user', 'department', 'leaves', 'attendances', 'contracts', 'payrolls');
        return view('admin.employees.show', compact('employee'));
    }
}
