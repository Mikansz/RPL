<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\Leave;
use App\Models\Department;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $role = $user->roles->first();

        if (!$role) {
            return view('dashboard.default');
        }

        switch ($role->name) {
            case 'ceo':
                return $this->ceoDashboard();
            case 'cfo':
                return $this->cfoDashboard();
            case 'hrd':
                return $this->hrdDashboard();
            case 'personalia':
                return $this->personaliaDashboard();
            case 'karyawan':
                return $this->karyawanDashboard();
            default:
                return view('dashboard.default');
        }
    }

    private function ceoDashboard()
    {
        $data = [
            'total_employees' => Employee::where('employment_status', 'active')->count(),
            'total_departments' => Department::where('is_active', true)->count(),
            'monthly_payroll' => Payroll::whereHas('payrollPeriod', function($q) {
                $q->where('start_date', '<=', now())
                  ->where('end_date', '>=', now());
            })->sum('net_salary'),
            'attendance_rate' => $this->getAttendanceRate(),
            'recent_leaves' => Leave::with(['user', 'leaveType'])
                ->where('status', 'pending')
                ->latest()
                ->take(5)
                ->get(),
            'department_stats' => $this->getDepartmentStats(),
            'monthly_trends' => $this->getMonthlyTrends(),
        ];

        return view('dashboard.ceo', $data);
    }

    private function cfoDashboard()
    {
        $currentMonth = now()->format('Y-m');
        
        $data = [
            'monthly_payroll_cost' => Payroll::whereHas('payrollPeriod', function($q) use ($currentMonth) {
                $q->whereRaw("DATE_FORMAT(start_date, '%Y-%m') = ?", [$currentMonth]);
            })->sum('net_salary'),
            'yearly_payroll_cost' => Payroll::whereHas('payrollPeriod', function($q) {
                $q->whereYear('start_date', now()->year);
            })->sum('net_salary'),
            'payroll_by_department' => $this->getPayrollByDepartment(),
            'cost_trends' => $this->getCostTrends(),
            'budget_analysis' => $this->getBudgetAnalysis(),
            'pending_approvals' => Payroll::where('status', 'draft')->count(),
        ];

        return view('dashboard.cfo', $data);
    }

    private function hrdDashboard()
    {
        $data = [
            'total_employees' => Employee::where('employment_status', 'active')->count(),
            'new_employees_this_month' => Employee::whereMonth('hire_date', now()->month)
                ->whereYear('hire_date', now()->year)
                ->count(),
            'pending_leaves' => Leave::where('status', 'pending')->count(),
            'attendance_today' => Attendance::where('date', today())
                ->where('status', 'present')
                ->count(),
            'recent_employees' => Employee::with(['user', 'department', 'position'])
                ->latest()
                ->take(5)
                ->get(),
            'leave_requests' => Leave::with(['user', 'leaveType'])
                ->where('status', 'pending')
                ->latest()
                ->take(10)
                ->get(),
            'attendance_summary' => $this->getAttendanceSummary(),
        ];

        return view('dashboard.hrd', $data);
    }

    private function personaliaDashboard()
    {
        $data = [
            'employees_count' => Employee::where('employment_status', 'active')->count(),
            'attendance_today' => Attendance::where('date', today())->count(),
            'absent_today' => $this->getAbsentToday(),
            'late_today' => Attendance::where('date', today())
                ->where('late_minutes', '>', 0)
                ->count(),
            'recent_attendance' => Attendance::with('user')
                ->where('date', today())
                ->latest()
                ->take(10)
                ->get(),
            'upcoming_leaves' => Leave::with(['user', 'leaveType'])
                ->where('status', 'approved')
                ->where('start_date', '>=', today())
                ->orderBy('start_date')
                ->take(10)
                ->get(),
        ];

        return view('dashboard.personalia', $data);
    }

    private function karyawanDashboard()
    {
        $user = Auth::user();
        
        $data = [
            'today_attendance' => Attendance::where('user_id', $user->id)
                ->where('date', today())
                ->first(),
            'monthly_attendance' => Attendance::where('user_id', $user->id)
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->get(),
            'recent_payroll' => Payroll::where('user_id', $user->id)
                ->with('payrollPeriod')
                ->latest()
                ->first(),
            'leave_balance' => $this->getLeaveBalance($user->id),
            'recent_leaves' => Leave::where('user_id', $user->id)
                ->with('leaveType')
                ->latest()
                ->take(5)
                ->get(),
            'attendance_stats' => $this->getUserAttendanceStats($user->id),
        ];

        return view('dashboard.karyawan', $data);
    }

    private function getAttendanceRate()
    {
        $totalWorkingDays = now()->day;
        $totalEmployees = Employee::where('employment_status', 'active')->count();
        $totalExpectedAttendance = $totalWorkingDays * $totalEmployees;
        
        $actualAttendance = Attendance::whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->where('status', 'present')
            ->count();
            
        return $totalExpectedAttendance > 0 ? round(($actualAttendance / $totalExpectedAttendance) * 100, 2) : 0;
    }

    private function getDepartmentStats()
    {
        return Department::withCount(['employees' => function($q) {
            $q->whereHas('user', function($q2) {
                $q2->where('status', 'active');
            });
        }])->where('is_active', true)->get();
    }

    private function getMonthlyTrends()
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = [
                'month' => $date->format('M Y'),
                'employees' => Employee::whereMonth('hire_date', '<=', $date->month)
                    ->whereYear('hire_date', '<=', $date->year)
                    ->where('employment_status', 'active')
                    ->count(),
                'payroll' => Payroll::whereHas('payrollPeriod', function($q) use ($date) {
                    $q->whereMonth('start_date', $date->month)
                      ->whereYear('start_date', $date->year);
                })->sum('net_salary'),
            ];
        }
        return $months;
    }

    private function getPayrollByDepartment()
    {
        return Department::with(['employees.user.payrolls' => function($q) {
            $q->whereHas('payrollPeriod', function($q2) {
                $q2->whereMonth('start_date', now()->month)
                   ->whereYear('start_date', now()->year);
            });
        }])->get()->map(function($dept) {
            return [
                'name' => $dept->name,
                'total' => $dept->employees->sum(function($emp) {
                    return $emp->user->payrolls->sum('net_salary');
                })
            ];
        });
    }

    private function getCostTrends()
    {
        $trends = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $trends[] = [
                'month' => $date->format('M Y'),
                'amount' => Payroll::whereHas('payrollPeriod', function($q) use ($date) {
                    $q->whereMonth('start_date', $date->month)
                      ->whereYear('start_date', $date->year);
                })->sum('net_salary'),
            ];
        }
        return $trends;
    }

    private function getBudgetAnalysis()
    {
        // Simplified budget analysis - in real app, this would come from budget table
        $currentMonthCost = Payroll::whereHas('payrollPeriod', function($q) {
            $q->whereMonth('start_date', now()->month)
              ->whereYear('start_date', now()->year);
        })->sum('net_salary');
        
        $budgetLimit = 1000000000; // 1 billion IDR as example
        
        return [
            'actual' => $currentMonthCost,
            'budget' => $budgetLimit,
            'variance' => $budgetLimit - $currentMonthCost,
            'percentage' => $budgetLimit > 0 ? round(($currentMonthCost / $budgetLimit) * 100, 2) : 0,
        ];
    }

    private function getAttendanceSummary()
    {
        $today = today();
        return [
            'present' => Attendance::where('date', $today)->where('status', 'present')->count(),
            'late' => Attendance::where('date', $today)->where('status', 'late')->count(),
            'absent' => $this->getAbsentToday(),
            'sick' => Attendance::where('date', $today)->where('status', 'sick')->count(),
            'leave' => Attendance::where('date', $today)->where('status', 'leave')->count(),
        ];
    }

    private function getAbsentToday()
    {
        $totalEmployees = Employee::where('employment_status', 'active')->count();
        $presentToday = Attendance::where('date', today())->count();
        return $totalEmployees - $presentToday;
    }

    private function getLeaveBalance($userId)
    {
        // Simplified leave balance calculation
        return [
            'annual' => 12 - Leave::where('user_id', $userId)
                ->whereYear('start_date', now()->year)
                ->where('status', 'approved')
                ->sum('total_days'),
            'sick' => 30, // Usually unlimited or high limit
        ];
    }

    private function getUserAttendanceStats($userId)
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        return [
            'present' => Attendance::where('user_id', $userId)
                ->whereMonth('date', $currentMonth)
                ->whereYear('date', $currentYear)
                ->where('status', 'present')
                ->count(),
            'late' => Attendance::where('user_id', $userId)
                ->whereMonth('date', $currentMonth)
                ->whereYear('date', $currentYear)
                ->where('late_minutes', '>', 0)
                ->count(),
            'absent' => Attendance::where('user_id', $userId)
                ->whereMonth('date', $currentMonth)
                ->whereYear('date', $currentYear)
                ->where('status', 'absent')
                ->count(),
        ];
    }
}
