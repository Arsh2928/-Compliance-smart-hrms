<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreComplaintRequest;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function index()
    {
        $complaints = \App\Models\Complaint::where('user_id', auth()->id())->latest()->paginate(10);
        return view('employee.complaints.index', compact('complaints'));
    }

    public function create()
    {
        return view('employee.complaints.create');
    }

    public function store(StoreComplaintRequest $request)
    {
        // Validation is handled by StoreComplaintRequest

        $complaint = \App\Models\Complaint::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'description' => $request->description,
            'is_anonymous' => $request->has('is_anonymous'),
            'status' => 'pending',
        ]);

        try {
            $adminEmails = \App\Models\User::where('role', 'admin')->pluck('email')->toArray();
            if (!empty($adminEmails)) {
                \Illuminate\Support\Facades\Mail::to($adminEmails)->send(new \App\Mail\NewComplaintMail($complaint));
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send complaint email: ' . $e->getMessage());
        }

        return redirect()->route('employee.complaints.index')->with('success', 'Complaint submitted successfully.');
    }
}
