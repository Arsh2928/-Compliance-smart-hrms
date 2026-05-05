<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\MonthlyReward;
use App\Models\PerformanceRecord;
use App\Services\RatingService;
use App\Services\ScoringService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $employee = auth()->user()->employee;

        if (!$employee) {
            return redirect()->route('login')->with('error', 'Employee record not found. Contact HR.');
        }

        // ── Attendance stats ───────────────────────────────────────────
        $startOfMonth = now()->startOfMonth()->format('Y-m-d');
        $endOfMonth   = now()->endOfMonth()->format('Y-m-d');

        $monthlyAttendances = \App\Models\Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get();

        $monthlyHours = $monthlyAttendances->sum(fn($a) => (float)($a->total_hours ?? 0));

        $approvedLeaves = \App\Models\Leave::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->count();
        $leaveBalance = max(0, 20 - $approvedLeaves);

        $recentAttendance = \App\Models\Attendance::where('employee_id', $employee->id)
            ->orderBy('date', 'desc')
            ->take(5)
            ->get();

        // ── Live performance score (real-time mid-month) ───────────────
        $scoringService  = app(ScoringService::class);
        $currentMonth    = now()->format('Y-m');
        $scoreResult     = $scoringService->computeScore($employee, $currentMonth);
        $liveScore       = $scoreResult['live_score'];
        $scoreComponents = $scoreResult['components'];
        $scoreFlags      = $scoreResult['flags'];

        // ── Weakest metric (suppressed if all metrics are good) ────────
        $ratingService   = app(RatingService::class);
        $weakestCategory = $ratingService->getWeakestCategory($employee);

        // ── Tier progression ───────────────────────────────────────────
        $latestMonth = PerformanceRecord::max('month') ?? $currentMonth;
        $tierInfo    = $scoringService->nextTierInfo($liveScore, $latestMonth);

        // ── Current rank from latest PerformanceRecord ─────────────────
        $latestRecord = PerformanceRecord::where('employee_id', $employee->id)
            ->orderBy('month', 'desc')
            ->first();

        // ── Reward history ─────────────────────────────────────────────
        $rewardHistory = MonthlyReward::where('employee_id', $employee->id)
            ->orderBy('month', 'desc')
            ->get();

        return view('employee.dashboard', compact(
            'employee',
            'monthlyHours',
            'leaveBalance',
            'recentAttendance',
            'liveScore',
            'scoreComponents',
            'scoreFlags',
            'weakestCategory',
            'tierInfo',
            'latestRecord',
            'rewardHistory'
        ));
    }
}
