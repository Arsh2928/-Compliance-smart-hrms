<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

    public function create()
    {
        $departments = Department::all();
        return view('admin.employees.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255',
            'department_id' => 'required',
            'employee_code' => 'required|string|max:50',
            'phone'         => 'nullable|string|max:20',
            'address'       => 'nullable|string',
            'joined_date'   => 'required|date',
            'role'          => 'required|in:employee,hr,admin',
        ]);

        // Manual uniqueness checks (MongoDB doesn't support unique: rule)
        if (User::where('email', $request->email)->exists()) {
            return back()->withErrors(['email' => 'Email already in use.'])->withInput();
        }
        if (Employee::where('employee_code', $request->employee_code)->exists()) {
            return back()->withErrors(['employee_code' => 'Employee code already taken.'])->withInput();
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => bcrypt('password123'),
            'role'     => $request->role,
        ]);

        Employee::create([
            'user_id'       => $user->id,
            'department_id' => $request->department_id,
            'employee_code' => $request->employee_code,
            'phone'         => $request->phone,
            'address'       => $request->address,
            'joined_date'   => $request->joined_date,
        ]);

        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee created. Default password: password123');
    }

    public function show(Employee $employee)
    {
        $employee->load('user', 'department', 'leaves', 'attendances', 'contracts', 'payrolls');
        return view('admin.employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $departments = Department::all();
        return view('admin.employees.edit', compact('employee', 'departments'));
    }

    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255',
            'department_id' => 'required',
            'phone'         => 'nullable|string|max:20',
            'address'       => 'nullable|string',
            'role'          => 'required|in:employee,hr,admin',
        ]);

        // Manual uniqueness check — exclude current user's email
        $emailTaken = User::where('email', $request->email)
            ->where('_id', '!=', $employee->user_id)
            ->exists();

        if ($emailTaken) {
            return back()->withErrors(['email' => 'Email already in use by another user.'])->withInput();
        }

        $employee->user->update([
            'name'  => $request->name,
            'email' => $request->email,
            'role'  => $request->role,
        ]);

        $employee->update($request->only('department_id', 'phone', 'address'));

        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        $employee->user->delete();
        $employee->delete();

        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee removed successfully.');
    }
}
