<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;

class SeedAttendanceAndCheckTasks extends Command
{
    protected $signature   = 'hr:seed-attendance-tasks';
    protected $description = 'Mark attendance for all employees and check task status';

    // Standard working hours
    const CHECK_IN  = '09:00';
    const CHECK_OUT = '18:00';
    const HOURS     = 9.0;

    public function handle(): int
    {
        $this->info('');
        $this->info('══════════════════════════════════════════');
        $this->info('  Attendance Seeder + Task Checker');
        $this->info('══════════════════════════════════════════');

        $employees = Employee::with('user')->get();

        if ($employees->isEmpty()) {
            $this->error('No employees found.');
            return self::FAILURE;
        }

        $this->info("Found {$employees->count()} employees.");
        $this->newLine();

        // ── HR user: full attendance from join date ──────────────────────
        $hrUser = User::where('email', 'arshdeep20050217@gmail.com')->first();
        $hrEmployee = $hrUser ? $employees->firstWhere('user_id', (string) $hrUser->id) : null;

        foreach ($employees as $employee) {
            $isHr       = $hrEmployee && (string)$employee->id === (string)$hrEmployee->id;
            $joinedDate = $this->parseJoinDate($employee->joined_date);

            if ($isHr) {
                // Full history from join date
                $startDate = $joinedDate ?? Carbon::parse('2025-01-01');
                $this->line("<options=bold>👤 {$employee->user->name} (HR) — seeding from {$startDate->toDateString()} to today</>");
                $this->seedAttendanceRange($employee, $startDate, Carbon::today());
            } else {
                // All other employees: only today
                $startDate = Carbon::today();
                $this->line("<options=bold>👤 {$employee->user->name} — marking today ({$startDate->toDateString()})</>");
                $this->seedAttendanceRange($employee, $startDate, Carbon::today());
            }

            // ── Check tasks ──────────────────────────────────────────────
            $this->checkTasks($employee);
            $this->newLine();
        }

        $this->info('✅ Done. Run php artisan hr:sync-live-scores to refresh leaderboard.');
        return self::SUCCESS;
    }

    // =========================================================================

    private function seedAttendanceRange(Employee $employee, Carbon $from, Carbon $to): void
    {
        $period   = CarbonPeriod::create($from->copy()->startOfDay(), $to->copy()->endOfDay());
        $inserted = 0;
        $skipped  = 0;

        foreach ($period as $date) {
            // Skip weekends
            if ($date->isWeekend()) {
                continue;
            }

            $dateStr = $date->toDateString();

            // Skip if attendance already exists for this date
            $exists = Attendance::where('employee_id', (string) $employee->id)
                ->where('date', $dateStr)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            Attendance::withoutEvents(function () use ($employee, $dateStr) {
                Attendance::create([
                    'employee_id' => (string) $employee->id,
                    'date'        => $dateStr,
                    'check_in'    => self::CHECK_IN,
                    'check_out'   => self::CHECK_OUT,
                    'total_hours' => self::HOURS,
                    'status'      => 'present',
                    'sync_status' => 'pending',
                ]);
            });

            $inserted++;
        }

        $this->line("  <fg=green>  ✓ Inserted {$inserted} attendance records</>, skipped {$skipped} existing.");
    }

    private function checkTasks(Employee $employee): void
    {
        $tasks = Task::where('employee_id', (string) $employee->id)->get();

        if ($tasks->isEmpty()) {
            $this->line('  <fg=gray>  ~ No tasks assigned.</> ');
            return;
        }

        $completed = $tasks->where('status', 'completed')->count();
        $pending   = $tasks->where('status', 'pending')->count();
        $overdue   = $tasks->filter(fn($t) =>
            $t->status !== 'completed' &&
            $t->deadline &&
            Carbon::parse($t->deadline)->isPast()
        )->count();

        $this->line("  <fg=cyan>  📋 Tasks:</> Total: {$tasks->count()} | "
            . "<fg=green>Done: {$completed}</> | "
            . "<fg=yellow>Pending: {$pending}</> | "
            . "<fg=red>Overdue: {$overdue}</>");

        if ($overdue > 0) {
            // Auto-mark overdue tasks as missed
            Task::where('employee_id', (string) $employee->id)
                ->where('status', 'pending')
                ->get()
                ->each(function ($task) {
                    if ($task->deadline && Carbon::parse($task->deadline)->isPast()) {
                        $task->withoutEvents(fn() => $task->update(['status' => 'missed']));
                    }
                });

            $this->line("  <fg=red>  ⚡ {$overdue} overdue tasks marked as missed.</>");
        }
    }

    private function parseJoinDate($raw): ?Carbon
    {
        if (! $raw) {
            return null;
        }

        try {
            if (is_array($raw) && isset($raw['$date']['$numberLong'])) {
                return Carbon::createFromTimestampMs((int) $raw['$date']['$numberLong']);
            }
            return Carbon::parse((string) $raw);
        } catch (\Throwable) {
            return null;
        }
    }
}
