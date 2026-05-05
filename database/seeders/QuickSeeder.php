<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class QuickSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data
        User::truncate();
        Department::truncate();
        Employee::truncate();
        \App\Models\Attendance::truncate();
        \App\Models\Leave::truncate();
        \App\Models\Complaint::truncate();
        \App\Models\Payroll::truncate();
        \App\Models\Rating::truncate();
        \App\Models\PerformanceRecord::truncate();
        \App\Models\MonthlyReward::truncate();
        \App\Models\Alert::truncate();

        // Departments
        $itDept = Department::create(['name' => 'Engineering', 'description' => 'Software Engineering']);
        $hrDept = Department::create(['name' => 'Human Resources', 'description' => 'HR and Admin']);

        // 1 Admin
        User::create([
            'name'     => 'System Admin',
            'email'    => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
        ]);

        // 1 HR
        $hrUser = User::create([
            'name'     => 'HR Manager',
            'email'    => 'hr@example.com',
            'password' => Hash::make('password123'),
            'role'     => 'hr',
        ]);

        Employee::create([
            'user_id'       => $hrUser->id,
            'department_id' => $hrDept->id,
            'employee_code' => 'EMP-HR1',
            'phone'         => '9876543210',
            'address'       => '10 HR Avenue, Suite 1',
            'joined_date'   => Carbon::now()->subMonths(12)->toDateString(),
        ]);

        // 20 Mock Employees
        $faker = \Faker\Factory::create();
        
        $employeesList = [];

        for ($i = 1; $i <= 20; $i++) {
            $user = User::create([
                'name'     => $faker->name,
                'email'    => $faker->unique()->safeEmail,
                'password' => Hash::make('password123'),
                'role'     => 'employee',
            ]);

            $emp = Employee::create([
                'user_id'       => $user->id,
                'department_id' => $faker->randomElement([$itDept->id, $hrDept->id]),
                'employee_code' => 'EMP-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'phone'         => $faker->phoneNumber,
                'address'       => $faker->address,
                'joined_date'   => Carbon::now()->subMonths(rand(1, 24))->toDateString(),
            ]);

            $employeesList[] = $emp;

            // Generate some attendances for the current month
            for ($d = 1; $d <= 5; $d++) {
                \App\Models\Attendance::create([
                    'employee_id' => $emp->id,
                    'date' => Carbon::now()->startOfMonth()->addDays($d)->toDateString(),
                    'check_in' => '09:00:00',
                    'check_out' => '17:00:00',
                    'total_hours' => 8,
                    'status' => 'present'
                ]);
            }

            // Generate a random leave
            if (rand(1, 10) > 7) {
                \App\Models\Leave::create([
                    'employee_id' => $emp->id,
                    'start_date' => Carbon::now()->addDays(rand(1, 10))->toDateString(),
                    'end_date' => Carbon::now()->addDays(rand(11, 15))->toDateString(),
                    'type' => $faker->randomElement(['casual', 'sick', 'earned']),
                    'reason' => $faker->sentence,
                    'status' => $faker->randomElement(['pending', 'approved', 'rejected']),
                    'admin_remark' => ''
                ]);
            }
        }

        // Generate some complaints
        for ($c = 1; $c <= 5; $c++) {
            \App\Models\Complaint::create([
                'user_id' => $employeesList[rand(0, 19)]->user_id,
                'title' => $faker->sentence(4),
                'description' => $faker->paragraph,
                'is_anonymous' => $faker->boolean,
                'status' => $faker->randomElement(['pending', 'resolved'])
            ]);
        }

        // Add some ratings so the leaderboard has data to evaluate
        $evaluatorId = $hrUser->id;
        foreach ($employeesList as $emp) {
            \App\Models\Rating::create([
                'employee_id' => $emp->id,
                'evaluator_id' => $evaluatorId,
                'month' => now()->subMonth()->format('Y-m'),
                'categories' => [
                    'work_quality' => rand(3, 5),
                    'punctuality' => rand(2, 5),
                    'teamwork' => rand(3, 5),
                    'task_completion' => rand(3, 5),
                    'discipline' => rand(3, 5),
                ],
                'average_rating' => rand(30, 50) / 10,
                'feedback' => 'Good effort.'
            ]);
        }

        $this->command->info('✅ Seeded: 1 Admin, 1 HR, 20 Employees with Attendances, Leaves, Complaints, and Ratings.');
    }
}
