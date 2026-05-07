<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalEmployees = \App\Models\Employee::whereHas('user', function($q) {
            $q->where('role', '!=', 'admin');
        })->count();
        $todayStr         = today()->format('Y-m-d');
        $attendanceToday  = \App\Models\Attendance::where('date', $todayStr)->count();
        $pendingLeaves    = \App\Models\Leave::where('status', 'pending')->count();
        $openComplaints   = \App\Models\Complaint::whereIn('status', ['open', 'pending'])->count();
        $complianceAlerts = \App\Models\Alert::where('is_read', false)->count();

        $employeeUserIds = \App\Models\User::where('role', 'employee')->pluck('id');

        // Get the latest evaluated month, fallback to current month
        $month = \App\Models\PerformanceRecord::max('month') ?? now()->format('Y-m');

        // FETCH RECORDS FOR CURRENT MONTH
        $allRecords = \App\Models\PerformanceRecord::with('employee.user')
            ->where('month', $month)
            ->whereHas('employee', function($q) use ($employeeUserIds) {
                $q->whereIn('user_id', $employeeUserIds);
            })
            ->get();

        // TOP PERFORMERS: Single Source of Truth
        $topRecords = $allRecords->sortByDesc(function($r) {
            return $r->final_score ?? $r->live_score ?? 0;
        })->take(3);
            
        $topPerformers = $topRecords->map(function($record) {
            $emp = $record->employee;
            $emp->performance_score = round($record->final_score ?? $record->live_score ?? 0, 1);
            return $emp;
        });
            
        // LOW PERFORMERS: Single Source of Truth
        $lowRecords = $allRecords->filter(function($r) {
            $score = $r->final_score ?? $r->live_score ?? 0;
            return $score < 50 && $score > 0; // Only actual low performers, ignore 0s
        })->sortBy(function($r) {
            return $r->final_score ?? $r->live_score ?? 0;
        })->take(3);

        $lowPerformers = $lowRecords->map(function($record) {
            $emp = $record->employee;
            $emp->performance_score = round($record->final_score ?? $record->live_score ?? 0, 1);
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

        // ==========================================
        // REAL DATA FOR CHARTS
        // ==========================================
        
        // 1. Performance Trends (Last 6 Months Average Score)
        $performanceLabels = [];
        $performanceData = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->subMonths($i)->format('Y-m');
            $performanceLabels[] = now()->subMonths($i)->format('M Y');
            $recordsInMonth = \App\Models\PerformanceRecord::where('month', $m)->get();
            $avg = $recordsInMonth->avg(function($r) {
                return $r->final_score ?? $r->live_score ?? 0;
            }) ?? 0;
            $performanceData[] = round($avg, 1);
        }

        // 2. Reward Distribution (Badges)
        $allEmps = \App\Models\Employee::all();
        $badgeCounts = ['Gold' => 0, 'Silver' => 0, 'Bronze' => 0, 'None' => 0];
        foreach ($allEmps as $e) {
            $b = is_array($e->badges) ? $e->badges : [];
            if (in_array('Gold', $b)) $badgeCounts['Gold']++;
            elseif (in_array('Silver', $b)) $badgeCounts['Silver']++;
            elseif (in_array('Bronze', $b)) $badgeCounts['Bronze']++;
            else $badgeCounts['None']++;
        }
        $rewardLabels = array_keys($badgeCounts);
        $rewardData = array_values($badgeCounts);

        // 3. HR Personal Attendance Data (for HR check-in/checkout graph)
        $hrEmployee = auth()->user()->employee;
        $hrChartDates = [];
        $hrChartHours = [];
        if (auth()->user()->role === 'hr' && $hrEmployee) {
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $dateStr = $date->format('Y-m-d');
                $hrChartDates[] = $date->format('M d');
                
                $att = \App\Models\Attendance::where('employee_id', $hrEmployee->id)
                    ->where('date', $dateStr)
                    ->first();
                
                $hrChartHours[] = $att ? (float) ($att->total_hours ?? 0) : 0;
            }
        }

        return view('admin.dashboard', compact(
            'totalEmployees', 'attendanceToday', 'pendingLeaves',
            'openComplaints', 'complianceAlerts',
            'chartDates', 'chartData', 'chartLeaves',
            'topPerformers', 'lowPerformers',
            'topPredictor', 'burnoutRisks',
            'performanceLabels', 'performanceData', 'rewardLabels', 'rewardData',
            'hrEmployee', 'hrChartDates', 'hrChartHours'
        ));
    }
}
