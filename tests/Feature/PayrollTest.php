<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Employee;
use App\Models\Payroll;
use Tests\TestCase;

class PayrollTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        User::where('email', 'like', '%@example.%')->each(function ($user) {
            Employee::where('user_id', $user->id)->delete();
            $user->delete();
        });
    }

    protected function tearDown(): void
    {
        User::where('email', 'like', '%@example.%')->each(function ($user) {
            Payroll::where('employee_id', Employee::where('user_id', $user->id)->value('id'))->delete();
            Employee::where('user_id', $user->id)->delete();
            $user->delete();
        });
        parent::tearDown();
    }

    public function test_admin_can_generate_payroll()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employeeUser = User::factory()->create(['role' => 'employee']);
        $employee = Employee::create([
            'user_id' => $employeeUser->id,
            'department' => 'IT',
            'designation' => 'Developer',
            'basic_salary' => 50000,
            'employee_code' => 'TEST-' . uniqid(),
        ]);

        $response = $this->actingAs($admin)->post('/admin/payrolls', [
            'employee_id'    => (string) $employee->id,
            'month'          => (int) now()->format('m'),
            'year'           => (int) now()->format('Y'),
            'basic_salary'   => 50000,
            'overtime_hours' => 0,
            'overtime_pay'   => 5000,
            'deductions'     => 2000,
            'status'         => 'pending',
        ]);

        $response->assertRedirect('/admin/payrolls');
        $this->assertDatabaseHas('payrolls', [
            'employee_id' => (string) $employee->id,
            'status' => 'pending',
        ]);
    }

    public function test_employee_can_view_own_payroll()
    {
        $user = User::factory()->create(['role' => 'employee']);
        $employee = Employee::create([
            'user_id' => $user->id,
            'department' => 'IT',
            'designation' => 'Developer',
            'basic_salary' => 50000,
            'employee_code' => 'TEST-' . uniqid(),
        ]);

        Payroll::create([
            'employee_id'    => (string) $employee->id,
            'month'          => (int) now()->format('m'),
            'year'           => (int) now()->format('Y'),
            'basic_salary'   => 50000,
            'overtime_hours' => 0,
            'overtime_pay'   => 5000,
            'deductions'     => 2000,
            'net_salary'     => 53000,
            'status'         => 'approved',
        ]);

        $response = $this->actingAs($user)->get('/employee/payrolls');
        $response->assertStatus(200);
    }
}
