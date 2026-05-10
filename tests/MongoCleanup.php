<?php

namespace Tests;

use App\Models\User;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\Payroll;

/**
 * Replaces RefreshDatabase for local MongoDB (no replica set / transactions).
 * Cleans up test users by email pattern instead of rolling back transactions.
 */
trait MongoCleanup
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->cleanupTestData();
    }

    protected function tearDown(): void
    {
        $this->cleanupTestData();
        parent::tearDown();
    }

    private function cleanupTestData(): void
    {
        // Delete only factory-generated test records (email pattern: *@example.*)
        User::where('email', 'like', '%@example.%')->each(function ($user) {
            Employee::where('user_id', $user->id)->delete();
            Leave::where('employee_id', 'like', '%')->delete(); // cleaned via employee
            Payroll::where('employee_id', 'like', '%')->delete();
            $user->delete();
        });

        // Also clean any direct test data
        User::where('email', 'test@example.com')->delete();
    }
}
