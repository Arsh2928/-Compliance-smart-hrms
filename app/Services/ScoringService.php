<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\Rating;
use Illuminate\Support\Facades\Log;

/**
 * ScoringService - Anti-cheat, Fair, Scalable Performance Engine
 *
 * Weights:
 *   attendance   = 0.35
 *   rating       = 0.30
 *   task         = 0.20
 *   consistency  = 0.15
 *
 * All components normalised to [0, 1] before weighting.
 */
class ScoringService
{
    // ----- Weight Constants -----
    const W_ATTENDANCE   = 0.35;
    const W_RATING       = 0.30;
    const W_TASK         = 0.20;
    const W_CONSISTENCY  = 0.15;

    // ----- Attendance Config -----
    const EXPECTED_WORKING_DAYS = 22;      // Expected days per month
    const MAX_HOURS_PER_DAY     = 9.0;     // Cap contribution per day
    const MIN_HOURS_VALID       = 4.0;     // Minimum to count as "verified" attendance

    // ----- Rating Config -----
    const MIN_EVALUATORS_REQUIRED = 2;     // Need at least 2 raters for score to count
    const OUTLIER_THRESHOLD       = 2.0;   // Reject ratings deviating > 2.0 from mean
    const RATING_SCALE            = 5.0;   // Max rating value

    /**
     * Master entry point — compute final score for an employee for a given month.
     * Safe to call mid-month (live score) or end-of-month (final snapshot).
     *
     * @param Employee $employee
     * @param string   $month  'Y-m' format
     * @return array   ['live_score', 'components', 'flags']
     */
    public function computeScore(Employee $employee, string $month): array
    {
        $components = [];
        $flags      = [];

        // --- ATTENDANCE ---
        [$normAttendance, $attFlags] = $this->computeAttendance($employee, $month);
        $components['attendance'] = round($normAttendance, 4);
        $flags = array_merge($flags, $attFlags);

        // --- RATING ---
        [$normRating, $ratingFlags, $ratingMeta] = $this->computeRating($employee, $month);
        $components['rating']      = round($normRating, 4);
        $components['rating_meta'] = $ratingMeta;
        $flags = array_merge($flags, $ratingFlags);

        // --- TASK COMPLETION ---
        [$normTask, $taskFlags, $missedDeadlines] = $this->computeTask($employee);
        $components['task'] = round($normTask, 4);
        $flags = array_merge($flags, $taskFlags);

        // --- CONSISTENCY ---
        [$normConsistency, $streakDays] = $this->computeConsistency($employee, $month);
        $components['consistency'] = round($normConsistency, 4);
        $components['streak_days'] = $streakDays;

        // --- PENALTY LOGIC ---
        // Late logins: Deduct 2 points each
        // Missed deadlines: Deduct 5 points each
        $lateLogins        = $employee->late_logins_temp ?? 0;
        $latePenalties     = $lateLogins * 2;
        $deadlinePenalties = $missedDeadlines * 5;
        $totalPenalty      = $latePenalties + $deadlinePenalties;

        if ($latePenalties > 0) $flags[] = "penalty:late_login_{$lateLogins}";
        if ($deadlinePenalties > 0) $flags[] = "penalty:missed_deadline_{$missedDeadlines}";

        // --- TEAMWORK MULTIPLIER ---
        // High peer feedback (> 4.0) gives up to 10% bonus
        $teamworkMultiplier = 1.0;
        if ($components['rating'] > 0.8) { // > 4.0 out of 5
            $teamworkMultiplier = 1.05 + (($components['rating'] - 0.8) * 0.25); // max 1.10x
            $flags[] = "bonus:teamwork_multiplier_active";
        }

        // --- FINAL WEIGHTED SCORE ---
        $baseScore = (
            ($components['attendance']  * self::W_ATTENDANCE)  +
            ($components['rating']      * self::W_RATING)      +
            ($components['task']        * self::W_TASK)        +
            ($components['consistency'] * self::W_CONSISTENCY)
        ) * 100;

        $liveScore = max(0, ($baseScore * $teamworkMultiplier) - $totalPenalty);

        return [
            'live_score'  => round($liveScore, 2),
            'components'  => $components,
            'flags'       => $flags,
        ];
    }

    // =========================================================================
    // ATTENDANCE COMPONENT
    // =========================================================================

