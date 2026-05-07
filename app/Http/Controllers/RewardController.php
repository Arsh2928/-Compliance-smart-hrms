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
        $topThree = Cache::remember("{$cacheKey}:top3", 300, function () use ($month, $empIdFilter) {
            $query = PerformanceRecord::with('employee.user', 'employee.department')
                ->where('month', $month);

            if ($empIdFilter !== null) {
                $query->whereIn('employee_id', $empIdFilter);
            }

            $allRecords = $query->get()->sortByDesc(function($r) {
                return $r->final_score ?? $r->live_score ?? 0;
            })->values();

            $total = $allRecords->count();
            foreach ($allRecords as $index => $r) {
                $r->dynamic_rank = $index + 1;
                $r->dynamic_percentile = $total > 0 ? max(1, round((1 - ($index / $total)) * 100)) : 0;
            }

            return $allRecords->take(3)->map(fn($r) => $this->formatRecord($r));
        });

        // ── Main paginated leaderboard ─────────────────────────────────
        $leaderboard = Cache::remember("{$cacheKey}:page:{$page}", 300, function () use ($month, $empIdFilter, $page) {
            $query = PerformanceRecord::with('employee.user', 'employee.department')
                ->where('month', $month);

            if ($empIdFilter !== null) {
                $query->whereIn('employee_id', $empIdFilter);
            }

            $allRecords = $query->get()->sortByDesc(function($r) {
                return $r->final_score ?? $r->live_score ?? 0;
            })->values();

            $total = $allRecords->count();
            foreach ($allRecords as $index => $r) {
                $r->dynamic_rank = $index + 1;
                $r->dynamic_percentile = $total > 0 ? max(1, round((1 - ($index / $total)) * 100)) : 0;
            }

            $slice = $allRecords->slice(($page - 1) * 15, 15)->values();
            $paginator = new \Illuminate\Pagination\LengthAwarePaginator($slice, $total, 15, $page, [
                'path' => request()->url(),
                'query' => request()->query()
            ]);

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
            'rank'          => $record->dynamic_rank ?? $record->rank,
            'rank_delta'    => $rankDelta,
            'delta_icon'    => $deltaIcon,
            'final_score'   => round((float)($record->final_score ?? $record->live_score ?? 0), 1),
            'reward_tier'   => $record->reward_tier ?? 'None',
            'percentile'    => $record->dynamic_percentile ?? $record->percentile ?? 0,
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
            ['id' => 1, 'name' => 'Premium Coffee Shop Voucher', 'cost' => 300,  'icon' => 'bi-cup-hot'],
            ['id' => 2, 'name' => 'Extra Paid Leave Day',        'cost' => 800,  'icon' => 'bi-calendar-plus'],
            ['id' => 3, 'name' => 'Company Merchandise',         'cost' => 500,  'icon' => 'bi-bag-heart'],
            ['id' => 4, 'name' => 'Team Outing Pass',            'cost' => 1000, 'icon' => 'bi-people'],
        ];

        // Fetch user's vouchers
        $myVouchers = collect($employee->owned_vouchers ?? [])->sortByDesc('redeemed_at')->values()->all();

        return view('rewards.index', compact('employee', 'rewards', 'myVouchers'));
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

        if (($employee->points ?? 0) < $request->cost) {
            return back()->with('error', "Insufficient points. You have " . ($employee->points ?? 0) . " pts, need {$request->cost}.");
        }

        $rewardName = $request->input('reward_name', 'Reward Voucher');

        $employee->points -= (int) $request->cost;
        
        $vouchers = $employee->owned_vouchers ?? [];
        $vouchers[] = [
            'voucher_id'  => uniqid('VOUCHER-'),
            'reward_id'   => $request->reward_id,
            'reward_name' => $rewardName,
            'cost'        => $request->cost,
            'redeemed_at' => now()->toDateTimeString(),
            'is_used'     => false,
            'used_at'     => null,
        ];
        
        $employee->owned_vouchers = $vouchers;
        $employee->save();

        return back()->with('success', 'Reward redeemed successfully! You can now use this voucher in the organization.');
    }

    public function useVoucher(Request $request)
    {
        $request->validate([
            'voucher_id' => 'required|string',
        ]);

        $employee = auth()->user()->employee;
        if (!$employee) return back()->with('error', 'Only employees can use vouchers.');

        $vouchers = $employee->owned_vouchers ?? [];
        $found = false;

        foreach ($vouchers as &$v) {
            if ($v['voucher_id'] === $request->voucher_id && !$v['is_used']) {
                $v['is_used'] = true;
                $v['used_at'] = now()->toDateTimeString();
                $found = true;
                break;
            }
        }

        if (!$found) {
            return back()->with('error', 'Voucher not found or already used.');
        }

        $employee->owned_vouchers = $vouchers;
        $employee->save();

        return back()->with('success', 'Voucher marked as used successfully!');
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
