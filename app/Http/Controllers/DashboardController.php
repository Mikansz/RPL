<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Employee;
use App\Models\Attendance;

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
        $data = [
            'total_employees' => Employee::where('employment_status', 'active')->count(),
            'total_departments' => Department::where('is_active', true)->count(),
            'attendance_today' => Attendance::where('date', today())->count(),
            'pending_leaves' => Leave::where('status', 'pending')->count(),
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
                ->get()
                ->map(function($attendance) {
                    return [
                        'date' => $attendance->date->format('Y-m-d'),
                        'total_work_minutes' => $attendance->total_work_minutes ?? 0,
                    ];
                })
                ->toArray(),

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
                'attendance' => Attendance::whereMonth('date', $date->month)
                    ->whereYear('date', $date->year)
                    ->count(),
            ];
        }
        return $months;
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
        // Simplified leave balance calculation - return annual leave balance as number
        $usedAnnualLeave = Leave::where('user_id', $userId)
            ->whereYear('start_date', now()->year)
            ->where('status', 'approved')
            ->sum('total_days');

        return max(0, 12 - $usedAnnualLeave);
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
