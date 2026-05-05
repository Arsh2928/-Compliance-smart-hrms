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
        $openComplaints   = \App\Models\Complaint::whereIn('status', ['open', 'pending'])->count();
        $complianceAlerts = \App\Models\Alert::where('is_read', false)->count();

        $employeeUserIds = \App\Models\User::where('role', 'employee')->pluck('id');

        // Get the latest evaluated month, fallback to current month
        $month = \App\Models\PerformanceRecord::max('month') ?? now()->format('Y-m');

        // TOP PERFORMERS
        $topRecords = \App\Models\PerformanceRecord::with('employee.user')
            ->where('month', $month)
            ->whereHas('employee', fn($q) => $q->whereIn('user_id', $employeeUserIds))
            ->orderBy('final_score', 'desc')
            ->take(3)
            ->get();

        $topPerformers = $topRecords->map(function ($record) {
            $emp = $record->employee;
            $emp->performance_score = $record->final_score;
            return $emp;
        });

        // LOW PERFORMERS
        $lowRecords = \App\Models\PerformanceRecord::with('employee.user')
            ->where('month', $month)
            ->where('final_score', '<', 50)
            ->whereHas('employee', fn($q) => $q->whereIn('user_id', $employeeUserIds))
            ->orderBy('final_score', 'asc')
            ->take(3)
            ->get();

        $lowPerformers = $lowRecords->map(function ($record) {
            $emp = $record->employee;
            $emp->performance_score = $record->final_score;
            return $emp;
        });

        // AI: Top Performer Predictor
        $topPredictor = \App\Models\Employee::whereIn('user_id', $employeeUserIds)
            ->where('points', '>', 0)
            ->orderBy('points', 'desc')
            ->first();

        // AI: Burnout Risk (>50 hrs in last 7 days)
        $sevenDaysAgo      = today()->subDays(7)->format('Y-m-d');
        $recentAttendances = \App\Models\Attendance::where('date', '>=', $sevenDaysAgo)->get();

        $burnoutRisks = collect();
        $recentAttendances->groupBy('employee_id')->map(function ($group) {
            return (object) [
                'employee_id' => $group->first()->employee_id,
                'weekly_hours' => $group->sum('total_hours'),
            ];
        })->values()->each(function ($record) use (&$burnoutRisks) {
            if ($record->weekly_hours >= 50) {
                $emp = \App\Models\Employee::with('user')->find($record->employee_id);
                if ($emp) {
                    $emp->burnout_hours = $record->weekly_hours;
                    $burnoutRisks->push($emp);
                }
            }
        });
        $burnoutRisks = $burnoutRisks->take(3);

        // Chart Data: Last 7 days
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
            'openComplaints', 'complianceAlerts',
            'chartDates', 'chartData', 'chartLeaves',
            'topPerformers', 'lowPerformers',
            'topPredictor', 'burnoutRisks'
        ));
    }
}
