<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalEmployees   = \App\Models\Employee::count();
        $todayStr         = today()->format('Y-m-d');
        $attendanceToday  = \App\Models\Attendance::where('date', $todayStr)->count();
        $pendingLeaves    = \App\Models\Leave::where('status', 'pending')->count();
        $openComplaints   = \App\Models\Complaint::where('status', 'open')->count();
        $complianceAlerts = \App\Models\Alert::where('is_read', false)->count();

        // Chart Data: Last 7 days attendance + leave submissions
        $chartDates  = [];
        $chartData   = [];
        $chartLeaves = [];
        for ($i = 6; $i >= 0; $i--) {
            $date          = today()->subDays($i);
            $dateStr       = $date->format('Y-m-d');
            $chartDates[]  = $date->format('M d');
            $chartData[]   = \App\Models\Attendance::where('date', $dateStr)->count();
            $chartLeaves[] = \App\Models\Leave::whereDate('created_at', $dateStr)->count();
        }

        return view('admin.dashboard', compact(
            'totalEmployees', 'attendanceToday', 'pendingLeaves',
            'openComplaints', 'complianceAlerts', 'chartDates', 'chartData', 'chartLeaves'
        ));
    }
}
