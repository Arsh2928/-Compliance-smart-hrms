<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Alert;

class RewardService
{
    /**
     * Feature 1: Process Attendance Points
     * Run this when a new attendance is logged.
     */
    public function processAttendanceReward(Employee $employee, string $status)
    {
        if ($employee->user && $employee->user->role !== 'employee') {
            return; // HR and Admins do not earn rewards
        }

        $points = 0;

        switch (strtolower($status)) {
            case 'present':
                $points = 10;
                break;
            case 'half day':
            case 'half_day':
                $points = 5;
                break;
            case 'absent':
                $points = -5;
                break;
        }

        // Update points balance (single source of truth)
        $employee->points = ($employee->points ?? 0) + $points;
        
        $this->evaluateBadges($employee);
        $employee->save();

        if ($points > 0) {
            $this->notify($employee, "You earned {$points} attendance points today! 🎉", 'success');
        } elseif ($points < 0) {
            $this->notify($employee, "You lost " . abs($points) . " points due to absence.", 'warning');
        }
    }

    /**
     * Bonus for Perfect Weekly Attendance
     */
    public function awardWeeklyBonus(Employee $employee)
    {
        if ($employee->user && $employee->user->role !== 'employee') {
            return;
        }

        $employee->points = ($employee->points ?? 0) + 20;
        $employee->save();

        $this->notify($employee, "🔥 20 Bonus Points awarded for perfect weekly attendance!", 'success');
        $this->evaluateBadges($employee);
    }

    /**
     * Feature 2 & 7: Badge Evaluation
     */
    public function evaluateBadges(Employee $employee)
    {
        // Badge evaluation now handled exclusively by EvaluateMonthlyPerformance cron
        // to ensure badges are based on real monthly evaluated data, not running totals
        return;
    }

    /**
     * Feature 2: Calculate Performance Score
     * Formula: score = (attendance_points * 0.6) + (rating * 20)
     */
    public function updatePerformanceScore(Employee $employee)
    {
        if ($employee->user && $employee->user->role !== 'employee') {
            return;
        }

        $attPoints = $employee->attendance_points ?? 0;
        $rating = $employee->rating ?? 0;
        
        $employee->performance_score = ($attPoints * 0.6) + ($rating * 20);
        $employee->save();

        $this->generateAIInsights($employee);
    }

    /**
     * Feature 7: AI / Smart Logic Detection
     */
    private function generateAIInsights(Employee $employee)
    {
        // Low attendance detection
        if (($employee->attendance_points ?? 0) < 50 && $employee->attendances()->count() > 10) {
            $this->notifyAdmin("{$employee->user->name} has consistently low attendance points. Needs improvement check-in.", 'warning');
        }

        // High performer detection
        if (($employee->performance_score ?? 0) > 300) {
            $this->notifyAdmin("⭐ {$employee->user->name} is a Top Performer! Eligible for bonus.", 'success');
        }
    }

    /**
     * Helper to send notification to employee
     */
    private function notify(Employee $employee, string $message, string $type)
    {
        Alert::create([
            'user_id' => $employee->user_id,
            'message' => $message,
            'type' => $type,
            'is_read' => false
        ]);
    }

    /**
     * Helper to notify admins (HR)
     */
    private function notifyAdmin(string $message, string $type)
    {
        $admins = \App\Models\User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Alert::create([
                'user_id' => $admin->id,
                'message' => $message,
                'type' => $type,
                'is_read' => false
            ]);
        }
    }
}
