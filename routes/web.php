<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\SalaryComponentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Dashboard routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/ceo', [DashboardController::class, 'index'])->name('dashboard.ceo');
    Route::get('/dashboard/cfo', [DashboardController::class, 'index'])->name('dashboard.cfo');
    Route::get('/dashboard/hrd', [DashboardController::class, 'index'])->name('dashboard.hrd');
    Route::get('/dashboard/personalia', [DashboardController::class, 'index'])->name('dashboard.personalia');
    Route::get('/dashboard/karyawan', [DashboardController::class, 'index'])->name('dashboard.karyawan');
    
    // Profile routes
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::get('/profile/password', function() {
        return view('profile.password');
    })->name('profile.password');
    Route::put('/profile/password', [AuthController::class, 'changePassword'])->name('profile.password.update');
    
    // User Management
    Route::middleware('permission:users.view')->group(function () {
        Route::resource('users', UserController::class);
        Route::post('users/{user}/roles', [UserController::class, 'assignRole'])->name('users.assign-role');
        Route::delete('users/{user}/roles/{role}', [UserController::class, 'removeRole'])->name('users.remove-role');
    });
    
    // Employee Management
    Route::middleware('permission:employees.view')->group(function () {
        Route::resource('employees', EmployeeController::class);
        Route::get('employees/{employee}/salary', [EmployeeController::class, 'salary'])->name('employees.salary');
        Route::post('employees/{employee}/salary', [EmployeeController::class, 'updateSalary'])->name('employees.salary.update');
    });
    
    // Department Management
    Route::middleware('permission:departments.view')->group(function () {
        Route::resource('departments', DepartmentController::class);
    });
    
    // Position Management
    Route::middleware('permission:positions.view')->group(function () {
        Route::resource('positions', PositionController::class);
    });
    
    // Attendance Management
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('index');
        Route::get('/clock', function() {
            return view('attendance.clock');
        })->name('clock');
        Route::post('/clock-in', [AttendanceController::class, 'clockIn'])->name('clock-in');
        Route::post('/clock-out', [AttendanceController::class, 'clockOut'])->name('clock-out');
        Route::post('/break-start', [AttendanceController::class, 'startBreak'])->name('break-start');
        Route::post('/break-end', [AttendanceController::class, 'endBreak'])->name('break-end');
        Route::get('/today', [AttendanceController::class, 'getTodayAttendance'])->name('today');
        
        Route::middleware('permission:attendance.edit')->group(function () {
            Route::get('/{attendance}/edit', [AttendanceController::class, 'edit'])->name('edit');
            Route::put('/{attendance}', [AttendanceController::class, 'update'])->name('update');
        });
        
        Route::middleware('permission:attendance.reports')->group(function () {
            Route::get('/report', [AttendanceController::class, 'report'])->name('report');
        });
    });
    
    // Leave Management
    Route::prefix('leaves')->name('leaves.')->group(function () {
        Route::get('/', [LeaveController::class, 'index'])->name('index');
        
        Route::middleware('permission:leaves.create')->group(function () {
            Route::get('/create', [LeaveController::class, 'create'])->name('create');
            Route::post('/', [LeaveController::class, 'store'])->name('store');
        });
        
        Route::middleware('permission:leaves.edit')->group(function () {
            Route::get('/{leave}/edit', [LeaveController::class, 'edit'])->name('edit');
            Route::put('/{leave}', [LeaveController::class, 'update'])->name('update');
        });
        
        Route::middleware('permission:leaves.approve')->group(function () {
            Route::get('/pending', [LeaveController::class, 'pending'])->name('pending');
            Route::post('/{leave}/approve', [LeaveController::class, 'approve'])->name('approve');
            Route::post('/{leave}/reject', [LeaveController::class, 'reject'])->name('reject');
        });
        
        Route::get('/{leave}', [LeaveController::class, 'show'])->name('show');
    });
    
    // Payroll Management
    Route::prefix('payroll')->name('payroll.')->group(function () {
        // Employee payroll slip
        Route::get('/slip', [PayrollController::class, 'slip'])->name('slip');
        Route::get('/slip/{payroll}/download', [PayrollController::class, 'downloadSlip'])->name('slip.download');
        
        Route::middleware('permission:payroll.view_all')->group(function () {
            Route::get('/', [PayrollController::class, 'index'])->name('index');
            Route::get('/{payroll}', [PayrollController::class, 'show'])->name('show');
        });
        
        Route::middleware('permission:payroll.create')->group(function () {
            Route::prefix('periods')->name('periods.')->group(function () {
                Route::get('/', [PayrollController::class, 'periods'])->name('index');
                Route::get('/create', [PayrollController::class, 'createPeriod'])->name('create');
                Route::post('/', [PayrollController::class, 'storePeriod'])->name('store');
                Route::get('/{period}/calculate', [PayrollController::class, 'calculate'])->name('calculate');
                Route::post('/{period}/process', [PayrollController::class, 'process'])->name('process');
            });
        });
        
        Route::middleware('permission:payroll.approve')->group(function () {
            Route::post('/{payroll}/approve', [PayrollController::class, 'approve'])->name('approve');
            Route::post('/period/{period}/approve', [PayrollController::class, 'approvePeriod'])->name('period.approve');
        });
        
        Route::middleware('permission:payroll.reports')->group(function () {
            Route::get('/reports', [PayrollController::class, 'reports'])->name('reports');
            Route::get('/reports/export', [PayrollController::class, 'exportReport'])->name('reports.export');
        });
    });
    
    // Salary Components
    Route::middleware('permission:salary_components.view')->group(function () {
        Route::resource('salary-components', SalaryComponentController::class);
    });
    
    // Reports
    Route::prefix('reports')->name('reports.')->middleware('permission:reports.view')->group(function () {
        Route::get('/hr', [ReportController::class, 'hr'])->name('hr');
        Route::get('/attendance', [ReportController::class, 'attendance'])->name('attendance');
        
        Route::middleware('permission:reports.financial')->group(function () {
            Route::get('/financial', [ReportController::class, 'financial'])->name('financial');
        });
        
        Route::middleware('permission:reports.export')->group(function () {
            Route::get('/export/{type}', [ReportController::class, 'export'])->name('export');
        });
    });
    
    // Permit Management (Izin)
    Route::prefix('permits')->name('permits.')->group(function () {
        Route::get('/', [App\Http\Controllers\PermitController::class, 'index'])->name('index');

        // Day Exchange (Tukar Hari)
        Route::prefix('day-exchange')->name('day-exchange.')->group(function () {
            Route::get('/', [App\Http\Controllers\PermitController::class, 'dayExchangeIndex'])->name('index');
            Route::get('/create', [App\Http\Controllers\PermitController::class, 'dayExchangeCreate'])->name('create');
            Route::post('/', [App\Http\Controllers\PermitController::class, 'dayExchangeStore'])->name('store');
            Route::get('/{dayExchange}', [App\Http\Controllers\PermitController::class, 'dayExchangeShow'])->name('show');
            Route::get('/{dayExchange}/edit', [App\Http\Controllers\PermitController::class, 'dayExchangeEdit'])->name('edit');
            Route::put('/{dayExchange}', [App\Http\Controllers\PermitController::class, 'dayExchangeUpdate'])->name('update');
            Route::delete('/{dayExchange}', [App\Http\Controllers\PermitController::class, 'dayExchangeDestroy'])->name('destroy');
        });

        // Overtime (Lembur)
        Route::prefix('overtime')->name('overtime.')->group(function () {
            Route::get('/', [App\Http\Controllers\PermitController::class, 'overtimeIndex'])->name('index');
            Route::get('/create', [App\Http\Controllers\PermitController::class, 'overtimeCreate'])->name('create');
            Route::post('/', [App\Http\Controllers\PermitController::class, 'overtimeStore'])->name('store');
            Route::get('/{overtime}', [App\Http\Controllers\PermitController::class, 'overtimeShow'])->name('show');
            Route::get('/{overtime}/edit', [App\Http\Controllers\PermitController::class, 'overtimeEdit'])->name('edit');
            Route::put('/{overtime}', [App\Http\Controllers\PermitController::class, 'overtimeUpdate'])->name('update');
            Route::delete('/{overtime}', [App\Http\Controllers\PermitController::class, 'overtimeDestroy'])->name('destroy');
        });

        // Leave (Cuti)
        Route::prefix('leave')->name('leave.')->group(function () {
            Route::get('/', [App\Http\Controllers\PermitController::class, 'leaveIndex'])->name('index');
            Route::get('/create', [App\Http\Controllers\PermitController::class, 'leaveCreate'])->name('create');
            Route::post('/', [App\Http\Controllers\PermitController::class, 'leaveStore'])->name('store');
            Route::get('/{leave}', [App\Http\Controllers\PermitController::class, 'leaveShow'])->name('show');
            Route::get('/{leave}/edit', [App\Http\Controllers\PermitController::class, 'leaveEdit'])->name('edit');
            Route::put('/{leave}', [App\Http\Controllers\PermitController::class, 'leaveUpdate'])->name('update');
            Route::delete('/{leave}', [App\Http\Controllers\PermitController::class, 'leaveDestroy'])->name('destroy');
        });
    });

    // Settings
    Route::middleware('permission:settings.view')->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::get('/settings/attendance-rules', [SettingController::class, 'attendanceRules'])->name('settings.attendance-rules');
        Route::get('/settings/leave-types', [SettingController::class, 'leaveTypes'])->name('settings.leave-types');
    });
});

// API Routes for AJAX calls
Route::prefix('api')->middleware('auth')->group(function () {
    Route::get('/departments/{department}/positions', function($departmentId) {
        return \App\Models\Position::where('department_id', $departmentId)
                                  ->where('is_active', true)
                                  ->get(['id', 'name', 'base_salary']);
    });
    
    Route::get('/users/search', function(\Illuminate\Http\Request $request) {
        $query = $request->get('q');
        return \App\Models\User::where('first_name', 'like', "%{$query}%")
                              ->orWhere('last_name', 'like', "%{$query}%")
                              ->orWhere('employee_id', 'like', "%{$query}%")
                              ->with('employee.department', 'employee.position')
                              ->limit(10)
                              ->get();
    });
});
