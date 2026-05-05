<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    public function index()
    {
        $contracts = \App\Models\Contract::with('employee.user')->latest()->paginate(10);
        return view('admin.contracts.index', compact('contracts'));
    }

    public function create()
    {
        $employees = \App\Models\Employee::with('user')->get();
        return view('admin.contracts.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id'   => 'required',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after:start_date',
            'document_path' => 'nullable|string',
            'status'        => 'required|in:active,expired,terminated',
        ]);

        if (!\App\Models\Employee::find($request->employee_id)) {
            return back()->withErrors(['employee_id' => 'Invalid employee selected.'])->withInput();
        }

        \App\Models\Contract::create([
            'employee_id'   => $request->employee_id,
            'start_date'    => $request->start_date,
            'end_date'      => $request->end_date,
            'document_path' => $request->document_path,
            'status'        => $request->status,
        ]);

        return redirect()->route('admin.contracts.index')->with('success', 'Contract added successfully.');
    }

    public function edit(\App\Models\Contract $contract)
    {
        $employees = \App\Models\Employee::with('user')->get();
        return view('admin.contracts.edit', compact('contract', 'employees'));
    }

    public function update(Request $request, \App\Models\Contract $contract)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:active,expired,terminated'
        ]);

        $contract->update($request->only(['start_date', 'end_date', 'status']));

        return redirect()->route('admin.contracts.index')->with('success', 'Contract updated successfully.');
    }
}
