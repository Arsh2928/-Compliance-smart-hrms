<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\MonthlyReward;
use App\Models\PerformanceRecord;
use App\Models\User;
use App\Services\RewardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RewardController extends Controller
{
    protected $rewardService;

    public function __construct(RewardService $rewardService)
    {
        $this->rewardService = $rewardService;
    }

    // =========================================================================
    // LEADERBOARD — cached, paginated, optimised for 10k+ employees
    // =========================================================================

    public function leaderboard(Request $request)
    {
        // ── Resolve month ──────────────────────────────────────────────
        $latestMonth = Cache::remember('leaderboard_latest_month', 3600, function () {
            return PerformanceRecord::max('month') ?? now()->format('Y-m');
        });
        $month = $request->get('month', $latestMonth);

        // ── Department filter — 2-step indexed query (avoids N+1 joins) ──
        $departmentId = $request->get('department_id');
        $empIdFilter  = null;

        if ($departmentId) {
            $empIdFilter = Employee::where('department_id', $departmentId)->pluck('id')->toArray();
            if (empty($empIdFilter)) {
                // No employees in this department — return empty result immediately
                return view('rewards.leaderboard', [
                    'leaderboard' => collect()->paginate(10),
                    'departments' => Department::all(),
                    'month'       => $month,
                    'topThree'    => [],
                ]);
            }
        }

        // ── Build cache key (month + optional department) ──────────────
        $cacheKey = "leaderboard:{$month}" . ($departmentId ? ":dept:{$departmentId}" : '');
        $page     = $request->get('page', 1);

        // Top 3 cached separately (never paginated)
        $topThree = Cache::remember("{$cacheKey}:top3", 86400, function () use ($month, $empIdFilter) {
            $query = PerformanceRecord::with('employee.user', 'employee.department')
                ->where('month', $month)
                ->orderBy('rank', 'asc')
                ->limit(3);

            if ($empIdFilter !== null) {
                $query->whereIn('employee_id', $empIdFilter);
            }

            return $query->get()->map(fn($r) => $this->formatRecord($r));
        });

        // ── Main paginated leaderboard ─────────────────────────────────
        $leaderboard = Cache::remember("{$cacheKey}:page:{$page}", 86400, function () use ($month, $empIdFilter, $request) {
            $query = PerformanceRecord::with('employee.user', 'employee.department')
                ->where('month', $month)
                ->orderBy('rank', 'asc');

            if ($empIdFilter !== null) {
                $query->whereIn('employee_id', $empIdFilter);
            }

            $paginator = $query->paginate(15);
            $paginator->getCollection()->transform(fn($r) => $this->formatRecord($r));
            return $paginator;
        });

        $departments = Department::all();

        return view('rewards.leaderboard', compact('leaderboard', 'departments', 'month', 'topThree'));
    }

    /**
     * Format a PerformanceRecord into a clean view-model array.
     */
    private function formatRecord(PerformanceRecord $record): array
    {
        $employee = $record->employee;
        $user     = $employee?->user;

        $rankDelta = $record->rank_delta;
        $deltaIcon = match(true) {
            $rankDelta === null  => '—',
            $rankDelta > 0       => "▲ {$rankDelta}",
            $rankDelta < 0       => "▼ " . abs($rankDelta),
            default              => '=',
        };

        return [
            'employee_id'   => $employee?->id,
            'user_id'       => $employee?->user_id,
            'name'          => $user?->name ?? 'Unknown',
            'initials'      => strtoupper(substr($user?->name ?? 'U', 0, 1)),
            'employee_code' => $employee?->employee_code ?? '—',
            'department'    => $employee?->department?->name ?? '—',
            'rank'          => $record->rank,
            'rank_delta'    => $rankDelta,
            'delta_icon'    => $deltaIcon,
            'final_score'   => round((float)$record->final_score, 1),
            'reward_tier'   => $record->reward_tier ?? 'None',
            'percentile'    => $record->percentile ?? 0,
            'badges'        => $employee?->badges ?? [],
            'flags'         => $record->flags ?? [],
        ];
    }

    // =========================================================================
    // REWARD CENTRE
    // =========================================================================

    public function rewardsCenter()
    {
        $employee = auth()->user()->employee;

        $rewards = [
            ['id' => 1, 'name' => 'Amazon Gift Card ₹2000', 'cost' => 500,  'icon' => 'bi-gift'],
            ['id' => 2, 'name' => 'Extra Paid Leave Day',   'cost' => 800,  'icon' => 'bi-calendar-plus'],
            ['id' => 3, 'name' => 'Free Lunch Voucher',     'cost' => 200,  'icon' => 'bi-cup-hot'],
            ['id' => 4, 'name' => 'Team Outing Pass',       'cost' => 1000, 'icon' => 'bi-people'],
        ];

        return view('rewards.index', compact('employee', 'rewards'));
    }

    public function redeemReward(Request $request)
    {
        $request->validate([
            'reward_id' => 'required',
            'cost'      => 'required|numeric|min:1',
        ]);

        $employee = auth()->user()->employee;

        if (!$employee) {
            return back()->with('error', 'Only employees can redeem rewards.');
        }

        if (($employee->total_points ?? 0) < $request->cost) {
            return back()->with('error', "Insufficient points. You have " . ($employee->total_points ?? 0) . " pts, need {$request->cost}.");
        }

        $employee->total_points -= (int) $request->cost;
        $employee->save();

        return back()->with('success', 'Reward redeemed! Your manager will follow up shortly.');
    }

    // =========================================================================
    // HR: RATE EMPLOYEE
    // =========================================================================

    public function rateEmployee(Request $request, Employee $employee)
    {
        if (!in_array(auth()->user()->role, ['admin', 'hr'])) {
            return back()->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'rating' => 'required|numeric|min:1|max:5',
        ]);

        $employee->rating = $request->rating;
        $employee->save();

        $this->rewardService->updatePerformanceScore($employee);

        return back()->with('success', 'Rating updated and performance score recalculated.');
    }
}
