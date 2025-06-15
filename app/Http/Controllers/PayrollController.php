<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\SalaryComponent;
use App\Models\User;
use Carbon\Carbon;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $query = Payroll::with(['user', 'payrollPeriod', 'approvedBy']);

        if ($request->filled('period_id')) {
            $query->where('payroll_period_id', $request->period_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        $payrolls = $query->orderBy('created_at', 'desc')->paginate(20);
        $periods = PayrollPeriod::orderBy('start_date', 'desc')->get();

        // Calculate summary data
        $total_payroll = $payrolls->sum('net_salary');
        $processed_count = $payrolls->where('status', 'approved')->count() + $payrolls->where('status', 'paid')->count();
        $pending_count = $payrolls->where('status', 'pending')->count() + $payrolls->where('status', 'draft')->count();
        $total_employees = $payrolls->count();

        return view('payroll.index', compact('payrolls', 'periods', 'total_payroll', 'processed_count', 'pending_count', 'total_employees'));
    }

    public function slip()
    {
        $user = Auth::user();
        $payrolls = Payroll::where('user_id', $user->id)
                          ->with('payrollPeriod', 'details.salaryComponent')
                          ->orderBy('created_at', 'desc')
                          ->paginate(12);

        return view('payroll.slip', compact('payrolls'));
    }

    public function show(Payroll $payroll)
    {
        $payroll->load(['user', 'payrollPeriod', 'details.salaryComponent']);
        return view('payroll.show', compact('payroll'));
    }

    public function periods()
    {
        $periods = PayrollPeriod::with('createdBy', 'approvedBy')
                               ->orderBy('start_date', 'desc')
                               ->paginate(20);

        return view('payroll.periods.index', compact('periods'));
    }

    public function createPeriod()
    {
        return view('payroll.periods.create');
    }

    public function storePeriod(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'pay_date' => 'nullable|date',
        ]);

        // Check for overlapping periods
        $overlapping = PayrollPeriod::where(function($query) use ($request) {
            $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                  ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                  ->orWhere(function($q) use ($request) {
                      $q->where('start_date', '<=', $request->start_date)
                        ->where('end_date', '>=', $request->end_date);
                  });
        })->exists();

        if ($overlapping) {
            return back()->withErrors(['start_date' => 'Periode ini bertumpang tindih dengan periode yang sudah ada.'])
                        ->withInput();
        }

        PayrollPeriod::create([
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'pay_date' => $request->pay_date,
            'status' => 'draft',
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('payroll.periods.index')
                        ->with('success', 'Periode payroll berhasil dibuat.');
    }



    public function calculate(PayrollPeriod $period)
    {
        if ($period->status !== 'draft') {
            return back()->with('error', 'Periode payroll sudah diproses.');
        }

        $employees = Employee::where('employment_status', 'active')
                           ->with(['user.salaryComponents' => function($query) {
                               $query->where('salary_components.is_active', true);
                           }])
                           ->get();

        return view('payroll.calculate', compact('period', 'employees'));
    }

    public function process(Request $request, PayrollPeriod $period)
    {
        if ($period->status !== 'draft') {
            return back()->with('error', 'Periode payroll sudah diproses.');
        }

        $employees = Employee::where('employment_status', 'active')->get();

        foreach ($employees as $employee) {
            $this->calculateEmployeePayroll($employee, $period);
        }

        $period->update(['status' => 'calculated']);

        return redirect()->route('payroll.periods.index')
                        ->with('success', 'Payroll berhasil dihitung untuk semua karyawan.');
    }

    private function calculateEmployeePayroll(Employee $employee, PayrollPeriod $period)
    {
        // Get attendance data for the period
        $attendances = Attendance::where('user_id', $employee->user_id)
                                ->whereBetween('date', [$period->start_date, $period->end_date])
                                ->get();

        $totalWorkingDays = $period->start_date->diffInDays($period->end_date) + 1;
        $totalPresentDays = $attendances->whereIn('status', ['present', 'late'])->count();
        $totalAbsentDays = $attendances->where('status', 'absent')->count();
        $totalLateDays = $attendances->where('status', 'late')->count();
        $totalOvertimeHours = $attendances->sum('overtime_minutes') / 60;

        // Calculate basic salary (prorated if absent)
        $basicSalary = $employee->basic_salary;
        if ($totalAbsentDays > 0) {
            $basicSalary = ($basicSalary / $totalWorkingDays) * $totalPresentDays;
        }

        // Get salary components
        $salaryComponents = $employee->user->salaryComponents()->where('salary_components.is_active', true)->get();
        
        $totalAllowances = 0;
        $totalDeductions = 0;
        $overtimeAmount = 0;

        $payrollDetails = [];

        foreach ($salaryComponents as $component) {
            // Use pivot amount if available, otherwise use default calculation
            $customAmount = isset($component->pivot) ? $component->pivot->amount : null;
            $amount = $component->calculateAmount($basicSalary, $customAmount);

            if ($component->type === 'allowance') {
                $totalAllowances += $amount;
            } elseif ($component->type === 'deduction') {
                $totalDeductions += $amount;
            }

            $payrollDetails[] = [
                'salary_component_id' => $component->id,
                'amount' => $amount,
                'calculation_notes' => "Calculated for period {$period->name}",
            ];
        }

        // Calculate overtime
        if ($totalOvertimeHours > 0) {
            $overtimeRate = ($basicSalary / 173) * 1.5; // 173 = average working hours per month
            $overtimeAmount = $overtimeRate * $totalOvertimeHours;
        }

        // Calculate gross and net salary
        $grossSalary = $basicSalary + $totalAllowances + $overtimeAmount;
        $taxAmount = $this->calculateTax($grossSalary);
        $netSalary = $grossSalary - $totalDeductions - $taxAmount;

        // Create or update payroll record
        $payroll = Payroll::updateOrCreate(
            [
                'payroll_period_id' => $period->id,
                'user_id' => $employee->user_id,
            ],
            [
                'basic_salary' => $basicSalary,
                'total_allowances' => $totalAllowances,
                'total_deductions' => $totalDeductions,
                'overtime_amount' => $overtimeAmount,
                'gross_salary' => $grossSalary,
                'tax_amount' => $taxAmount,
                'net_salary' => $netSalary,
                'total_working_days' => $totalWorkingDays,
                'total_present_days' => $totalPresentDays,
                'total_absent_days' => $totalAbsentDays,
                'total_late_days' => $totalLateDays,
                'total_overtime_hours' => $totalOvertimeHours,
                'status' => 'draft',
            ]
        );

        // Save payroll details
        $payroll->details()->delete(); // Remove existing details
        foreach ($payrollDetails as $detail) {
            $payroll->details()->create($detail);
        }
    }

    private function calculateTax($grossSalary)
    {
        // Simplified tax calculation (PPh 21)
        // This should be more complex in real implementation
        $taxableIncome = max(0, $grossSalary - 4500000); // PTKP per month
        
        if ($taxableIncome <= 5000000) {
            return $taxableIncome * 0.05;
        } elseif ($taxableIncome <= 25000000) {
            return 250000 + (($taxableIncome - 5000000) * 0.15);
        } elseif ($taxableIncome <= 50000000) {
            return 3250000 + (($taxableIncome - 25000000) * 0.25);
        } else {
            return 9500000 + (($taxableIncome - 50000000) * 0.30);
        }
    }

    public function approve(Payroll $payroll)
    {
        // Check if payroll is in a state that can be approved
        if (!in_array($payroll->status, ['draft', 'pending'])) {
            return back()->with('error', 'Payroll tidak dapat disetujui karena status saat ini: ' . $payroll->status);
        }

        $payroll->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Payroll berhasil disetujui.');
    }

    public function approvePeriod(PayrollPeriod $period)
    {
        if ($period->status !== 'calculated') {
            return back()->with('error', 'Periode payroll belum dihitung.');
        }

        $period->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        // Update all payrolls in this period
        $period->payrolls()->update(['status' => 'approved']);

        return back()->with('success', 'Periode payroll berhasil disetujui.');
    }

    public function bulkApprove(Request $request)
    {
        $request->validate([
            'payroll_ids' => 'required|array',
            'payroll_ids.*' => 'exists:payrolls,id'
        ]);

        $payrollIds = $request->payroll_ids;

        // Get payrolls that can be approved
        $payrolls = Payroll::whereIn('id', $payrollIds)
                          ->whereIn('status', ['draft', 'pending'])
                          ->get();

        if ($payrolls->isEmpty()) {
            return back()->with('error', 'Tidak ada payroll yang dapat disetujui.');
        }

        $approvedCount = 0;
        foreach ($payrolls as $payroll) {
            $payroll->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
            $approvedCount++;
        }

        return back()->with('success', "Berhasil menyetujui {$approvedCount} payroll.");
    }

    public function reports(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfYear()->format('Y-m-d'));

        $payrolls = Payroll::with(['user', 'payrollPeriod'])
                          ->whereHas('payrollPeriod', function($q) use ($startDate, $endDate) {
                              $q->whereBetween('start_date', [$startDate, $endDate]);
                          })
                          ->get();

        $summary = [
            'total_employees' => $payrolls->groupBy('user_id')->count(),
            'total_gross_salary' => $payrolls->sum('gross_salary'),
            'total_net_salary' => $payrolls->sum('net_salary'),
            'total_tax' => $payrolls->sum('tax_amount'),
            'total_deductions' => $payrolls->sum('total_deductions'),
        ];

        return view('payroll.reports', compact('payrolls', 'summary', 'startDate', 'endDate'));
    }

    public function downloadSlip(Payroll $payroll)
    {
        $payroll->load(['user', 'payrollPeriod', 'details.salaryComponent']);

        // This would generate a PDF slip
        // For now, return a view
        return view('payroll.slip-pdf', compact('payroll'));
    }

    public function exportReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfYear()->format('Y-m-d'));

        $payrolls = Payroll::with(['user', 'payrollPeriod'])
                          ->whereHas('payrollPeriod', function($q) use ($startDate, $endDate) {
                              $q->whereBetween('start_date', [$startDate, $endDate]);
                          })
                          ->get();

        // For now, return JSON data
        // In a real implementation, this would generate Excel/CSV
        return response()->json([
            'data' => $payrolls,
            'summary' => [
                'total_employees' => $payrolls->groupBy('user_id')->count(),
                'total_gross_salary' => $payrolls->sum('gross_salary'),
                'total_net_salary' => $payrolls->sum('net_salary'),
                'total_tax' => $payrolls->sum('tax_amount'),
                'total_deductions' => $payrolls->sum('total_deductions'),
            ]
        ]);
    }
}
