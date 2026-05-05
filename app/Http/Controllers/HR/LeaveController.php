<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\Leave;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function index()
    {
        $leaves = Leave::with('employee.user')->latest()->paginate(10);
        return view('admin.leaves.index', compact('leaves'));
    }

    public function update(Request $request, Leave $leave)
    {
        $request->validate([
            'status'       => 'required|in:approved,rejected',
            'admin_remark' => 'nullable|string|max:500',
        ]);

        $leave->update([
            'status'       => $request->status,
            'admin_remark' => $request->admin_remark,
        ]);

        $userId = optional($leave->employee)->user_id;
        if ($userId) {
            Alert::create([
                'user_id' => $userId,
                'type'    => $request->status === 'approved' ? 'success' : 'danger',
                'message' => 'Your leave request (' . $leave->type . ') from '
                             . $leave->start_date . ' has been ' . $request->status . '.',
                'is_read' => false,
                'link'    => route('employee.leaves.index'),
            ]);
        }

        return redirect()->route('hr.leaves.index')
            ->with('success', 'Leave status updated to ' . ucfirst($request->status) . '.');
    }
}
