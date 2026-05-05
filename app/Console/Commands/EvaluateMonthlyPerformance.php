<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\User;
use App\Models\PerformanceRecord;
use App\Models\MonthlyReward;
use App\Models\Alert;
use App\Services\ScoringService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EvaluateMonthlyPerformance extends Command
{
    protected $signature   = 'hr:evaluate-monthly-performance {--month= : Override evaluated month (Y-m)}';
    protected $description = 'Calculates monthly performance scores, assigns reward tiers, tracks rank movement.';

    public function handle(ScoringService $scoringService)
    {
        $this->info('═══════════════════════════════════════════');
        $this->info('  Monthly Performance Evaluation Starting   ');
        $this->info('═══════════════════════════════════════════');

        $month = $this->option('month') ?? now()->subMonth()->format('Y-m');
        $this->info("  Evaluating month: {$month}");

        // ── Idempotency guard ──────────────────────────────────────────
        if (PerformanceRecord::where('month', $month)->exists()) {
            $this->error("Evaluation for {$month} already processed. Skipping.");
            return self::FAILURE;
        }

        // ── Fetch employees ────────────────────────────────────────────
        $employeeUserIds = User::where('role', 'employee')->pluck('id');
        $employees       = Employee::whereIn('user_id', $employeeUserIds)->get();

        if ($employees->isEmpty()) {
            $this->warn('No employees found to evaluate.');
            return self::FAILURE;
        }

        $this->info("  Employees found: {$employees->count()}");

        // ── Fetch prior month's ranks for delta calculation ────────────
        $priorMonth = now()->subMonths(2)->format('Y-m');
        $priorRanks = PerformanceRecord::where('month', $priorMonth)
            ->pluck('rank', 'employee_id')
            ->toArray();

        // ── Score all employees ────────────────────────────────────────
        $evaluated = [];

        foreach ($employees as $employee) {
            try {
                $result = $scoringService->computeScore($employee, $month);
            } catch (\Throwable $e) {
                Log::error("Score computation failed for employee {$employee->id}: " . $e->getMessage());
                $this->warn("  ⚠ Skipped employee {$employee->employee_code}: {$e->getMessage()}");
                continue;
            }

            $evaluated[] = [
                'employee'   => $employee,
                'live_score' => $result['live_score'],
                'components' => $result['components'],
                'flags'      => $result['flags'],
                'attendance' => $result['components']['attendance'] ?? 0,
                'rating'     => $result['components']['rating']     ?? 0,
            ];
        }

        if (empty($evaluated)) {
            $this->error('All employee score computations failed. Aborting.');
            $this->notifyAdmins("⚠ Monthly evaluation FAILED for {$month} — no scores computed. Check logs.");
            return self::FAILURE;
        }

        // ── Tie-breaker sort: score → attendance → rating → id ──────────────
        usort($evaluated, function ($a, $b) {
            if ($a['live_score'] !== $b['live_score']) {
                return $b['live_score'] <=> $a['live_score']; // Descending
            }
            if ($a['attendance'] !== $b['attendance']) {
                return $b['attendance'] <=> $a['attendance']; // Descending
            }
            if ($a['rating'] !== $b['rating']) {
                return $b['rating'] <=> $a['rating']; // Descending
            }
            return strcmp($a['employee']->id, $b['employee']->id); // Ascending ID fallback
        });

        $totalEmployees = count($evaluated);
        $level5Limit    = (int) ceil($totalEmployees * 0.10); // Top 10%
        $level4Limit    = $level5Limit + (int) ceil($totalEmployees * 0.20); // Next 20%
        $level3Limit    = $level4Limit + (int) ceil($totalEmployees * 0.30); // Next 30%
        $level2Limit    = $level3Limit + (int) ceil($totalEmployees * 0.20); // Next 20%

        $this->info("  Level Cutoffs — L5: top {$level5Limit}, L4: up to {$level4Limit}, L3: up to {$level3Limit}, L2: up to {$level2Limit}");

        // ── Atomic transaction ─────────────────────────────────────────
        try {
            DB::connection('mongodb')->transaction(function () use (
                $evaluated, $month, $level5Limit, $level4Limit, $level3Limit, $level2Limit, $totalEmployees, $priorRanks
            ) {
                foreach ($evaluated as $index => $data) {
                    $employee  = $data['employee'];
                    $rank      = $index + 1;
                    $percentile = (int) round((($totalEmployees - $rank) / $totalEmployees) * 100);

                    // Rank delta vs previous month
                    $priorRank  = $priorRanks[$employee->id] ?? null;
                    $rankDelta  = $priorRank ? ($priorRank - $rank) : null;

                    $tier  = 'Level 1';
                    $bonus = 10;

                    if ($rank <= $level5Limit)        { $tier = 'Level 5'; $bonus = 200; }
                    elseif ($rank <= $level4Limit)    { $tier = 'Level 4'; $bonus = 100; }
                    elseif ($rank <= $level3Limit)    { $tier = 'Level 3'; $bonus = 50; }
                    elseif ($rank <= $level2Limit)    { $tier = 'Level 2'; $bonus = 25; }

                    // Badges Assignment Logic
                    $earnedBadges = [];
                    if ($rank === 1) $earnedBadges[] = 'Top Performer'; // Rank 1 gets Top Performer
                    if (($data['components']['streak_days'] ?? 0) >= 20) $earnedBadges[] = 'Consistency King';
                    if (($data['components']['rating_meta']['final_avg'] ?? 0) > 4.5) $earnedBadges[] = 'Team Player';

                    // ── Save PerformanceRecord using updateOrCreate ──
                    PerformanceRecord::updateOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'month'       => $month,
                        ],
                        [
                            'live_score'            => $data['live_score'],
                            'final_score'           => $data['live_score'],
                            'attendance_component'  => $data['components']['attendance'] ?? 0,
                            'rating_component'      => $data['components']['rating']     ?? 0,
                            'task_component'        => $data['components']['task']       ?? 0,
                            'consistency_component' => $data['components']['consistency'] ?? 0,
                            'average_rating'        => ($data['components']['rating_meta']['final_avg'] ?? 0),
                            'streak_days'           => $data['components']['streak_days'] ?? 0,
                            'rank'                  => $rank,
                            'rank_delta'            => $rankDelta,
                            'percentile'            => $percentile,
                            'reward_tier'           => $tier,
                            'flags'                 => $data['flags'],
                        ]
                    );

                    // ── Monthly Reward (idempotent) ───────────────────
                    MonthlyReward::updateOrCreate(
                        ['employee_id' => $employee->id, 'month' => $month],
                        [
                            'rank'                 => $rank,
                            'percentile'          => $percentile,
                            'reward_tier'         => $tier,
                            'bonus_points_awarded' => $bonus,
                            'rank_delta'          => $rankDelta,
                        ]
                    );

                    $employee->points = ($employee->points ?? 0) + $bonus;
                    
                    $currentBadges = $employee->badges ?? [];
                    $newBadges = [];
                    foreach ($earnedBadges as $b) {
                        if (!in_array($b, $currentBadges)) {
                            $currentBadges[] = $b;
                            $newBadges[] = $b;
                        }
                    }
                    $employee->badges = $currentBadges;
                    $employee->save();

                    $badgeMsg = !empty($newBadges) ? " You also unlocked: " . implode(', ', $newBadges) . "!" : "";

                    Alert::create([
                        'user_id'  => $employee->user_id,
                        'message'  => "🎉 You reached {$tier} for {$month}! Rank #{$rank} · +{$bonus} pts.{$badgeMsg}",
                        'type'     => 'success',
                        'is_read'  => false,
                    ]);
                }
            });
        } catch (\Throwable $e) {
            Log::critical("Monthly evaluation FAILED for {$month}: " . $e->getMessage());
            $this->error("❌ Critical failure: " . $e->getMessage());
            $this->notifyAdmins("❌ Monthly evaluation FAILED for {$month}. Error: {$e->getMessage()}");
            return self::FAILURE;
        }

        // ── Invalidate leaderboard cache ───────────────────────────────
        Cache::forget("leaderboard:{$month}");
        Cache::forget("top_performers:{$month}");

        $this->info("✅ Evaluation complete — {$totalEmployees} employees processed for {$month}.");
        return self::SUCCESS;
    }

    /**
     * Dispatch alert to all admin users on cron failure.
     */
    private function notifyAdmins(string $message): void
    {
        $adminIds = User::where('role', 'admin')->pluck('id');
        foreach ($adminIds as $adminId) {
            Alert::create([
                'user_id'  => $adminId,
                'message'  => $message,
                'type'     => 'danger',
                'is_read'  => false,
            ]);
        }
    }
}