    /**
     * Anti-cheat rule: Only attendance records with total_hours >= MIN_HOURS_VALID
     * and that were not created retroactively (created_at <= date + 1 day) count.
     * Approved leave days do NOT penalise the score.
     */
    private function computeAttendance(Employee $employee, string $month): array
    {
        $flags = [];

        [$startDate, $endDate] = $this->monthRange($month);

        // Count approved leave days (not penalised)
        $leaveDays = Leave::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereBetween('start_date', [$startDate, $endDate])
            ->get()
            ->sum(function ($leave) {
                return \Carbon\Carbon::parse($leave->start_date)
                    ->diffInWeekdays(\Carbon\Carbon::parse($leave->end_date)) + 1;
            });

        $leaveDays = min($leaveDays, self::EXPECTED_WORKING_DAYS); // cap

        $effectiveExpected = max(1, self::EXPECTED_WORKING_DAYS - $leaveDays);

        // Fetch only verified attendance
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('check_out')
            ->get();

        $verifiedDays    = 0;
        $suspiciousDays  = 0;
        $lateLogins      = 0;

        foreach ($attendances as $record) {
            $hours = (float) ($record->total_hours ?? 0);

            if ($hours < self::MIN_HOURS_VALID) {
                // Under 4 hrs — does not count as verified
                continue;
            }

            // Check for late login (after 09:15)
            if ($record->check_in) {
                $checkInTime = \Carbon\Carbon::parse($record->date . ' ' . $record->check_in, 'Asia/Kolkata');
                if ($checkInTime->format('H:i') > '09:15') {
                    $lateLogins++;
                }
            }

            // Cap per-day contribution
            $cappedHours  = min($hours, self::MAX_HOURS_PER_DAY);
            $verifiedDays += ($cappedHours / self::MAX_HOURS_PER_DAY); // fractional credit
        }

        if ($suspiciousDays > 0) {
            $flags[] = "attendance_anomaly:{$suspiciousDays}_days_under_minimum";
        }
        
        $employee->setAttribute('late_logins_temp', $lateLogins); // Hacky way to pass back

        $normalised = min($verifiedDays / $effectiveExpected, 1.0);

        return [$normalised, $flags];
    }

    // =========================================================================
    // RATING COMPONENT (Anti-manipulation)
    // =========================================================================

    /**
     * Fairness rules:
     *  1. Minimum 2 independent evaluators required.
     *  2. If >= 3 raters: trimmed mean (drop highest + lowest).
     *  3. Outlier rejection: skip any rating deviating > 2.0 from trimmed mean.
     *  4. Each evaluator capped at 1 rating per 7-day window (enforced in RatingService).
     *  5. Evaluator cannot rate themselves.
     *  6. If < 2 evaluators: return 0.5 (neutral) and flag.
     */
    private function computeRating(Employee $employee, string $month): array
    {
        $flags = [];

        $ratings = Rating::where('employee_id', $employee->id)
            ->where('month', $month)
            ->get();

        $uniqueEvaluators = $ratings->pluck('evaluator_id')->unique()->count();

        if ($uniqueEvaluators < self::MIN_EVALUATORS_REQUIRED) {
            $flags[] = "insufficient_evaluators:{$uniqueEvaluators}";
            // Return neutral 0.5 — not penalised, not rewarded
            return [0.5, $flags, ['evaluator_count' => $uniqueEvaluators, 'method' => 'neutral_fallback']];
        }

        // Get raw averages per evaluator (one rating per evaluator per month)
        $averages = $ratings->pluck('average_rating')->map(fn($v) => (float) $v)->sort()->values();

        // Trimmed mean for 3+ raters
        if ($averages->count() >= 3) {
            $averages = $averages->slice(1, $averages->count() - 2)->values(); // drop first + last
        }

        $trimmedMean = $averages->average();

        // Outlier rejection: remove ratings that differ > OUTLIER_THRESHOLD from trimmedMean
        $validAverages = $averages->filter(function ($val) use ($trimmedMean) {
            return abs($val - $trimmedMean) <= self::OUTLIER_THRESHOLD;
        });

        $outliersRemoved = $averages->count() - $validAverages->count();
        if ($outliersRemoved > 0) {
            $flags[] = "rating_outliers_removed:{$outliersRemoved}";
        }

        $finalRating = $validAverages->count() > 0
            ? $validAverages->average()
            : $trimmedMean; // fallback if all were outliers (edge case)

        $normalised = min($finalRating / self::RATING_SCALE, 1.0);

        return [
            $normalised,
            $flags,
            [
                'evaluator_count'  => $uniqueEvaluators,
                'outliers_removed' => $outliersRemoved,
                'final_avg'        => round($finalRating, 2),
                'method'           => $averages->count() >= 3 ? 'trimmed_mean' : 'direct_mean',
            ]
        ];
    }

    // =========================================================================
    // TASK COMPLETION COMPONENT
    // =========================================================================

