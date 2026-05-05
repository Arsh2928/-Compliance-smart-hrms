<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contract;
use App\Models\Payroll;
use App\Models\Attendance;
use Carbon\Carbon;

class GenerateMonthlyPayroll extends Command
{
    protected $signature = 'payroll:generate {--month=} {--year=}';
    protected $description = 'Auto-generate monthly payroll for all active employees based on attendance and contract.';

    public function handle()
    {
        $month = $this->option('month') ?: now()->subMonth()->month;
        $year  = $this->option('year') ?: now()->subMonth()->year;
        
        $date = Carbon::create($year, $month, 1);
        $monthStr = str_pad($month, 2, '0', STR_PAD_LEFT);
        $daysInMonth = $date->daysInMonth;
        
        $this->info("Generating Payroll for $monthStr-$year...");

        $contracts = Contract::with('employee')->where('status', 'active')->get();
        
        $count = 0;
        foreach ($contracts as $contract) {
            $employee = $contract->employee;
            if (!$employee) continue;

            // Check if payroll already exists
            $exists = Payroll::where('employee_id', $employee->id)
                             ->where('month', (int)$month)
                             ->where('year', (int)$year)
                             ->exists();
            if ($exists) {
                $this->warn("Payroll already exists for {$employee->employee_code}");
                continue;
            }

            // Calculate attendance
            $startDate = "$year-$monthStr-01";
            $endDate = Carbon::create($year, $month, 1)->endOfMonth()->format('Y-m-d');

            $attendances = Attendance::where('employee_id', $employee->id)
                                     ->whereBetween('date', [$startDate, $endDate])
                                     ->get();

            $daysPresent = $attendances->count();
            // Simple assumption: 22 working days in a month.
            $workingDays = 22; 
            
            $basicSalary = $contract->basic_salary ?? 0;
            $dailyRate = $basicSalary / $workingDays;
            $hourlyRate = $dailyRate / 8;

            $deductions = 0;
            if ($daysPresent < $workingDays) {
                // Not considering approved leaves for simplicity right now
                $daysAbsent = $workingDays - $daysPresent;
                $deductions = $daysAbsent * $dailyRate;
            }

            $totalHoursWorked = $attendances->sum('total_hours');
            $expectedHours = $daysPresent * 8;
            $overtimeHours = max(0, $totalHoursWorked - $expectedHours);
            $overtimePay = $overtimeHours * $hourlyRate * 1.5;

            $netSalary = $basicSalary + $overtimePay - $deductions;

            Payroll::create([
                'employee_id'    => $employee->id,
                'month'          => (int)$month,
                'year'           => (int)$year,
                'basic_salary'   => round($basicSalary, 2),
                'overtime_hours' => round($overtimeHours, 2),
                'overtime_pay'   => round($overtimePay, 2),
                'deductions'     => round($deductions, 2),
                'net_salary'     => round($netSalary, 2),
                'status'         => 'pending',
            ]);

            $count++;
        }

        $this->info("Successfully generated $count payroll records.");
    }
}
