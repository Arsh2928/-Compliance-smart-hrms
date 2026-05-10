<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Alert;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with('user', 'department')
            ->whereHas('user', function ($q) {
                $q->where('role', '!=', 'admin');
            });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($qu) use ($search) {
                    $qu->where('name', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%");
                })->orWhere('employee_code', 'like', "%$search%");
            });
        }

        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }

        $employees   = $query->paginate(10);
        $departments = Department::all();
        $pendingUsers = \App\Models\User::where('status', 'pending')->get();
        return view('admin.employees.index', compact('employees', 'departments', 'pendingUsers'));
    }

    public function show(Employee $employee)
    {
        $employee->load('user', 'department', 'leaves', 'attendances', 'contracts', 'payrolls');
        return view('admin.employees.show', compact('employee'));
    }

    public function create()
    {
        $departments = Department::all();
        return view('admin.employees.create', compact('departments'));
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255',
            'department_id' => 'required',
            'phone'         => 'nullable|string|max:20',
            'address'       => 'nullable|string',
            'joined_date'   => 'required|date',
            'role'          => 'required|in:employee,hr',
        ]);

        if (\App\Models\User::where('email', $request->email)->exists()) {
            return back()->withErrors(['email' => 'Email already in use.'])->withInput();
        }

        $employeeCode = 'EMP-' . strtoupper(substr(uniqid(), -6));
        while (Employee::where('employee_code', $employeeCode)->exists()) {
            $employeeCode = 'EMP-' . strtoupper(substr(uniqid(), -6));
        }

        $user = \App\Models\User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'address'  => $request->address,
            'password' => bcrypt('password123'),
            'role'     => $request->role,
            'status'   => 'approved',
        ]);

        $employee = Employee::create([
            'user_id'       => $user->id,
            'department_id' => $request->department_id,
            'employee_code' => $employeeCode,
            'joined_date'   => $request->joined_date,
        ]);

        \App\Models\Contract::create([
            'employee_id'  => $employee->id,
            'start_date'   => now()->format('Y-m-d'),
            'end_date'     => now()->addMonths(6)->format('Y-m-d'),
            'status'       => 'active',
            'basic_salary' => 0,
        ]);

        \App\Models\Alert::create([
            'user_id' => $user->id,
            'type'    => 'info',
            'message' => 'A standard 6-month contract has been automatically generated for you.',
            'is_read' => false,
            'link'    => '#',
        ]);

        return redirect()->route('hr.employees.index')
            ->with('success', 'Employee created with a 6-month contract. Default password: password123');
    }

    public function edit(Employee $employee)
    {
        $departments = Department::all();
        return view('admin.employees.edit', compact('employee', 'departments'));
    }

    public function update(\Illuminate\Http\Request $request, Employee $employee)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255',
            'department_id' => 'required',
            'phone'         => 'nullable|string|max:20',
            'address'       => 'nullable|string',
            'role'          => 'required|in:employee,hr',
        ]);

        $emailTaken = \App\Models\User::where('email', $request->email)
            ->where('_id', '!=', $employee->user_id)
            ->exists();

        if ($emailTaken) {
            return back()->withErrors(['email' => 'Email already in use by another user.'])->withInput();
        }

        $employee->user->update([
            'name'    => $request->name,
            'email'   => $request->email,
            'phone'   => $request->phone,
            'address' => $request->address,
            'role'    => $request->role,
        ]);

        $employee->update($request->only('department_id'));

        return redirect()->route('hr.employees.index')
            ->with('success', 'Employee updated successfully.');
    }
}
