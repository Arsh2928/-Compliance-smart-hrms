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
Route::get('/leaderboard/public', [HomeController::class, 'leaderboard'])->name('public.leaderboard');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/contact', [HomeController::class, 'submitContact'])->name('contact.submit');

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
    Route::post('/alerts/mark-all-read', [AlertController::class, 'markAllRead'])->name('alerts.read.all');
    Route::get('/alerts/{alert}/read', [AlertController::class, 'markAsRead'])->name('alerts.read');

    // Reward / Leaderboard / Engage — all roles
    Route::get('/leaderboard', [RewardController::class, 'leaderboard'])->name('leaderboard.index');
    Route::get('/rewards', [RewardController::class, 'rewardsCenter'])->name('rewards.index');
    Route::post('/rewards/redeem', [RewardController::class, 'redeemReward'])->name('rewards.redeem');
    Route::post('/rewards/use', [RewardController::class, 'useVoucher'])->name('rewards.use');

    // Performance System API Routes (JSON endpoints)
    Route::post('/api/employees/{id}/rate', [PerformanceController::class, 'storeRating'])->name('api.employees.rate');
    Route::get('/api/my-performance', [PerformanceController::class, 'myHistory'])->name('api.my-performance');

    // Messaging System
    Route::get('/messages', [\App\Http\Controllers\MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/sent', [\App\Http\Controllers\MessageController::class, 'sent'])->name('messages.sent');
    Route::get('/messages/create', [\App\Http\Controllers\MessageController::class, 'create'])->name('messages.create');
    Route::post('/messages', [\App\Http\Controllers\MessageController::class, 'store'])->name('messages.store');
    Route::get('/messages/{message}', [\App\Http\Controllers\MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{message}/reply', [\App\Http\Controllers\MessageController::class, 'reply'])->name('messages.reply');

    // ── ADMIN ROUTES ───────────────────────────────────────────────────
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

        // Rate an employee (must be BEFORE resource to avoid {employee} clash)
        Route::post('/employees/{id}/rate', [PerformanceController::class, 'webRating'])->name('employees.rate');

        // Approve / Reject pending registrations
        Route::post('/employees/{user}/approve', [\App\Http\Controllers\Admin\EmployeeController::class, 'approveUser'])->name('employees.approve');
        Route::delete('/employees/{user}/reject', [\App\Http\Controllers\Admin\EmployeeController::class, 'rejectUser'])->name('employees.reject');

        Route::resource('employees', \App\Http\Controllers\Admin\EmployeeController::class);
        Route::resource('leaves', \App\Http\Controllers\Admin\LeaveController::class)
            ->only(['index', 'update'])
            ->parameters(['leaves' => 'leave']);
        Route::resource('complaints', \App\Http\Controllers\Admin\ComplaintController::class)->only(['index', 'update']);
        Route::get('/payrolls/download-all', [\App\Http\Controllers\Admin\PayrollController::class, 'downloadAll'])->name('payrolls.downloadAll');
        Route::get('/payrolls/calculate', [\App\Http\Controllers\Admin\PayrollController::class, 'calculate'])->name('payrolls.calculate');
        Route::get('/payrolls/{payroll}/download', [\App\Http\Controllers\Admin\PayrollController::class, 'downloadPdf'])->name('payrolls.download');
        Route::resource('payrolls', \App\Http\Controllers\Admin\PayrollController::class)->only(['index', 'create', 'store', 'update', 'edit']);
        Route::resource('contracts', \App\Http\Controllers\Admin\ContractController::class)->except(['show', 'destroy']);
    });

    // ── HR ROUTES ──────────────────────────────────────────────────────
    Route::middleware('role:hr,admin')->prefix('hr')->name('hr.')->group(function () {
        Route::get('/dashboard', [HRDashboard::class, 'index'])->name('dashboard');

        // Rate an employee (must be BEFORE resource)
        Route::post('/employees/{id}/rate', [PerformanceController::class, 'webRating'])->name('employees.rate');

        // Approve / Reject pending registrations
        Route::post('/employees/{user}/approve', [\App\Http\Controllers\Admin\EmployeeController::class, 'approveUser'])->name('employees.approve');
        Route::delete('/employees/{user}/reject', [\App\Http\Controllers\Admin\EmployeeController::class, 'rejectUser'])->name('employees.reject');

        Route::resource('employees', \App\Http\Controllers\Admin\EmployeeController::class)->except(['destroy']);
        Route::resource('leaves', \App\Http\Controllers\HR\LeaveController::class)
            ->only(['index', 'update'])
            ->parameters(['leaves' => 'leave']);
        Route::resource('complaints', \App\Http\Controllers\HR\ComplaintController::class)->only(['index', 'update']);
        Route::get('/payrolls/download-all', [\App\Http\Controllers\HR\PayrollController::class, 'downloadAll'])->name('payrolls.downloadAll');
        Route::get('/payrolls/calculate', [\App\Http\Controllers\Admin\PayrollController::class, 'calculate'])->name('payrolls.calculate');
        Route::get('/payrolls/{payroll}/download', [\App\Http\Controllers\HR\PayrollController::class, 'downloadPdf'])->name('payrolls.download');
        Route::resource('payrolls', \App\Http\Controllers\HR\PayrollController::class)->only(['index', 'create', 'store', 'update', 'edit']);
        Route::resource('contracts', \App\Http\Controllers\HR\ContractController::class)->except(['show', 'destroy']);
        Route::post('/attendance/check-in', [\App\Http\Controllers\Employee\AttendanceController::class, 'checkIn'])->name('attendance.checkin');
        Route::post('/attendance/check-out', [\App\Http\Controllers\Employee\AttendanceController::class, 'checkOut'])->name('attendance.checkout');
    });

    // ── EMPLOYEE ROUTES ────────────────────────────────────────────────
    Route::middleware('role:employee')->prefix('employee')->name('employee.')->group(function () {
        Route::get('/dashboard', [EmployeeDashboard::class, 'index'])->name('dashboard');
        Route::post('/attendance/check-in', [\App\Http\Controllers\Employee\AttendanceController::class, 'checkIn'])->name('attendance.checkin');
        Route::post('/attendance/check-out', [\App\Http\Controllers\Employee\AttendanceController::class, 'checkOut'])->name('attendance.checkout');
        Route::resource('leaves', \App\Http\Controllers\Employee\LeaveController::class)->parameters(['leaves' => 'leave']);
        Route::resource('complaints', \App\Http\Controllers\Employee\ComplaintController::class)->only(['index', 'create', 'store']);
        Route::resource('payrolls', \App\Http\Controllers\Employee\PayrollController::class)->only(['index']);
    });

}); // end middleware('auth')

Route::get('/verify-otp', [\App\Http\Controllers\Auth\OtpController::class, 'showVerifyForm'])->name('otp.verify');
Route::post('/verify-otp', [\App\Http\Controllers\Auth\OtpController::class, 'verifyOtp'])->name('otp.verify.post');
Route::post('/verify-otp/resend', [\App\Http\Controllers\Auth\OtpController::class, 'resendOtp'])->name('otp.verify.resend');

require __DIR__.'/auth.php';
