<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Employee;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    public function index()
    {
        $contracts = Contract::with('employee.user')->latest()->paginate(10);
        return view('admin.contracts.index', compact('contracts'));
    }

    public function create()
    {
        $employees = Employee::with('user')->get();
        return view('admin.contracts.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id'   => 'required',
            'basic_salary'  => 'required|numeric|min:0',
            'start_date'    => 'required|date',
            'end_date'      => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($request) {
                    $start = \Carbon\Carbon::parse($request->start_date);
                    $end = \Carbon\Carbon::parse($value);
                    if ($start->diffInMonths($end) < 6) {
                        $fail('The contract must be at least 6 months long.');
                    }
                },
            ],
            'document_path' => 'nullable|string',
            'status'        => 'required|in:active,expired,terminated',
        ]);

        if (!Employee::find($request->employee_id)) {
            return back()->withErrors(['employee_id' => 'Invalid employee selected.'])->withInput();
        }

        Contract::create([
            'employee_id'   => $request->employee_id,
            'start_date'    => $request->start_date,
            'end_date'      => $request->end_date,
            'document_path' => $request->document_path,
            'status'        => $request->status,
            'basic_salary'  => $request->basic_salary,
        ]);

        return redirect()->route('hr.contracts.index')->with('success', 'Contract added successfully.');
    }

    public function edit(Contract $contract)
    {
        $employees = Employee::with('user')->get();
        return view('admin.contracts.edit', compact('contract', 'employees'));
    }

    public function update(Request $request, Contract $contract)
    {
        $request->validate([
            'basic_salary' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date'   => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($request) {
                    $start = \Carbon\Carbon::parse($request->start_date);
                    $end = \Carbon\Carbon::parse($value);
                    if ($start->diffInMonths($end) < 6) {
                        $fail('The contract must be at least 6 months long.');
                    }
                },
            ],
            'status'     => 'required|in:active,expired,terminated',
        ]);

        $contract->update($request->only(['start_date', 'end_date', 'status', 'basic_salary']));

        return redirect()->route('hr.contracts.index')->with('success', 'Contract updated successfully.');
    }
}
