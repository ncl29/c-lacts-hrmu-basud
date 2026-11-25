<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeDashboardController;
use App\Http\Controllers\DocumentRoutingController;
use App\Http\Controllers\HrAttendanceController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes - Centralized Leave Application & Credit Tracking System (C-LACTS)
| HRMU Municipality of Basud, Camarines Norte
|--------------------------------------------------------------------------
*/

// Root route: Redirects to role-appropriate home or login
Route::get('/', function () {
    if (Auth::check()) {
        return match (Auth::user()->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'routing_clerk' => redirect()->route('clerk.scanner'),
            'hrmu_staff' => redirect()->route('hr.tardiness.create'),
            default => redirect()->route('employee.dashboard'),
        };
    }
    return redirect()->route('login');
});

// Authentication Routes (Guest Only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

// Authenticated Routes (Requires Civil Service Login)
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    // Self-service password change (Available to all authenticated users)
    Route::post('/password/change', [AuthController::class, 'updatePassword'])->name('password.change');

    // Employee Profile & Account Settings (Available to all authenticated users)
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password/send-otp', [ProfileController::class, 'sendPasswordOtp'])->name('profile.password.otp');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // 1. Employee Self-Service Module (Regular Municipal Personnel Only; Excludes Pure Routing Clerks)
    Route::prefix('employee')->name('employee.')->middleware('role:employee,hrmu_staff,admin')->group(function () {
        Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');
        Route::get('/leave-record', [\App\Http\Controllers\LeaveRecordController::class, 'employeeView'])->name('leave-record');
        Route::get('/leave-record/print', function(\Illuminate\Http\Request $request) {
            $user = Auth::user();
            $empId = $user?->employee_id ?? 1;
            return app(\App\Http\Controllers\LeaveRecordController::class)->printCard($request, $empId);
        })->name('leave-record.print');
        Route::post('/leave/store', [EmployeeDashboardController::class, 'storeLeave'])->name('leave.store');
        Route::post('/leave/{id}/cancel', [EmployeeDashboardController::class, 'cancelLeave'])->name('leave.cancel');
    });

    // Printable Civil Service Form No. 6 (Revised 2020)
    Route::get('/forms/form-6/{id}/print', [EmployeeDashboardController::class, 'printForm6'])->name('forms.form6.print');

    // 2. Physical Custody & QR Movement Terminal (Clerk, HR Staff, Admin, Department Head, and Mayor)
    Route::prefix('clerk')->name('clerk.')->middleware('role:scanner_authorized')->group(function () {
        Route::get('/scanner', [DocumentRoutingController::class, 'scannerView'])->name('scanner');
    });

    // 3. HR Attendance Encoding & Starla Computation Engine (HR Staff, Admin)
    Route::prefix('hr')->name('hr.')->middleware('role:hrmu_staff,admin')->group(function () {
        // Official Civil Service Leave Record Card (11-Column Tabular Ledger)
        Route::get('/leave-records', [\App\Http\Controllers\LeaveRecordController::class, 'index'])->name('leave-records.index');
        Route::post('/leave-records/store', [\App\Http\Controllers\LeaveRecordController::class, 'storeRow'])->name('leave-records.store');
        Route::put('/leave-records/{id}', [\App\Http\Controllers\LeaveRecordController::class, 'updateRow'])->name('leave-records.update');
        Route::delete('/leave-records/{id}', [\App\Http\Controllers\LeaveRecordController::class, 'destroyRow'])->name('leave-records.destroy');
        Route::post('/leave-records/accrue-monthly', [\App\Http\Controllers\LeaveRecordController::class, 'runMonthlyAccrual'])->name('leave-records.accrue-monthly');
        Route::post('/leave-records/prorated-accrual', [\App\Http\Controllers\LeaveRecordController::class, 'storeProratedAccrual'])->name('leave-records.prorated-accrual');
        Route::post('/leave-records/update-all', [\App\Http\Controllers\LeaveRecordController::class, 'updateAllRecords'])->name('leave-records.update-all');
        Route::post('/leave-records/recalculate/{employeeId}', [\App\Http\Controllers\LeaveRecordController::class, 'recalculate'])->name('leave-records.recalculate');
        Route::get('/leave-records/{employeeId}/print', [\App\Http\Controllers\LeaveRecordController::class, 'printCard'])->name('leave-records.print');

        // Starla Tardiness & Reference Tables
        Route::get('/tardiness', [HrAttendanceController::class, 'create'])->name('tardiness.create');
        Route::post('/tardiness/store', [HrAttendanceController::class, 'store'])->name('tardiness.store');
        Route::get('/starla-tables', [HrAttendanceController::class, 'starlaTables'])->name('starla.tables');
    });

    // 4. System Administrator & Governance Module (Admin Level 4 Only)
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::post('/users/{id}/role', [AdminController::class, 'updateUserRole'])->name('users.role.update');
        Route::post('/users/{id}/reset-password', [AdminController::class, 'resetUserPassword'])->name('users.password.reset');
        Route::delete('/users/{id}', [AdminController::class, 'destroyUser'])->name('users.destroy');

        // Backup & Disaster Recovery Management
        Route::post('/backups/create', [AdminController::class, 'createBackup'])->name('backups.create');
        Route::get('/backups/download/{filename}', [AdminController::class, 'downloadBackup'])->name('backups.download');
        Route::delete('/backups/{filename}', [AdminController::class, 'destroyBackup'])->name('backups.destroy');
    });

    // 5. Document Routing & QR Traceability API Endpoints
    Route::prefix('api')->group(function () {
        // Visual Timeline Tracker (All authenticated personnel can track their application)
        Route::get('/routing/{id}/timeline', [DocumentRoutingController::class, 'trackApplicationStatus'])->name('api.routing.timeline');

        // Office Custody Tracking Actions (Accessible to Any Authenticated Office Personnel Acting as Receiver)
        Route::middleware('role:employee,routing_clerk,hrmu_staff,admin')->group(function () {
            Route::get('/routing/scan', [DocumentRoutingController::class, 'scanOrEnterReference'])->name('api.routing.scan');
            Route::post('/routing/{id}/status', [DocumentRoutingController::class, 'updateRoutingStatus'])->name('api.routing.status');
        });

        // HRMU Section 7.A Leave Credits Certification & Starla Math Preview
        Route::middleware('role:hrmu_staff,admin')->group(function () {
            Route::post('/routing/{id}/certify', [DocumentRoutingController::class, 'certifyCredits'])->name('api.routing.certify');
            Route::post('/documents/{id}/verify', [DocumentRoutingController::class, 'verifyAttachment'])->name('api.documents.verify');
            Route::get('/starla/preview', [HrAttendanceController::class, 'preview'])->name('api.starla.preview');
            Route::get('/leave-records/preview-particular', [\App\Http\Controllers\LeaveRecordController::class, 'previewParticular'])->name('api.leave-records.preview-particular');
        });
    });

    // Supporting Documents View & Download
    Route::get('/documents/{id}/view', [DocumentRoutingController::class, 'viewAttachment'])->name('documents.view');
    Route::get('/documents/{id}/download', [DocumentRoutingController::class, 'downloadAttachment'])->name('documents.download');
});
