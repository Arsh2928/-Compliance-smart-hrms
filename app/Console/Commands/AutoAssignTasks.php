<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;

class AutoAssignTasks extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'hr:auto-assign-tasks {--historical : Generate tasks for the past 6 months}';

    /**
     * The console command description.
     */
    protected $description = 'Automatically assigns and completes generic tasks for employees weekly to maintain performance scores.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $employees = Employee::all();
        $adminId = User::where('role', 'admin')->first()->id ?? null;

        $taskNames = [
            'Submit Weekly Status Report',
            'Update Client Logs',
            'Attend Team Sync',
            'Code Review / QA Assessment',
            'Update Compliance Documentation',
            'Clear Support Ticket Backlog',
            'Prepare Month-End Analytics',
        ];

        $now = now();
        $weeks = $this->option('historical') ? 24 : 1; // Generate for last 24 weeks or just current week
        $count = 0;

        foreach ($employees as $employee) {
            for ($i = 0; $i < $weeks; $i++) {
                $weekStart = $now->copy()->subWeeks($i)->startOfWeek();
                $deadline = $weekStart->copy()->addDays(4)->endOfDay(); // Friday end of day
                
                // Pick 2 random tasks
                $tasksToAssign = array_rand(array_flip($taskNames), 2);
                
                foreach ($tasksToAssign as $title) {
                    // Check if a task with similar title and deadline already exists to avoid duplicates
                    $exists = Task::where('employee_id', $employee->id)
                        ->where('title', $title)
                        ->whereBetween('deadline', [
                            $deadline->copy()->startOfDay(), 
                            $deadline->copy()->endOfDay()
                        ])
                        ->exists();

                    if ($exists) continue;

                    // Assume 90% completion on time, 10% missed/late
                    $isLate = rand(1, 100) <= 10;
                    
                    if ($isLate) {
                        $completedAt = $deadline->copy()->addDays(rand(1, 3));
                    } else {
                        $completedAt = $deadline->copy()->subHours(rand(1, 48));
                    }
                    
                    Task::create([
                        'employee_id'  => $employee->id,
                        'assigned_by'  => $adminId,
                        'title'        => $title,
                        'description'  => 'Automated recurring task.',
                        'status'       => 'completed',
                        'deadline'     => $deadline,
                        'completed_at' => $completedAt,
                    ]);
                    $count++;
                }
            }
        }

        $this->info("Successfully generated and completed {$count} automated tasks.");
    }
}
