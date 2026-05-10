<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Employee;
use App\Models\Leave;
use Tests\TestCase;

class LeaveTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Clean only test data (factory emails end with @example.com/net/org)
        Leave::where('reason', 'Feeling unwell')->delete();
        User::where('email', 'like', '%@example.%')->each(function ($user) {
            Employee::where('user_id', $user->id)->delete();
            $user->delete();
        });
    }

    protected function tearDown(): void
    {
        Leave::where('reason', 'Feeling unwell')->delete();
        User::where('email', 'like', '%@example.%')->each(function ($user) {
            Employee::where('user_id', $user->id)->delete();
            $user->delete();
        });
        parent::tearDown();
    }

    public function test_employee_can_view_leaves()
    {
        $user = User::factory()->create(['role' => 'employee']);
        $employee = Employee::create([
            'user_id' => $user->id,
            'department' => 'IT',
            'designation' => 'Developer',
            'basic_salary' => 50000,
            'employee_code' => 'TEST-' . uniqid(),
        ]);

        $response = $this->actingAs($user)->get('/employee/leaves');
        $response->assertStatus(200);
    }

    public function test_employee_can_create_leave_request()
    {
        $user = User::factory()->create(['role' => 'employee']);
        $employee = Employee::create([
            'user_id' => $user->id,
            'department' => 'IT',
            'designation' => 'Developer',
            'basic_salary' => 50000,
            'employee_code' => 'TEST-' . uniqid(),
        ]);

        $response = $this->actingAs($user)->post('/employee/leaves', [
            'type' => 'sick',
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addDays(2)->format('Y-m-d'),
            'reason' => 'Feeling unwell',
        ]);

        $response->assertRedirect('/employee/leaves');
        $this->assertDatabaseHas('leaves', [
            'type' => 'sick',
            'status' => 'pending',
            'employee_id' => (string) $employee->id,
        ]);
    }

    public function test_hr_can_approve_leave()
    {
        $hr = User::factory()->create(['role' => 'hr']);
        $user = User::factory()->create(['role' => 'employee']);
        $employee = Employee::create([
            'user_id' => $user->id,
            'department' => 'IT',
            'designation' => 'Developer',
            'basic_salary' => 50000,
            'employee_code' => 'TEST-' . uniqid(),
        ]);

        $leave = Leave::create([
            'employee_id' => (string) $employee->id,
            'type' => 'sick',
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addDays(2)->format('Y-m-d'),
            'reason' => 'Feeling unwell',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($hr)->put("/hr/leaves/{$leave->id}", [
            'status' => 'approved',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('leaves', [
            '_id' => $leave->id,
            'status' => 'approved'
        ]);
    }
}
