<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contract;
use App\Models\Payroll;
use App\Models\Attendance;
use App\Models\Alert;
use Carbon\Carbon;

class GenerateMonthlyPayroll extends Command
{
    protected $signature = 'payroll:generate {--month=} {--year=}';
    protected $description = 'Auto-generate monthly payroll for all active employees based on attendance, department, and contract.';

    /**
     * Overtime rate per hour in INR, keyed by department name (lowercase).
     */
    protected array $departmentOvertimeRates = [
        'it'               => 1800,  // ₹1800/hr
        'engineering'      => 1800,  // ₹1800/hr
        'finance'          => 1500,  // ₹1500/hr
        'legal'            => 1500,  // ₹1500/hr
        'sales'            => 1100,  // ₹1100/hr
        'marketing'        => 1100,  // ₹1100/hr
        'design'           => 1100,  // ₹1100/hr
        'operations'       => 900,   // ₹900/hr
        'customer support' => 900,   // ₹900/hr
        'hr'               => 900,   // ₹900/hr
    ];

    /** Tax rate: 10% on gross (basic + overtime) */
    protected float $taxRate = 0.10;

    public function handle()
    {
        $month = $this->option('month') ?: now()->subMonth()->month;
        $year  = $this->option('year') ?: now()->subMonth()->year;

        $date     = Carbon::create($year, $month, 1);
        $monthStr = str_pad($month, 2, '0', STR_PAD_LEFT);

        $this->info("Generating Payroll for $monthStr-$year...");

        $contracts = Contract::with('employee.department')->where('status', 'active')->get();

        $count = 0;
        foreach ($contracts as $contract) {
            $employee = $contract->employee;
            if (!$employee) continue;

            // Skip admin users
            $user = $employee->user;
            if (!$user || $user->role === 'admin') continue;

            // Skip if payroll already exists for this month
            $exists = Payroll::where('employee_id', $employee->id)
                             ->where('month', (int)$month)
                             ->where('year', (int)$year)
                             ->exists();
            if ($exists) {
                $this->warn("Payroll already exists for {$employee->employee_code}");
                continue;
            }

            // Attendance for the month
            $startDate   = "$year-$monthStr-01";
            $endDate     = Carbon::create($year, $month, 1)->endOfMonth()->format('Y-m-d');
            $attendances = Attendance::where('employee_id', $employee->id)
                                     ->whereBetween('date', [$startDate, $endDate])
                                     ->get();

            $workingDays = 22; // Standard working days assumption
            $daysPresent = $attendances->count();
            $basicSalary = $contract->basic_salary ?? 0;
            $dailyRate   = $basicSalary / $workingDays;

            // Absent deduction
            $absentDeduction = 0;
            if ($daysPresent < $workingDays) {
                $daysAbsent      = $workingDays - $daysPresent;
                $absentDeduction = $daysAbsent * $dailyRate;
            }

            // Overtime calculation using department-specific rate
            $totalHoursWorked = $attendances->sum('total_hours');
            $expectedHours    = $daysPresent * 8;
            $overtimeHours    = max(0, $totalHoursWorked - $expectedHours);

            $deptName   = strtolower($employee->department->name ?? '');
            $overtimeRate = $this->departmentOvertimeRates[$deptName] 
                            ?? 900; // Default ₹900/hr for unlisted departments
            $overtimePay  = round($overtimeHours * $overtimeRate, 2);

            // Tax deduction (10% of gross)
            $gross   = $basicSalary + $overtimePay;
            $taxDeduction = round($gross * $this->taxRate, 2);

            // Total deductions = absent penalty + tax
            $totalDeductions = round($absentDeduction + $taxDeduction, 2);
            $netSalary       = round($gross - $totalDeductions, 2);

            Payroll::create([
                'employee_id'    => $employee->id,
                'month'          => (int)$month,
                'year'           => (int)$year,
                'basic_salary'   => round($basicSalary, 2),
                'overtime_hours' => round($overtimeHours, 2),
                'overtime_pay'   => $overtimePay,
                'deductions'     => $totalDeductions,
                'net_salary'     => $netSalary,
                'status'         => 'pending',
            ]);

            // Notify employee of new payslip
            if ($user) {
                Alert::create([
                    'user_id' => $user->id,
                    'type'    => 'info',
                    'message' => "Your payslip for " . Carbon::create()->month($month)->format('F') . " $year is ready. Net Salary: ₹" . number_format($netSalary, 2) . ". Status: Pending approval.",
                    'is_read' => false,
                    'link'    => '#',
                ]);
            }

            $this->info("Generated payroll for {$employee->employee_code} — Net: ₹{$netSalary}");
            $count++;
        }

        $this->info("Successfully generated $count payroll records.");
    }
}
