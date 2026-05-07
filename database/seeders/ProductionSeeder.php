<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Payroll;
use App\Models\Contract;
use App\Models\Task;
use App\Models\Complaint;
use App\Models\Rating;
use App\Models\PerformanceRecord;
use App\Models\MonthlyReward;
use App\Models\Alert;
use App\Services\ScoringService;
use App\Services\RatingService;
use Illuminate\Support\Facades\Log;

class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting Production Data Seeding...');

        // Clear existing collections
        $this->clearCollections();

        // 1. Create Core Entities (Departments, Admins, HR)
        $departments = $this->createDepartments();
        $evaluators = $this->createEvaluators();

        // 2. Define Employee Profiles
        $profiles = $this->getEmployeeProfiles($departments);

        // Date Setup
        $currentDate = Carbon::now(); // May 2026
        $monthsToSimulate = [
            $currentDate->copy()->subMonths(3), // Feb
            $currentDate->copy()->subMonths(2), // Mar
            $currentDate->copy()->subMonths(1), // Apr
        ];
        $currentMonth = $currentDate->copy(); // May (Ongoing)

        $employees = [];

        // 3. Create Employees & Base Data
        foreach ($profiles as $index => $profile) {
            $empNum = $index + 1;
            
            // Create User
            $user = User::create([
                'name' => $profile['name'],
                'email' => "employee{$empNum}@compliancesys.com",
                'password' => Hash::make('password'),
                'role' => 'employee',
                'status' => 'approved',
                'email_verified_at' => Carbon::now(), // Verified
            ]);

            // Create Employee
            $joiningDate = $currentDate->copy()->subMonths(rand(6, 24))->startOfMonth();
            $employee = Employee::create([
                'user_id' => $user->id,
                'employee_code' => 'EMP-' . str_pad($empNum, 3, '0', STR_PAD_LEFT),
                'department_id' => $profile['department_id'],
                'designation' => $profile['designation'],
                'phone' => '+91 98' . rand(10000000, 99999999),
                'address' => rand(10, 99) . ', ' . ['MG Road', 'Linking Road', 'Tech Park', 'Cyber City'][rand(0, 3)] . ', ' . ['Mumbai', 'Bangalore', 'Delhi', 'Pune'][rand(0, 3)],
                'joining_date' => $joiningDate->format('Y-m-d'),
                'status' => 'active',
                'points' => 0,
            ]);

            // Create Contract
            Contract::create([
                'employee_id' => $employee->id,
                'start_date' => $joiningDate->format('Y-m-d'),
                'end_date' => $joiningDate->copy()->addYears(2)->format('Y-m-d'),
                'status' => 'active',
                'basic_salary' => $profile['base_salary'],
            ]);

            $employees[] = [
                'model' => $employee,
                'profile' => $profile
            ];
            
            $this->command->info("Created Employee: {$profile['name']} ({$profile['tier']})");
        }

        // 4. Simulate Past 3 Months & Current Month
        $allMonths = array_merge($monthsToSimulate, [$currentMonth]);
        
        $scoringService = app(ScoringService::class);
        
        foreach ($allMonths as $dateObj) {
            $isCurrentMonth = $dateObj->isSameMonth($currentMonth);
            $monthStr = $dateObj->format('Y-m');
            $daysInMonth = $dateObj->daysInMonth;
            
            $this->command->info("Simulating Month: {$monthStr}");

            // Collect scores for rewards calculation (only for past months)
            $monthScores = [];

            foreach ($employees as $empData) {
                $employee = $empData['model'];
                $profile = $empData['profile'];

                $stats = $this->simulateMonthForEmployee($employee, $profile, $dateObj, $isCurrentMonth, $evaluators);
                
                // Calculate Performance Score
                $scoreResult = $scoringService->computeScore($employee, $monthStr);
                $liveScore = $scoreResult['live_score'];
                
                // Save Performance Record
                $perfRecord = PerformanceRecord::create([
                    'employee_id' => $employee->id,
                    'month' => $monthStr,
                    'live_score' => $liveScore,
                    'final_score' => $isCurrentMonth ? null : $liveScore, // Freeze if past month
                    'attendance_component' => $scoreResult['components']['attendance'],
                    'rating_component' => $scoreResult['components']['rating'],
                    'task_component' => $scoreResult['components']['task'],
                    'consistency_component' => $scoreResult['components']['consistency'],
                    'average_rating' => $scoreResult['components']['rating_meta']['final_avg'] ?? 0,
                    'streak_days' => $scoreResult['components']['streak_days'] ?? 0,
                    'flags' => $scoreResult['flags'],
                ]);

                if (!$isCurrentMonth) {
                    $monthScores[] = [
                        'employee_id' => $employee->id,
                        'record_id' => $perfRecord->id,
                        'score' => $liveScore,
                        'base_salary' => $profile['base_salary'],
                        'stats' => $stats
                    ];
                }
            }

            // 5. Finalize Past Month (Rewards, Ranks, Payroll)
            if (!$isCurrentMonth) {
                // Sort by score desc
                usort($monthScores, fn($a, $b) => $b['score'] <=> $a['score']);
                
                $totalEmployees = count($monthScores);
                foreach ($monthScores as $rankIndex => $scoreData) {
                    $rank = $rankIndex + 1;
                    $percentile = round((($totalEmployees - $rank) / $totalEmployees) * 100);
                    
                    // Assign Tier
                    $tier = 'None';
                    $bonus = 0;
                    if ($rank === 1) { $tier = 'Gold'; $bonus = 500; }
                    elseif ($rank === 2) { $tier = 'Silver'; $bonus = 300; }
                    elseif ($rank === 3) { $tier = 'Bronze'; $bonus = 100; }

                    $perfRecord = PerformanceRecord::find($scoreData['record_id']);
                    $perfRecord->update([
                        'rank' => $rank,
                        'percentile' => $percentile,
                        'reward_tier' => $tier,
                    ]);

                    if ($tier !== 'None') {
                        MonthlyReward::create([
                            'employee_id' => $scoreData['employee_id'],
                            'month' => $monthStr,
                            'rank' => $rank,
                            'percentile' => $percentile,
                            'reward_tier' => $tier,
                            'bonus_points_awarded' => $bonus
                        ]);
                        
                        $empModel = Employee::find($scoreData['employee_id']);
                        $empModel->increment('points', $bonus);
                    }

                    // Generate Payroll
                    $baseSalary = $scoreData['base_salary'];
                    $stats = $scoreData['stats'];
                    
                    // Simple deduction logic based on absences
                    $perDaySalary = $baseSalary / 30;
                    $absentDeductions = $stats['unpaid_absences'] * $perDaySalary;
                    
                    // Bonus logic
                    $performanceBonus = ($tier === 'Gold') ? ($baseSalary * 0.10) : (($tier === 'Silver') ? ($baseSalary * 0.05) : 0);
                    $overtimePay = $stats['overtime_hours'] * ($perDaySalary / 8) * 1.5;

                    $grossSalary = $baseSalary + $performanceBonus + $overtimePay - $absentDeductions;
                    $taxDeduction = $grossSalary * 0.10; // 10% fixed tax
                    $totalDeductions = $absentDeductions + $taxDeduction;

                    Payroll::create([
                        'employee_id' => $scoreData['employee_id'],
                        'month' => $dateObj->month,
                        'year' => $dateObj->year,
                        'basic_salary' => $baseSalary,
                        'overtime_hours' => $stats['overtime_hours'],
                        'overtime_pay' => round($overtimePay),
                        'deductions' => round($totalDeductions),
                        'net_salary' => round($grossSalary - $taxDeduction),
                        'status' => 'paid',
                    ]);
                }
            }
        }
        
        $this->command->info('Production Seeding Completed Successfully!');
    }

    private function simulateMonthForEmployee($employee, $profile, Carbon $monthObj, $isCurrentMonth, $evaluators)
    {
        $stats = ['unpaid_absences' => 0, 'overtime_hours' => 0];
        
        $daysInMonth = $isCurrentMonth ? min($monthObj->daysInMonth, date('j')) : $monthObj->daysInMonth;
        
        // 1. Generate Attendance & Leaves
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $monthObj->copy()->setDay($day);
            
            if ($date->isWeekend()) continue;

            $rand = rand(1, 100);
            $isPresent = $rand <= $profile['attendance_rate'];
            
            if ($isPresent) {
                // Generate Present Attendance
                $isLate = rand(1, 100) <= $profile['late_rate'];
                
                $checkInHour = $isLate ? rand(9, 10) : 8;
                $checkInMin = $isLate ? rand(16, 59) : rand(30, 59); // Assuming 09:00 is shift start
                if ($isLate && $checkInHour == 9 && $checkInMin <= 15) $checkInMin = 16;
                
                $checkIn = $date->copy()->setTime($checkInHour, $checkInMin);
                
                // Checkout between 17:00 and 19:00
                $checkOutHour = rand(17, 19);
                $checkOutMin = rand(0, 59);
                $checkOut = $date->copy()->setTime($checkOutHour, $checkOutMin);
                
                $totalMinutes = $checkIn->diffInMinutes($checkOut);
                $totalHours = round($totalMinutes / 60, 2);
                
                if ($totalHours > 8) {
                    $stats['overtime_hours'] += ($totalHours - 8);
                }

                Attendance::create([
                    'employee_id' => $employee->id,
                    'date' => $date->format('Y-m-d'),
                    'check_in' => $checkIn->format('H:i:s'),
                    'check_out' => $checkOut->format('H:i:s'),
                    'total_hours' => $totalHours,
                    'status' => 'present'
                ]);
            } else {
                // Generate Leave or Absence
                $isLeave = rand(1, 100) <= 60; // 60% chance of taking a leave instead of just missing
                if ($isLeave && !$isCurrentMonth) { // past leaves
                    $reasons = [
                        'Personal work', 'Sick leave (fever)', 'Family emergency', 'Doctor appointment', 'Attending a wedding'
                    ];
                    $statuses = ['approved', 'approved', 'approved', 'rejected', 'pending'];
                    $leaveStatus = $statuses[rand(0, count($statuses) - 1)];

                    Leave::create([
                        'employee_id' => $employee->id,
                        'start_date' => $date->format('Y-m-d'),
                        'end_date' => $date->format('Y-m-d'),
                        'type' => ['casual', 'sick', 'earned'][rand(0, 2)],
                        'reason' => $reasons[rand(0, count($reasons) - 1)],
                        'status' => $leaveStatus
                    ]);

                    if ($leaveStatus !== 'approved') {
                        $stats['unpaid_absences']++;
                    }
                } else {
                    $stats['unpaid_absences']++;
                }
            }
        }

        // 2. Generate Tasks
        $taskCount = rand(3, 6);
        for ($i = 0; $i < $taskCount; $i++) {
            $taskDate = $monthObj->copy()->setDay(rand(1, 20));
            $deadline = $taskDate->copy()->addDays(rand(2, 5));
            
            $isCompletedOnTime = rand(1, 100) <= $profile['task_completion_rate'];
            $status = 'completed';
            $completedAt = null;

            if ($isCompletedOnTime) {
                $completedAt = $deadline->copy()->subHours(rand(1, 24));
            } else {
                // Late or missed
                $isMissed = rand(1, 100) <= 30; // 30% chance missed completely if not on time
                if ($isMissed && !$isCurrentMonth) {
                    $status = 'pending';
                } else {
                    $completedAt = $deadline->copy()->addHours(rand(12, 72));
                }
            }
            
            if ($isCurrentMonth && $deadline > now()) {
                $status = 'pending';
                $completedAt = null;
            }

            Task::create([
                'employee_id' => $employee->id,
                'assigned_by' => $evaluators[0]->id, // Admin assigned
                'title' => 'Monthly Deliverable ' . ($i + 1),
                'description' => 'Complete the assigned deliverable.',
                'status' => $status,
                'deadline' => $deadline,
                'completed_at' => $completedAt,
            ]);
        }

        // Generate Complaints
        if (rand(1, 100) <= 50) { // 50% chance to generate a complaint per month per employee (increased)
            $complaintTitles = ['AC not working', 'Internet issue', 'Salary discrepancy', 'Harassment', 'Food quality in cafeteria'];
            $compStatus = ['pending', 'pending', 'resolved', 'rejected'][rand(0, 3)]; // Mostly pending so they are not all solved
            Complaint::create([
                'user_id' => $employee->user_id,
                'title' => $complaintTitles[rand(0, count($complaintTitles) - 1)],
                'description' => 'I would like to report this issue which happened recently. Please look into it.',
                'is_anonymous' => rand(0, 1) == 1,
                'status' => $compStatus,
                'admin_response' => ($compStatus === 'resolved' || $compStatus === 'rejected') ? 'This has been reviewed.' : null,
                'created_at' => $monthObj->copy()->setDay(rand(1, 28))
            ]);
        }

        // 3. Generate Ratings (using the real RatingService to enforce rules)
        $ratingService = app(RatingService::class);
        $monthStr = $monthObj->format('Y-m');
        
        // Ensure ratings exist if the month has started
        // We use insert directly here to bypass cooldown logic in Seeder, but simulate the service payload
        // Actually, let's insert directly to avoid the 7-day cooldown exception in Seeder loop
        foreach ($evaluators as $evaluator) {
            $baseRating = $profile['rating_base'];
            $categories = [
                'work_quality' => $this->clampRating($baseRating + (rand(-5, 5) / 10)),
                'punctuality' => $this->clampRating($baseRating + (rand(-5, 5) / 10) - ($profile['late_rate'] / 20)),
                'teamwork' => $this->clampRating($baseRating + (rand(-5, 5) / 10)),
                'task_completion' => $this->clampRating($baseRating + (rand(-5, 5) / 10) - ((100 - $profile['task_completion_rate']) / 20)),
                'discipline' => $this->clampRating($baseRating + (rand(-5, 5) / 10)),
            ];
            $avg = array_sum($categories) / count($categories);

            // We bypass RatingService logic ONLY for historical seeding to avoid 7 day cooldown exceptions.
            // In a real seeder this is acceptable. The ScoringService will still compute properly.
            Rating::create([
                'employee_id' => $employee->id,
                'evaluator_id' => $evaluator->id,
                'month' => $monthStr,
                'categories' => $categories,
                'average_rating' => round($avg, 2),
                'feedback' => $this->getFeedback($profile['tier']),
                'created_at' => $monthObj->copy()->setDay(rand(20, 28)), // End of month ratings
            ]);
        }

        return $stats;
    }

    private function clampRating($val) {
        return max(1, min(5, round($val, 1)));
    }

    private function getFeedback($tier) {
        $feedbacks = [
            'High' => ['Exceptional work this month!', 'Consistent top performer.', 'Exceeded expectations.'],
            'Above Average' => ['Good solid performance.', 'Reliable and hardworking.', 'Good job, keep it up.'],
            'Average' => ['Met standard expectations.', 'Satisfactory work.', 'Needs to improve punctuality a bit.'],
            'Low' => ['Needs significant improvement.', 'Missed several deadlines.', 'Attendance has been a major issue.']
        ];
        return $feedbacks[$tier][rand(0, 2)];
    }

    private function getEmployeeProfiles($departments)
    {
        // 2 High, 3 Above Avg, 3 Avg, 2 Low
        return [
            [
                'name' => 'Rajesh Kumar', 'department_id' => $departments['Engineering'], 'designation' => 'Senior Developer',
                'tier' => 'High', 'attendance_rate' => 98, 'late_rate' => 2, 'task_completion_rate' => 95, 'rating_base' => 4.8, 'base_salary' => 80000
            ],
            [
                'name' => 'Priya Sharma', 'department_id' => $departments['HR'], 'designation' => 'HR Specialist',
                'tier' => 'High', 'attendance_rate' => 96, 'late_rate' => 5, 'task_completion_rate' => 98, 'rating_base' => 4.7, 'base_salary' => 75000
            ],
            [
                'name' => 'Amit Patel', 'department_id' => $departments['Engineering'], 'designation' => 'Backend Engineer',
                'tier' => 'Above Average', 'attendance_rate' => 92, 'late_rate' => 10, 'task_completion_rate' => 88, 'rating_base' => 4.2, 'base_salary' => 60000
            ],
            [
                'name' => 'Sneha Desai', 'department_id' => $departments['Operations'], 'designation' => 'Operations Analyst',
                'tier' => 'Above Average', 'attendance_rate' => 90, 'late_rate' => 12, 'task_completion_rate' => 85, 'rating_base' => 4.0, 'base_salary' => 45000
            ],
            [
                'name' => 'Vikram Singh', 'department_id' => $departments['Sales'], 'designation' => 'Sales Executive',
                'tier' => 'Above Average', 'attendance_rate' => 88, 'late_rate' => 15, 'task_completion_rate' => 82, 'rating_base' => 3.9, 'base_salary' => 55000
            ],
            [
                'name' => 'Neha Gupta', 'department_id' => $departments['Operations'], 'designation' => 'Support Staff',
                'tier' => 'Average', 'attendance_rate' => 82, 'late_rate' => 25, 'task_completion_rate' => 75, 'rating_base' => 3.5, 'base_salary' => 35000
            ],
            [
                'name' => 'Rahul Verma', 'department_id' => $departments['Engineering'], 'designation' => 'Frontend Dev',
                'tier' => 'Average', 'attendance_rate' => 80, 'late_rate' => 30, 'task_completion_rate' => 70, 'rating_base' => 3.4, 'base_salary' => 48000
            ],
            [
                'name' => 'Pooja Joshi', 'department_id' => $departments['Sales'], 'designation' => 'Sales Associate',
                'tier' => 'Average', 'attendance_rate' => 78, 'late_rate' => 35, 'task_completion_rate' => 65, 'rating_base' => 3.2, 'base_salary' => 40000
            ],
            [
                'name' => 'Sanjay Reddy', 'department_id' => $departments['Engineering'], 'designation' => 'Junior Dev',
                'tier' => 'Low', 'attendance_rate' => 65, 'late_rate' => 50, 'task_completion_rate' => 45, 'rating_base' => 2.5, 'base_salary' => 30000
            ],
            [
                'name' => 'Kavita Iyer', 'department_id' => $departments['Operations'], 'designation' => 'Data Entry',
                'tier' => 'Low', 'attendance_rate' => 60, 'late_rate' => 60, 'task_completion_rate' => 40, 'rating_base' => 2.2, 'base_salary' => 25000
            ],
        ];
    }

    private function createDepartments()
    {
        $deps = [];
        $names = ['Engineering', 'HR', 'Sales', 'Operations'];
        foreach ($names as $name) {
            $deps[$name] = Department::create(['name' => $name, 'description' => "{$name} Department"])->id;
        }
        return $deps;
    }

    private function createEvaluators()
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@compliancesys.com'],
            ['name' => 'System Admin', 'password' => Hash::make('password'), 'role' => 'admin', 'status' => 'approved', 'email_verified_at' => Carbon::now()]
        );

        $hr = User::firstOrCreate(
            ['email' => 'hr@compliancesys.com'],
            ['name' => 'HR Manager', 'password' => Hash::make('password'), 'role' => 'hr', 'status' => 'approved', 'email_verified_at' => Carbon::now()]
        );
        
        $manager = User::firstOrCreate(
            ['email' => 'manager@compliancesys.com'],
            ['name' => 'Engineering Manager', 'password' => Hash::make('password'), 'role' => 'admin', 'status' => 'approved', 'email_verified_at' => Carbon::now()]
        );

        return [$admin, $hr, $manager];
    }

    private function clearCollections()
    {
        // Only delete seeded data to preserve user's own accounts
        $users = User::where('email', 'like', '%@compliancesys.com')->get();
        foreach ($users as $user) {
            $user->delete();
            Complaint::where('user_id', $user->id)->delete();
            Alert::where('user_id', $user->id)->delete();
            
            $employee = Employee::where('user_id', $user->id)->first();
            if ($employee) {
                Attendance::where('employee_id', $employee->id)->delete();
                Leave::where('employee_id', $employee->id)->delete();
                Payroll::where('employee_id', $employee->id)->delete();
                Contract::where('employee_id', $employee->id)->delete();
                Task::where('employee_id', $employee->id)->delete();
                Rating::where('employee_id', $employee->id)->delete();
                PerformanceRecord::where('employee_id', $employee->id)->delete();
                MonthlyReward::where('employee_id', $employee->id)->delete();
                $employee->delete();
            }
        }
    }
}
