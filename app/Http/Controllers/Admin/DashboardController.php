<?php

namespace App\Http\Controllers\Admin;

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

        // TOP PERFORMERS: Single Source of Truth
        $topRecords = \App\Models\PerformanceRecord::with('employee.user')
            ->where('month', $month)
            ->whereHas('employee', function($q) use ($employeeUserIds) {
                $q->whereIn('user_id', $employeeUserIds);
            })
            ->orderBy('final_score', 'desc')
            ->take(3)
            ->get();
            
        $topPerformers = $topRecords->map(function($record) {
            $emp = $record->employee;
            $emp->performance_score = $record->final_score;
            return $emp;
        });
            
        // LOW PERFORMERS: Single Source of Truth
        $lowRecords = \App\Models\PerformanceRecord::with('employee.user')
            ->where('month', $month)
            ->where('final_score', '<', 50) // Actual low performers only
            ->whereHas('employee', function($q) use ($employeeUserIds) {
                $q->whereIn('user_id', $employeeUserIds);
            })
            ->orderBy('final_score', 'asc')
            ->take(3)
            ->get();

        $lowPerformers = $lowRecords->map(function($record) {
            $emp = $record->employee;
            $emp->performance_score = $record->final_score;
            return $emp;
        });

        // ==========================================
        // AI SYSTEM (SECTION 11) - PREDICTIONS
        // ==========================================
        $burnoutRisks = collect();
        $topPredictor = null;

        // Predict Top Performer (highest live score velocity this month)
        $topPredictor = \App\Models\Employee::whereIn('user_id', $employeeUserIds)
            ->where('points', '>', 0)
            ->orderBy('points', 'desc')
            ->first();

        // Burnout Risk: Employees logging more than 50 hours in the last 7 days
        $sevenDaysAgo = today()->subDays(7)->format('Y-m-d');
        $recentAttendances = \App\Models\Attendance::where('date', '>=', $sevenDaysAgo)->get();
        
        $highHoursEmployees = $recentAttendances->groupBy('employee_id')->map(function($group) {
            return (object) [
                'employee_id' => $group->first()->employee_id,
                'weekly_hours' => $group->sum('total_hours')
            ];
        })->values();

        foreach ($highHoursEmployees as $record) {
            if ($record->weekly_hours >= 50) {
                $emp = \App\Models\Employee::with('user')->find($record->employee_id);
                if ($emp) {
                    $emp->burnout_hours = $record->weekly_hours;
                    $burnoutRisks->push($emp);
                }
            }
        }
        $burnoutRisks = $burnoutRisks->take(3); // Show top 3 at risk

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

        // Auto-generate compliance alerts (safe)
        try { $this->evaluateAlerts(); } catch (\Exception $e) {}

        return view('admin.dashboard', compact(
            'totalEmployees', 'attendanceToday', 'pendingLeaves',
            'openComplaints', 'complianceAlerts', 'chartDates', 'chartData', 'chartLeaves', 'topPerformers', 'lowPerformers',
            'topPredictor', 'burnoutRisks'
        ));
    }

    private function evaluateAlerts()
    {
        // Check for pending leaves overload
        $pendingLeaves = \App\Models\Leave::where('status', 'pending')->count();
        if ($pendingLeaves > 5) {
            \App\Models\Alert::firstOrCreate([
                'user_id' => auth()->id(),
                'type' => 'warning',
                'message' => "High volume of pending leaves ($pendingLeaves). Please review.",
            ], [
                'is_read' => false,
                'link' => route('admin.leaves.index')
            ]);
        }

        // Check for contracts expiring soon (dummy logic for now, depends on Contract model)
        $expiringContracts = \App\Models\Contract::where('end_date', '<=', today()->addDays(30))
                                               ->where('status', 'active')
                                               ->count();
        if ($expiringContracts > 0) {
            \App\Models\Alert::firstOrCreate([
                'user_id' => auth()->id(),
                'type' => 'danger',
                'message' => "$expiringContracts contracts are expiring within 30 days.",
            ], [
                'is_read' => false,
                'link' => route('admin.contracts.index')
            ]);
        }
    }
}
