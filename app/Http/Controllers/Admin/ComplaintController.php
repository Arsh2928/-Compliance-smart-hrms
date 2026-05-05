<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ComplaintController extends Controller
{
    public function index()
    {
        $complaints = Complaint::with('user')->latest()->paginate(10);
        return view('admin.complaints.index', compact('complaints'));
    }

    public function update(Request $request, Complaint $complaint)
    {
        $request->validate([
            'status'         => 'required|in:resolved,rejected',
            'admin_response' => 'nullable|string|max:2000',
        ]);

        $complaint->update([
            'status'         => $request->status,
            'admin_response' => $request->admin_response,
        ]);

        // Notify the complainant (skip if anonymous — user_id is still stored)
        if ($complaint->user_id) {
            Alert::create([
                'user_id'  => $complaint->user_id,
                'type'     => $request->status === 'resolved' ? 'success' : 'danger',
                'message'  => 'Your complaint "' . Str::limit($complaint->title, 30) . '" has been ' . $request->status . '.',
                'is_read'  => false,
                'link'     => route('employee.complaints.index'),
            ]);
        }

        return redirect()->route('admin.complaints.index')
            ->with('success', 'Complaint updated successfully.');
    }
}
