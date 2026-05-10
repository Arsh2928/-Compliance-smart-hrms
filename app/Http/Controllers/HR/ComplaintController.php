<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ComplaintController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $query = Complaint::with('user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $complaints = $query->paginate(15);
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

        if ($complaint->user_id) {
            Alert::create([
                'user_id'  => $complaint->user_id,
                'type'     => $request->status === 'resolved' ? 'success' : 'danger',
                'message'  => 'Your complaint "' . Str::limit($complaint->title, 30) . '" has been ' . $request->status . '.',
                'is_read'  => false,
                'link'     => route('employee.complaints.index'),
            ]);
        }

        return redirect()->route('hr.complaints.index')
            ->with('success', 'Complaint updated successfully.');
    }
}