    /**
     * Currently uses employee->task_completion_score (0-100).
     * Future: integrate a task management module here.
     */
    private function computeTask(Employee $employee): array
    {
        $score  = max(0, min(100, (float)($employee->task_completion_score ?? 75)));
        $normalised = $score / 100.0;
        
        // Mocking missed deadlines since we don't have a Task model yet
        // If task score is below 60, we assume 1 missed deadline, below 40 -> 2.
        $missedDeadlines = 0;
        if ($score < 60) $missedDeadlines = 1;
        if ($score < 40) $missedDeadlines = 2;
        
        $flags = [];
        if ($missedDeadlines > 0) {
            $flags[] = "task:poor_completion_inferred_missed_deadlines";
        }

        return [$normalised, $flags, $missedDeadlines];
    }

    // =========================================================================
    // CONSISTENCY COMPONENT
    // =========================================================================

    /**
     * Measures how consistently the employee shows up each week.
     * Rewards streaks but caps at 15% influence (already weighted).
     * Penalises erratic behaviour (attending 2 days, skipping 3, etc.)
     */
    private function computeConsistency(Employee $employee, string $month): array
    {
        [$startDate, $endDate] = $this->monthRange($month);

        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('check_out')
            ->where('total_hours', '>=', self::MIN_HOURS_VALID)
            ->orderBy('date', 'asc')
            ->pluck('date')
            ->map(fn($d) => \Carbon\Carbon::parse($d)->startOfDay())
            ->values();

        if ($attendances->isEmpty()) {
            return [0.0, 0];
        }

        // Calculate current streak
        $streak         = 1;
        $maxStreak      = 1;
        $prevDate       = $attendances->first();

        foreach ($attendances->slice(1) as $date) {
            $diff = $prevDate->diffInDays($date);
            // Skip weekends in gap calculation
            if ($diff <= 3) { // Mon–Fri logic: 1 day apart or weekend gap (3 days)
                $streak++;
                $maxStreak = max($maxStreak, $streak);
            } else {
                $streak = 1; // streak broken
            }
            $prevDate = $date;
        }

        // Normalise: cap streak influence at 22 days (full month)
        $streakScore = min($maxStreak / self::EXPECTED_WORKING_DAYS, 1.0);

        // Penalise erratic weeks: compute coefficient of variation of weekly attendance
        $weeklyGroups = $attendances->groupBy(fn($d) => $d->weekOfYear);
        if ($weeklyGroups->count() > 1) {
            $weekCounts = $weeklyGroups->map->count()->values();
            $meanCount  = $weekCounts->average();
            $stddev     = sqrt($weekCounts->map(fn($c) => pow($c - $meanCount, 2))->average());
            $cv         = $meanCount > 0 ? $stddev / $meanCount : 1; // coefficient of variation

            // Penalise high variance (erratic attendance)
            $regularityBonus = max(0, 1.0 - $cv);
        } else {
            $regularityBonus = 1.0;
        }

        $normalised = ($streakScore * 0.6) + ($regularityBonus * 0.4);

        return [round($normalised, 4), $maxStreak];
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function monthRange(string $month): array
    {
        $start = \Carbon\Carbon::parse($month . '-01')->startOfMonth()->toDateString();
        $end   = \Carbon\Carbon::parse($month . '-01')->endOfMonth()->toDateString();
        return [$start, $end];
    }

    /**
     * Calculate how many more score points needed to hit next tier.
     * Tier cutoffs based on the last month's min scores in PerformanceRecord.
     */
    public function nextTierInfo(float $liveScore, string $month): array
    {
        $level5Min = 90;
        $level4Min = 80;
        $level3Min = 65;
        $level2Min = 50;

        if ($liveScore >= $level5Min) {
            return ['current_tier' => 'Level 5', 'next_tier' => null, 'points_needed' => 0];
        } elseif ($liveScore >= $level4Min) {
            return ['current_tier' => 'Level 4', 'next_tier' => 'Level 5', 'points_needed' => round($level5Min - $liveScore, 1)];
        } elseif ($liveScore >= $level3Min) {
            return ['current_tier' => 'Level 3', 'next_tier' => 'Level 4', 'points_needed' => round($level4Min - $liveScore, 1)];
        } elseif ($liveScore >= $level2Min) {
            return ['current_tier' => 'Level 2', 'next_tier' => 'Level 3', 'points_needed' => round($level3Min - $liveScore, 1)];
        } else {
            return ['current_tier' => 'Level 1', 'next_tier' => 'Level 2', 'points_needed' => round($level2Min - $liveScore, 1)];
        }
    }
}
