<?php

namespace App\Services;

use App\Models\Employee;

class AiCoachService
{
    /**
     * Analyzes an employee's performance data and returns actionable insights.
     */
    public function generateInsights(array $scoreComponents, array $scoreFlags): array
    {
        $insights = [];

        // 1. Analyze Attendance (35% weight)
        $attendanceScore = $scoreComponents['attendance'] ?? 0;
        if ($attendanceScore < 0.7) {
            $insights[] = [
                'type' => 'warning',
                'icon' => 'bi-clock-history',
                'title' => 'Boost Your Attendance Score',
                'message' => 'Your attendance is tracking lower than the organization average. Try to maintain consistent check-in hours to maximize the 35% attendance weight.'
            ];
        } elseif ($attendanceScore > 0.95) {
            $insights[] = [
                'type' => 'success',
                'icon' => 'bi-star',
                'title' => 'Stellar Attendance',
                'message' => 'You have near-perfect attendance! Keep this up to easily secure the top performance tier.'
            ];
        }

        // 2. Analyze Tasks (20% weight)
        $taskScore = $scoreComponents['task'] ?? 0;
        if ($taskScore < 0.6) {
            $insights[] = [
                'type' => 'danger',
                'icon' => 'bi-list-check',
                'title' => 'Task Deadlines Need Attention',
                'message' => 'We noticed several missed or late tasks. Tip: Break large tasks into smaller sub-tasks and update their status daily.'
            ];
        } elseif ($taskScore > 0.9 && $taskScore < 1.0) {
            $insights[] = [
                'type' => 'info',
                'icon' => 'bi-clipboard-check',
                'title' => 'Strong Task Execution',
                'message' => 'Great job on your tasks. Ensure you complete them well before the deadline for the maximum bonus multiplier.'
            ];
        }

        // 3. Analyze Ratings (30% weight)
        $ratingScore = $scoreComponents['rating'] ?? 0;
        if ($ratingScore < 0.6) {
            $insights[] = [
                'type' => 'warning',
                'icon' => 'bi-chat-dots',
                'title' => 'Improve Peer Ratings',
                'message' => 'Your peer ratings are low. Consider scheduling 1-on-1s with your team members to ask for constructive feedback.'
            ];
        } elseif ($ratingScore > 0.8) {
            $insights[] = [
                'type' => 'success',
                'icon' => 'bi-people',
                'title' => 'Teamwork Multiplier Active',
                'message' => 'Your peers highly value your work! This high rating gives you an active multiplier on your overall score.'
            ];
        }

        // 4. Analyze Consistency (15% weight)
        $consistencyScore = $scoreComponents['consistency'] ?? 0;
        if ($consistencyScore < 0.5) {
            $insights[] = [
                'type' => 'warning',
                'icon' => 'bi-graph-down',
                'title' => 'Erratic Schedule Detected',
                'message' => 'Your daily working hours fluctuate significantly. A more regular routine will boost your consistency score.'
            ];
        }

        // 5. Check System Flags for specific penalties
        $hasLateLogins = false;
        $hasMissedDeadlines = false;
        foreach ($scoreFlags as $flag) {
            if (str_starts_with($flag, 'penalty:late_login')) {
                $hasLateLogins = true;
            }
            if (str_starts_with($flag, 'penalty:missed_deadline')) {
                $hasMissedDeadlines = true;
            }
        }

        if ($hasLateLogins) {
            $insights[] = [
                'type' => 'danger',
                'icon' => 'bi-exclamation-triangle',
                'title' => 'Late Logins Penalizing Score',
                'message' => 'You are losing 2 points for every late login. Arriving 5 minutes earlier will immediately stop this point drain.'
            ];
        }

        if ($hasMissedDeadlines) {
            $insights[] = [
                'type' => 'danger',
                'icon' => 'bi-calendar-x',
                'title' => 'Severe Deadline Penalty',
                'message' => 'You are losing 5 points for every missed task deadline. Prioritize expiring tasks immediately to protect your rank.'
            ];
        }

        // Fallback if doing perfect
        if (empty($insights)) {
            $insights[] = [
                'type' => 'success',
                'icon' => 'bi-trophy',
                'title' => 'Peak Performance',
                'message' => 'You are performing exceptionally well across all metrics. Your AI Coach has no critical advice right now. Keep it up!'
            ];
        }

        // Limit to top 3 insights so it doesn't clutter the UI
        return array_slice($insights, 0, 3);
    }
}
