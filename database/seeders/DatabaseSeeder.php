<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Complaint;
use App\Models\Payroll;
use App\Models\Contract;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data
        User::truncate();
        Department::truncate();
        Employee::truncate();
        Attendance::truncate();
        Leave::truncate();
        Complaint::truncate();
        Payroll::truncate();
        Contract::truncate();
        \App\Models\Alert::truncate();

        // Departments
        $itDept = Department::create(['name' => 'Engineering', 'description' => 'Software Engineering']);
        $hrDept = Department::create(['name' => 'Human Resources', 'description' => 'HR and Admin']);
        $salesDept = Department::create(['name' => 'Sales', 'description' => 'Sales and Marketing']);

        // Admin User
        User::create([
            'name' => 'System Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        // HR User
        User::create([
            'name' => 'HR Manager',
            'email' => 'hr@example.com',
            'password' => Hash::make('password123'),
            'role' => 'hr',
        ]);

        // 5 Employees
        $employeesData = [
            ['name' => 'John Doe', 'email' => 'john@example.com', 'dept' => $itDept->id, 'salary' => 5000],
            ['name' => 'Jane Smith', 'email' => 'jane@example.com', 'dept' => $itDept->id, 'salary' => 5500],
            ['name' => 'Michael Brown', 'email' => 'michael@example.com', 'dept' => $salesDept->id, 'salary' => 4000],
            ['name' => 'Sarah Johnson', 'email' => 'sarah@example.com', 'dept' => $hrDept->id, 'salary' => 4500],
            ['name' => 'Emily Davis', 'email' => 'emily@example.com', 'dept' => $salesDept->id, 'salary' => 4200],
        ];

        $employees = [];

        foreach ($employeesData as $index => $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password123'),
                'role' => 'employee',
            ]);

            $emp = Employee::create([
                'user_id' => $user->id,
                'department_id' => $data['dept'],
                'employee_code' => 'EMP-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'phone' => '123-456-' . rand(1000, 9999),
                'address' => '123 Business Rd, Suite ' . rand(10, 99),
                'joined_date' => Carbon::now()->subMonths(rand(6, 24))->toDateString(),
            ]);

            $employees[] = $emp;

            // Contracts
            Contract::create([
                'employee_id' => $emp->id,
                'start_date' => $emp->joined_date,
                'end_date' => Carbon::parse($emp->joined_date)->addYears(1)->toDateString(),
                'status' => 'active'
            ]);

            // Payrolls (Last 2 months)
            for ($m = 1; $m <= 2; $m++) {
                Payroll::create([
                    'employee_id' => $emp->id,
                    'month' => Carbon::now()->subMonths($m)->month,
                    'year' => Carbon::now()->subMonths($m)->year,
                    'basic_salary' => $data['salary'],
                    'overtime_hours' => rand(0, 10),
                    'overtime_pay' => rand(0, 200),
                    'deductions' => rand(50, 150),
                    'net_salary' => $data['salary'] + rand(0, 200) - rand(50, 150),
                    'status' => 'paid'
                ]);
            }
        }

        // Attendances for the last 7 days
        foreach ($employees as $emp) {
            for ($i = 0; $i < 7; $i++) {
                $date = Carbon::today()->subDays($i);
                // Skip some days randomly to simulate absences
                if ($date->isWeekend() || rand(1, 10) > 8) continue;

                $checkIn = Carbon::parse($date->format('Y-m-d') . ' 09:00:00')->addMinutes(rand(-15, 30));
                $checkOut = Carbon::parse($date->format('Y-m-d') . ' 17:00:00')->addMinutes(rand(-30, 60));
                
                Attendance::create([
                    'employee_id' => $emp->id,
                    'date' => $date->format('Y-m-d'),
                    'check_in' => $checkIn->format('H:i:s'),
                    'check_out' => $checkOut->format('H:i:s'),
                    'total_hours' => 8 + rand(-1, 1) + (rand(0, 9) / 10),
                ]);
            }
        }

        // Leaves
        Leave::create([
            'employee_id' => $employees[0]->id,
            'start_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(4)->format('Y-m-d'),
            'type' => 'casual',
            'reason' => 'Going out of town for personal work.',
            'status' => 'pending'
        ]);

        Leave::create([
            'employee_id' => $employees[1]->id,
            'start_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
            'end_date' => Carbon::now()->subDays(8)->format('Y-m-d'),
            'type' => 'sick',
            'reason' => 'Viral fever.',
            'status' => 'approved',
            'admin_remark' => 'Get well soon.'
        ]);

        // Complaints
        Complaint::create([
            'user_id' => $employees[2]->user_id,
            'title' => 'AC not working in Sales Bay',
            'description' => 'The air conditioning unit in the sales department has been blowing warm air since Monday. Please fix it.',
            'is_anonymous' => false,
            'status' => 'open'
        ]);

        Complaint::create([
            'user_id' => $employees[3]->user_id,
            'title' => 'Unfair shift allocation',
            'description' => 'I have been given night shifts for 3 consecutive weeks without rotation.',
            'is_anonymous' => true,
            'status' => 'resolved',
            'admin_response' => 'We have adjusted the roster. You will be on morning shift starting next week.'
        ]);
    }
}
