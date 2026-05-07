<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = Employee::all();
        $adminUser = User::where('role', 'admin')->first() ?? User::first();

        if (!$adminUser) {
            $this->command->info("No assigner user found, skipping TaskSeeder.");
            return;
        }

        Task::truncate();

        foreach ($employees as $employee) {
            // Seed a few tasks for the current month
            
            // Task 1: Completed on time
            Task::create([
                'employee_id' => $employee->id,
                'assigned_by' => $adminUser->id,
                'title' => 'Update Project Documentation',
                'description' => 'Complete the technical specifications for the upcoming sprint.',
                'status' => 'completed',
                'deadline' => Carbon::now()->startOfMonth()->addDays(5),
                'completed_at' => Carbon::now()->startOfMonth()->addDays(4),
            ]);

            // Task 2: Completed late
            Task::create([
                'employee_id' => $employee->id,
                'assigned_by' => $adminUser->id,
                'title' => 'Fix Authentication Bug',
                'description' => 'Resolve the issue with JWT tokens expiring early.',
                'status' => 'completed',
                'deadline' => Carbon::now()->startOfMonth()->addDays(10),
                'completed_at' => Carbon::now()->startOfMonth()->addDays(12),
            ]);

            // Task 3: In progress (not missed yet if deadline is in the future)
            Task::create([
                'employee_id' => $employee->id,
                'assigned_by' => $adminUser->id,
                'title' => 'Design New Dashboard UI',
                'description' => 'Create Figma mockups for the new analytics dashboard.',
                'status' => 'in_progress',
                'deadline' => Carbon::now()->endOfMonth()->subDays(2),
                'completed_at' => null,
            ]);

            // Task 4: Missed deadline (Pending)
            Task::create([
                'employee_id' => $employee->id,
                'assigned_by' => $adminUser->id,
                'title' => 'Client Requirements Meeting',
                'description' => 'Finalize requirements with the AlphaCorp client.',
                'status' => 'pending',
                'deadline' => Carbon::now()->startOfMonth()->addDays(2), // Already missed
                'completed_at' => null,
            ]);
        }

        $this->command->info("Tasks seeded successfully!");
    }
}
