<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckCompliance extends Command
{
    protected $signature = 'compliance:check';
    protected $description = 'Checks for expiring contracts, pending complaints, and attendance anomalies.';

    public function handle()
    {
        $this->info('Running compliance checks...');

        // 1. Expiring Contracts (Within 30 days)
        $expiringContracts = \App\Models\Contract::where('status', 'active')
            ->whereBetween('end_date', [now(), now()->addDays(30)])
            ->get();

        foreach ($expiringContracts as $contract) {
            \App\Models\Alert::firstOrCreate([
                'employee_id' => $contract->employee_id,
                'type' => 'contract_expiry',
                'message' => 'Contract for ' . $contract->employee->user->name . ' is expiring on ' . \Carbon\Carbon::parse($contract->end_date)->format('Y-m-d'),
                'is_read' => false
            ]);
        }

        // 2. Overtime Anomalies (More than 10 hours overtime a week is a compliance risk)
        $attendances = \App\Models\Attendance::where('date', '>=', now()->subDays(7))->get();
        $grouped = $attendances->groupBy('employee_id');

        foreach ($grouped as $employee_id => $records) {
            $totalHours = $records->sum('total_hours');
            if ($totalHours > 50) { // Assuming 40 hour work week, >50 means >10h overtime
                $employee = \App\Models\Employee::find($employee_id);
                \App\Models\Alert::firstOrCreate([
                    'employee_id' => $employee_id,
                    'type' => 'excessive_overtime',
                    'message' => $employee->user->name . ' has worked ' . round($totalHours, 2) . ' hours in the past 7 days. This may violate labor laws.',
                    'is_read' => false
                ]);
            }
        }

        $this->info('Compliance checks completed.');
    }
}
