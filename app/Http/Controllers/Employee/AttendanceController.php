<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function checkIn(Request $request)
    {
        $employee = auth()->user()->employee;
        if (!$employee) return back()->with('error', 'Employee record not found.');

        $todayStr = today()->format('Y-m-d');
        $attendance = \App\Models\Attendance::firstOrCreate(
            ['employee_id' => $employee->id, 'date' => $todayStr],
            ['check_in' => now()->format('H:i:s'), 'status' => 'present']
        );

        if (!$attendance->wasRecentlyCreated) {
            return back()->with('error', 'Already checked in for today.');
        }

        return back()->with('success', 'Checked in successfully.');
    }

    public function checkOut(Request $request)
    {
        $employee = auth()->user()->employee;
        if (!$employee) return back()->with('error', 'Employee record not found.');
        
        $todayStr = today()->format('Y-m-d');
        $attendance = \App\Models\Attendance::where('employee_id', $employee->id)
                        ->where('date', $todayStr)
                        ->first();

        if (!$attendance) return back()->with('error', 'No check-in record found for today.');
        if ($attendance->check_out) return back()->with('error', 'Already checked out.');

        $checkIn = \Carbon\Carbon::parse($todayStr . ' ' . $attendance->check_in);
        $checkOut = now();
        $totalMinutes = $checkIn->diffInMinutes($checkOut);
        $totalHours   = round($totalMinutes / 60, 2);

        $attendance->update([
            'check_out'   => $checkOut->format('H:i:s'),
            'total_hours' => $totalHours,
        ]);

        // Send overtime notification if worked more than 8 hours
        $overtimeHours = round(max(0, $totalHours - 8), 2);
        if ($overtimeHours > 0) {
            \App\Models\Alert::create([
                'user_id' => auth()->id(),
                'type'    => 'success',
                'message' => "Great work! You worked {$overtimeHours} extra hour(s) today. This overtime will be included in your monthly payroll.",
                'is_read' => false,
                'link'    => '#',
            ]);
        }

        return back()->with('success', 'Checked out successfully. Total hours: ' . round($totalHours, 2));
    }
}
