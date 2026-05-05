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

        $attendance = \App\Models\Attendance::firstOrCreate(
            ['employee_id' => $employee->id, 'date' => today()],
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
        
        $attendance = \App\Models\Attendance::where('employee_id', $employee->id)
                        ->where('date', today())
                        ->first();

        if (!$attendance) return back()->with('error', 'No check-in record found for today.');
        if ($attendance->check_out) return back()->with('error', 'Already checked out.');

        $checkIn = \Carbon\Carbon::parse($attendance->check_in);
        $checkOut = now();
        $totalMinutes = $checkIn->diffInMinutes($checkOut);
        $totalHours   = round($totalMinutes / 60, 2);

        $attendance->update([
            'check_out' => $checkOut->format('H:i:s'),
            'total_hours' => $totalHours,
        ]);

        return back()->with('success', 'Checked out successfully. Total hours: ' . round($totalHours, 2));
    }
}
