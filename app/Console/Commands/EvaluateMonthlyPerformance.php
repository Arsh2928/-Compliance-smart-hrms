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

        // ── Tie-breaker sort: score → attendance → rating ──────────────
        usort($evaluated, function ($a, $b) {
            if ($a['live_score'] !== $b['live_score']) {
                return $b['live_score'] <=> $a['live_score'];
            }
            if ($a['attendance'] !== $b['attendance']) {
                return $b['attendance'] <=> $a['attendance'];
            }
            return $b['rating'] <=> $a['rating'];
        });

        $totalEmployees = count($evaluated);
        $goldLimit      = (int) ceil($totalEmployees * 0.10);
        $silverLimit    = $goldLimit + (int) ceil($totalEmployees * 0.20);
        $bronzeLimit    = $silverLimit + (int) ceil($totalEmployees * 0.30);

        $this->info("  Tier cutoffs — Gold: top {$goldLimit}, Silver: next " . ($silverLimit - $goldLimit) . ", Bronze: next " . ($bronzeLimit - $silverLimit));

        // ── Atomic transaction ─────────────────────────────────────────
        try {
            DB::connection('mongodb')->transaction(function () use (
                $evaluated, $month, $goldLimit, $silverLimit, $bronzeLimit, $totalEmployees, $priorRanks
            ) {
                foreach ($evaluated as $index => $data) {
                    $employee  = $data['employee'];
                    $rank      = $index + 1;
                    $percentile = (int) round((($totalEmployees - $rank) / $totalEmployees) * 100);

                    // Rank delta vs previous month
                    $priorRank  = $priorRanks[$employee->id] ?? null;
                    $rankDelta  = $priorRank ? ($priorRank - $rank) : null; // positive = improved
                    $scoreDelta = null; // could compute from prior PerformanceRecord

                    $tier  = 'None';
                    $bonus = 0;

                    if ($rank <= $goldLimit)        { $tier = 'Gold';   $bonus = 200; }
                    elseif ($rank <= $silverLimit)  { $tier = 'Silver'; $bonus = 100; }
                    elseif ($rank <= $bronzeLimit)  { $tier = 'Bronze'; $bonus = 50; }

                    // ── Save PerformanceRecord (now with rank, rank_delta) ──
                    PerformanceRecord::create([
                        'employee_id'           => $employee->id,
                        'month'                 => $month,
                        'live_score'            => $data['live_score'],  // NEW: separate live_score
                        'final_score'           => $data['live_score'],  // snapshot = live at evaluation time
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
                    ]);

                    // ── Monthly Reward (top tiers only) ───────────────────
                    if ($tier !== 'None') {
                        MonthlyReward::create([
                            'employee_id'         => $employee->id,
                            'month'               => $month,
                            'rank'                => $rank,
                            'percentile'          => $percentile,
                            'reward_tier'         => $tier,
                            'bonus_points_awarded'=> $bonus,
                            'rank_delta'          => $rankDelta,
                        ]);

                        $employee->total_points = ($employee->total_points ?? 0) + $bonus;
                        $badges = $employee->badges ?? [];
                        if (!in_array($tier, $badges)) {
                            $badges[]        = $tier;
                            $employee->badges = $badges;
                        }

                        Alert::create([
                            'user_id'  => $employee->user_id,
                            'message'  => "🎉 You earned the {$tier} reward for {$month}! Rank #{$rank} · +{$bonus} points.",
                            'type'     => 'success',
                            'is_read'  => false,
                        ]);
                    }

                    // ── Reset monthly attendance points ───────────────────
                    $employee->attendance_points = 0;
                    $employee->save();
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
