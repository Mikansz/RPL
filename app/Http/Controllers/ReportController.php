<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\Leave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function hr(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        
        $employeeCount = Employee::where('employment_status', 'active')->count();
        $newEmployees = Employee::whereBetween('hire_date', [$startDate, $endDate])->count();
        $resignedEmployees = Employee::where('employment_status', 'resigned')
                                   ->whereBetween('updated_at', [$startDate, $endDate])
                                   ->count();
        
        $leaveRequests = Leave::whereBetween('created_at', [$startDate, $endDate])
                             ->selectRaw('status, COUNT(*) as count')
                             ->groupBy('status')
                             ->pluck('count', 'status')
                             ->toArray();
        
        $departmentStats = Employee::join('departments', 'employees.department_id', '=', 'departments.id')
                                 ->where('employees.employment_status', 'active')
                                 ->selectRaw('departments.name, COUNT(*) as count')
                                 ->groupBy('departments.id', 'departments.name')
                                 ->get();
        
        return view('reports.hr', compact(
            'employeeCount', 'newEmployees', 'resignedEmployees', 
            'leaveRequests', 'departmentStats', 'startDate', 'endDate'
        ));
    }

    public function attendance(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        $departmentId = $request->get('department_id');
        
        $query = Attendance::with('user.employee.department')
                          ->whereBetween('date', [$startDate, $endDate]);
        
        if ($departmentId) {
            $query->whereHas('user.employee', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }
        
        $attendances = $query->paginate(20);
        
        $stats = [
            'total_present' => $query->where('status', 'present')->count(),
            'total_late' => $query->where('status', 'late')->count(),
            'total_absent' => $query->where('status', 'absent')->count(),
            'total_leave' => $query->where('status', 'leave')->count(),
        ];
        
        $departments = \App\Models\Department::where('is_active', true)->get();
        
        return view('reports.attendance', compact(
            'attendances', 'stats', 'departments', 'startDate', 'endDate', 'departmentId'
        ));
    }

    public function financial(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        
        $payrollStats = Payroll::whereBetween('period_start', [$startDate, $endDate])
                              ->selectRaw('
                                  SUM(gross_salary) as total_gross,
                                  SUM(total_allowances) as total_allowances,
                                  SUM(total_deductions) as total_deductions,
                                  SUM(net_salary) as total_net,
                                  COUNT(*) as total_employees
                              ')
                              ->first();
        
        $departmentPayroll = Payroll::join('users', 'payrolls.user_id', '=', 'users.id')
                                   ->join('employees', 'users.id', '=', 'employees.user_id')
                                   ->join('departments', 'employees.department_id', '=', 'departments.id')
                                   ->whereBetween('payrolls.period_start', [$startDate, $endDate])
                                   ->selectRaw('
                                       departments.name as department_name,
                                       SUM(payrolls.net_salary) as total_salary,
                                       COUNT(*) as employee_count
                                   ')
                                   ->groupBy('departments.id', 'departments.name')
                                   ->get();
        
        return view('reports.financial', compact(
            'payrollStats', 'departmentPayroll', 'startDate', 'endDate'
        ));
    }

    public function export(Request $request, $type)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        switch ($type) {
            case 'attendance':
                return $this->exportAttendance($startDate, $endDate);
            case 'payroll':
                return $this->exportPayroll($startDate, $endDate);
            case 'employees':
                return $this->exportEmployees();
            case 'leaves':
                return $this->exportLeaves($startDate, $endDate);
            case 'overtime':
                return $this->exportOvertime($startDate, $endDate);
            default:
                return redirect()->back()->with('error', 'Invalid export type.');
        }
    }

    public function ceoReports(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        // Get all available reports for CEO
        $reports = [
            'hr' => [
                'title' => 'Laporan HR',
                'description' => 'Laporan karyawan, departemen, dan posisi',
                'icon' => 'fas fa-users',
                'color' => 'info',
                'route' => 'reports.hr',
                'export' => 'employees'
            ],
            'attendance' => [
                'title' => 'Laporan Absensi',
                'description' => 'Laporan kehadiran, keterlambatan, dan absensi karyawan',
                'icon' => 'fas fa-clock',
                'color' => 'primary',
                'route' => 'reports.attendance',
                'export' => 'attendance'
            ],
            'financial' => [
                'title' => 'Laporan Keuangan',
                'description' => 'Laporan payroll dan keuangan perusahaan',
                'icon' => 'fas fa-chart-line',
                'color' => 'success',
                'route' => 'reports.financial',
                'export' => 'payroll'
            ],
            'leaves' => [
                'title' => 'Laporan Cuti',
                'description' => 'Laporan penggunaan cuti dan izin karyawan',
                'icon' => 'fas fa-calendar-times',
                'color' => 'warning',
                'route' => 'reports.leaves',
                'export' => 'leaves'
            ],
            'overtime' => [
                'title' => 'Laporan Lembur',
                'description' => 'Laporan lembur dan overtime karyawan',
                'icon' => 'fas fa-business-time',
                'color' => 'secondary',
                'route' => 'reports.overtime',
                'export' => 'overtime'
            ]
        ];

        // Get summary statistics
        $summary = [
            'total_employees' => Employee::where('employment_status', 'active')->count(),
            'total_departments' => \App\Models\Department::where('is_active', true)->count(),
            'attendance_rate' => $this->getAttendanceRate($startDate, $endDate),
            'total_payroll' => \App\Models\Payroll::whereBetween('period_start', [$startDate, $endDate])->sum('net_salary'),
            'pending_leaves' => Leave::where('status', 'pending')->count(),
        ];

        return view('reports.ceo', compact('reports', 'summary', 'startDate', 'endDate'));
    }

    private function exportAttendance($startDate, $endDate)
    {
        $attendances = Attendance::with('user.employee.department')
                                ->whereBetween('date', [$startDate, $endDate])
                                ->get();
        
        $filename = "attendance_report_{$startDate}_to_{$endDate}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        
        $callback = function() use ($attendances) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Employee ID', 'Name', 'Department', 'Clock In', 'Clock Out', 'Status']);
            
            foreach ($attendances as $attendance) {
                fputcsv($file, [
                    $attendance->date,
                    $attendance->user->employee_id,
                    $attendance->user->full_name,
                    $attendance->user->employee->department->name ?? '',
                    $attendance->clock_in_time,
                    $attendance->clock_out_time,
                    $attendance->status,
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    private function exportPayroll($startDate, $endDate)
    {
        $payrolls = Payroll::with('user.employee.department')
                          ->whereBetween('period_start', [$startDate, $endDate])
                          ->get();
        
        $filename = "payroll_report_{$startDate}_to_{$endDate}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        
        $callback = function() use ($payrolls) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Employee ID', 'Name', 'Department', 'Period', 'Gross Salary', 'Allowances', 'Deductions', 'Net Salary']);
            
            foreach ($payrolls as $payroll) {
                fputcsv($file, [
                    $payroll->user->employee_id,
                    $payroll->user->full_name,
                    $payroll->user->employee->department->name ?? '',
                    $payroll->period_start . ' - ' . $payroll->period_end,
                    $payroll->gross_salary,
                    $payroll->total_allowances,
                    $payroll->total_deductions,
                    $payroll->net_salary,
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    private function exportEmployees()
    {
        $employees = Employee::with('user', 'department', 'position')->get();
        
        $filename = "employees_report_" . now()->format('Y-m-d') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        
        $callback = function() use ($employees) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Employee ID', 'Name', 'Email', 'Department', 'Position', 'Hire Date', 'Status', 'Basic Salary']);
            
            foreach ($employees as $employee) {
                fputcsv($file, [
                    $employee->user->employee_id,
                    $employee->user->full_name,
                    $employee->user->email,
                    $employee->department->name,
                    $employee->position->name,
                    $employee->hire_date,
                    $employee->employment_status,
                    $employee->basic_salary,
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    private function exportLeaves($startDate, $endDate)
    {
        $leaves = Leave::with(['user.employee.department', 'leaveType'])
                       ->whereBetween('start_date', [$startDate, $endDate])
                       ->get();

        $filename = "leaves_report_{$startDate}_to_{$endDate}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($leaves) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Employee ID', 'Name', 'Department', 'Leave Type', 'Start Date', 'End Date', 'Days', 'Status', 'Reason']);

            foreach ($leaves as $leave) {
                fputcsv($file, [
                    $leave->user->employee_id,
                    $leave->user->full_name,
                    $leave->user->employee->department->name ?? '',
                    $leave->leaveType->name ?? '',
                    $leave->start_date,
                    $leave->end_date,
                    $leave->total_days,
                    $leave->status,
                    $leave->reason,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportOvertime($startDate, $endDate)
    {
        $overtimes = \App\Models\OvertimeRequest::with(['user.employee.department'])
                                               ->whereBetween('overtime_date', [$startDate, $endDate])
                                               ->get();

        $filename = "overtime_report_{$startDate}_to_{$endDate}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($overtimes) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Employee ID', 'Name', 'Department', 'Date', 'Start Time', 'End Time', 'Hours', 'Status', 'Reason']);

            foreach ($overtimes as $overtime) {
                fputcsv($file, [
                    $overtime->user->employee_id,
                    $overtime->user->full_name,
                    $overtime->user->employee->department->name ?? '',
                    $overtime->overtime_date,
                    $overtime->start_time,
                    $overtime->end_time,
                    $overtime->total_hours,
                    $overtime->status,
                    $overtime->reason,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getAttendanceRate($startDate = null, $endDate = null)
    {
        if (!$startDate) $startDate = now()->startOfMonth()->format('Y-m-d');
        if (!$endDate) $endDate = now()->endOfMonth()->format('Y-m-d');

        $totalDays = Attendance::whereBetween('date', [$startDate, $endDate])->count();
        $presentDays = Attendance::whereBetween('date', [$startDate, $endDate])
                                ->whereIn('status', ['present', 'late'])
                                ->count();

        return $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 0;
    }

    public function leaves(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        $departmentId = $request->get('department_id');

        $query = Leave::with(['user.employee.department', 'leaveType'])
                     ->whereBetween('start_date', [$startDate, $endDate]);

        if ($departmentId) {
            $query->whereHas('user.employee', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        $leaves = $query->paginate(20);

        $stats = [
            'total_approved' => $query->where('status', 'approved')->count(),
            'total_pending' => $query->where('status', 'pending')->count(),
            'total_rejected' => $query->where('status', 'rejected')->count(),
            'total_days' => $query->where('status', 'approved')->sum('total_days'),
        ];

        $departments = \App\Models\Department::where('is_active', true)->get();

        return view('reports.leaves', compact(
            'leaves', 'stats', 'departments', 'startDate', 'endDate', 'departmentId'
        ));
    }

    public function overtime(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        $departmentId = $request->get('department_id');

        $query = \App\Models\OvertimeRequest::with(['user.employee.department'])
                                           ->whereBetween('overtime_date', [$startDate, $endDate]);

        if ($departmentId) {
            $query->whereHas('user.employee', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        $overtimes = $query->paginate(20);

        $stats = [
            'total_approved' => $query->where('status', 'approved')->count(),
            'total_pending' => $query->where('status', 'pending')->count(),
            'total_rejected' => $query->where('status', 'rejected')->count(),
            'total_hours' => $query->where('status', 'approved')->sum('total_hours'),
        ];

        $departments = \App\Models\Department::where('is_active', true)->get();

        return view('reports.overtime', compact(
            'overtimes', 'stats', 'departments', 'startDate', 'endDate', 'departmentId'
        ));
    }
}
