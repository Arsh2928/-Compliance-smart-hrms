<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\HR\DashboardController as HRDashboard;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboard;
use App\Http\Controllers\RewardController;
use App\Http\Controllers\PerformanceController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/features', [HomeController::class, 'features'])->name('features');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Dashboard Redirector
    Route::get('/dashboard', function () {
        $role = auth()->user()->role;
        if ($role === 'admin') return redirect()->route('admin.dashboard');
        if ($role === 'hr') return redirect()->route('hr.dashboard');
        return redirect()->route('employee.dashboard');
    })->name('dashboard');

    // Alerts
    Route::get('/alerts/{alert}/read', [AlertController::class, 'markAsRead'])->name('alerts.read');

    // Reward System
    Route::get('/leaderboard', [RewardController::class, 'leaderboard'])->name('leaderboard.index');
    Route::get('/rewards', [RewardController::class, 'rewardsCenter'])->name('rewards.index');
    Route::post('/rewards/redeem', [RewardController::class, 'redeemReward'])->name('rewards.redeem');
    Route::post('/employee/{employee}/rate', [RewardController::class, 'rateEmployee'])->name('admin.employees.rate');

    // Performance System API Routes
    Route::post('/api/employees/{id}/rate', [PerformanceController::class, 'storeRating'])->name('api.employees.rate');
    Route::get('/api/my-performance', [PerformanceController::class, 'myHistory'])->name('api.my-performance');

    // Admin Routes
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function() {
        Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');
        Route::resource('employees', \App\Http\Controllers\Admin\EmployeeController::class);
        Route::resource('leaves', \App\Http\Controllers\Admin\LeaveController::class)
            ->only(['index', 'update'])
            ->parameters(['leaves' => 'leave']);
        Route::resource('complaints', \App\Http\Controllers\Admin\ComplaintController::class)->only(['index', 'update']);
        Route::resource('payrolls', \App\Http\Controllers\Admin\PayrollController::class)->only(['index', 'create', 'store', 'update']);
        Route::resource('contracts', \App\Http\Controllers\Admin\ContractController::class)->except(['show', 'destroy']);
    });

    // HR Routes — Full access except employee create/edit/delete
    Route::middleware('role:hr,admin')->prefix('hr')->name('hr.')->group(function() {
        Route::get('/dashboard', [HRDashboard::class, 'index'])->name('dashboard');

        // Employees — view only (no create/edit/destroy)
        Route::resource('employees', \App\Http\Controllers\HR\EmployeeController::class)
            ->only(['index', 'show']);

        // Leaves — full management
        Route::resource('leaves', \App\Http\Controllers\HR\LeaveController::class)
            ->only(['index', 'update'])
            ->parameters(['leaves' => 'leave']);

        // Complaints — full management
        Route::resource('complaints', \App\Http\Controllers\HR\ComplaintController::class)
            ->only(['index', 'update']);

        // Payrolls — full management (create, generate, mark paid)
        Route::resource('payrolls', \App\Http\Controllers\HR\PayrollController::class)
            ->only(['index', 'create', 'store', 'update']);

        // Contracts — full management (create, edit, update; no destroy)
        Route::resource('contracts', \App\Http\Controllers\HR\ContractController::class)
            ->except(['show', 'destroy']);

        // Performance — HR can rate employees
        Route::post('/employees/{id}/rate', [\App\Http\Controllers\PerformanceController::class, 'storeRating'])
            ->name('employees.rate');
    });

    // Employee Routes
    Route::middleware('role:employee')->prefix('employee')->name('employee.')->group(function() {
        Route::get('/dashboard', [EmployeeDashboard::class, 'index'])->name('dashboard');
        Route::post('/attendance/check-in', [\App\Http\Controllers\Employee\AttendanceController::class, 'checkIn'])->name('attendance.checkin');
        Route::post('/attendance/check-out', [\App\Http\Controllers\Employee\AttendanceController::class, 'checkOut'])->name('attendance.checkout');
        Route::resource('leaves', \App\Http\Controllers\Employee\LeaveController::class)
            ->parameters(['leaves' => 'leave']);
        Route::resource('complaints', \App\Http\Controllers\Employee\ComplaintController::class)->only(['index', 'create', 'store']);
        Route::resource('payrolls', \App\Http\Controllers\Employee\PayrollController::class)->only(['index']);
    });
});

require __DIR__.'/auth.php';
