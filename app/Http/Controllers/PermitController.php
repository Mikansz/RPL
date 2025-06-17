<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\OvertimeRequest;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\PermitType;
use Carbon\Carbon;

class PermitController extends Controller
{
    // Dashboard untuk semua izin
    public function index()
    {
        $user = Auth::user();
        
        $data = [
            'pending_overtime' => OvertimeRequest::byUser($user->id)->pending()->count(),
            'pending_leaves' => LeaveRequest::byUser($user->id)->pending()->count(),

            'recent_overtime' => OvertimeRequest::byUser($user->id)->latest()->take(5)->get(),
            'recent_leaves' => LeaveRequest::byUser($user->id)->latest()->take(5)->get(),

            'monthly_overtime_hours' => OvertimeRequest::byUser($user->id)
                                                      ->thisMonth()
                                                      ->approved()
                                                      ->sum('actual_hours'),

            'yearly_leave_days' => LeaveRequest::byUser($user->id)
                                              ->thisYear()
                                              ->approved()
                                              ->sum('total_days'),
        ];

        return view('permits.index', $data);
    }



    // LEMBUR (Overtime)
    public function overtimeIndex()
    {
        $user = Auth::user();
        $overtimes = OvertimeRequest::byUser($user->id)
                                   ->with('approvedBy')
                                   ->latest()
                                   ->paginate(10);

        $monthlyStats = [
            'total_hours' => OvertimeRequest::byUser($user->id)->thisMonth()->sum('actual_hours'),
            'total_amount' => OvertimeRequest::byUser($user->id)->thisMonth()->sum('overtime_amount'),
            'total_requests' => OvertimeRequest::byUser($user->id)->thisMonth()->count(),
        ];

        return view('permits.overtime.index', compact('overtimes', 'monthlyStats'));
    }

    public function overtimeCreate()
    {
        return view('permits.overtime.create');
    }

    public function overtimeStore(Request $request)
    {
        $request->validate([
            'overtime_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'work_description' => 'required|string|max:1000',
            'reason' => 'required|string|max:500',
        ]);

        $user = Auth::user();

        $overtime = new OvertimeRequest([
            'user_id' => $user->id,
            'overtime_date' => $request->overtime_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'work_description' => $request->work_description,
            'reason' => $request->reason,
        ]);

        $overtime->planned_hours = $overtime->calculatePlannedHours();

        // Check for conflicts
        if ($overtime->hasConflict()) {
            return back()->withErrors(['error' => 'Sudah ada pengajuan lembur pada tanggal tersebut.']);
        }

        // Validate request
        if (!$overtime->isValidRequest()) {
            return back()->withErrors(['error' => 'Pengajuan lembur tidak valid. Periksa tanggal dan jam.']);
        }

        $overtime->save();

        return redirect()->route('permits.overtime.index')
                        ->with('success', 'Pengajuan lembur berhasil dibuat.');
    }

    public function overtimeShow(OvertimeRequest $overtime)
    {
        // Check if user owns this overtime request
        if ($overtime->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
        return view('permits.overtime.show', compact('overtime'));
    }

    public function overtimeEdit(OvertimeRequest $overtime)
    {
        // Check if user owns this overtime request
        if ($overtime->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        if (!$overtime->canBeEdited()) {
            return back()->with('error', 'Pengajuan tidak dapat diubah.');
        }

        return view('permits.overtime.edit', compact('overtime'));
    }

    public function overtimeUpdate(Request $request, OvertimeRequest $overtime)
    {
        // Check if user owns this overtime request
        if ($overtime->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        if (!$overtime->canBeEdited()) {
            return back()->with('error', 'Pengajuan tidak dapat diubah.');
        }

        $request->validate([
            'overtime_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'work_description' => 'required|string|max:1000',
            'reason' => 'required|string|max:500',
        ]);

        $overtime->update($request->only([
            'overtime_date', 'start_time', 'end_time', 
            'work_description', 'reason'
        ]));

        $overtime->planned_hours = $overtime->calculatePlannedHours();
        $overtime->save();

        return redirect()->route('permits.overtime.index')
                        ->with('success', 'Pengajuan lembur berhasil diperbarui.');
    }

    public function overtimeDestroy(OvertimeRequest $overtime)
    {
        // Check if user owns this overtime request
        if ($overtime->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        if (!$overtime->canBeEdited()) {
            return back()->with('error', 'Pengajuan tidak dapat dihapus.');
        }

        $overtime->delete();

        return redirect()->route('permits.overtime.index')
                        ->with('success', 'Pengajuan lembur berhasil dihapus.');
    }

    public function overtimeApprove(OvertimeRequest $overtime)
    {
        // Check if user has permission to approve - prioritize roles for HRD and Admin
        $user = auth()->user();
        $hasPermission = $user->hasAnyRole(['Admin', 'HRD', 'HR', 'Manager']) ||
                        (method_exists($user, 'hasPermission') && $user->hasPermission('overtime.approve'));

        if (!$hasPermission) {
            abort(403, 'Unauthorized to approve overtime requests');
        }

        // User cannot approve their own overtime request
        if ($overtime->user_id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menyetujui pengajuan lembur Anda sendiri.');
        }

        if ($overtime->status !== 'pending') {
            return back()->with('error', 'Pengajuan lembur sudah diproses sebelumnya.');
        }

        $overtime->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Calculate overtime amount if not already calculated
        if (!$overtime->overtime_amount) {
            $overtime->updateOvertimeAmount();
        }

        return back()->with('success', 'Pengajuan lembur berhasil disetujui.');
    }

    public function overtimeReject(Request $request, OvertimeRequest $overtime)
    {
        // Check if user has permission to approve/reject - prioritize roles for HRD and Admin
        $user = auth()->user();
        $hasPermission = $user->hasAnyRole(['Admin', 'HRD', 'HR', 'Manager']) ||
                        (method_exists($user, 'hasPermission') && $user->hasPermission('overtime.approve'));

        if (!$hasPermission) {
            abort(403, 'Unauthorized to reject overtime requests');
        }

        // User cannot reject their own overtime request
        if ($overtime->user_id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menolak pengajuan lembur Anda sendiri.');
        }

        if ($overtime->status !== 'pending') {
            return back()->with('error', 'Pengajuan lembur sudah diproses sebelumnya.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $overtime->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return back()->with('success', 'Pengajuan lembur berhasil ditolak.');
    }

    public function overtimePending()
    {
        // Check if user has permission to approve overtime - prioritize roles for HRD and Admin
        $user = auth()->user();
        $hasPermission = $user->hasAnyRole(['Admin', 'HRD', 'HR', 'Manager']) ||
                        (method_exists($user, 'hasPermission') && $user->hasPermission('overtime.approve'));

        if (!$hasPermission) {
            abort(403, 'Unauthorized to view pending overtime requests');
        }

        $overtimes = OvertimeRequest::with(['user.employee', 'approvedBy'])
                                   ->where('status', 'pending')
                                   ->latest()
                                   ->paginate(10);

        $stats = [
            'total_pending' => OvertimeRequest::where('status', 'pending')->count(),
            'total_approved_today' => OvertimeRequest::where('status', 'approved')
                                                    ->whereDate('approved_at', today())
                                                    ->count(),
            'total_hours_pending' => OvertimeRequest::where('status', 'pending')
                                                   ->sum('planned_hours'),
        ];

        return view('permits.overtime.pending', compact('overtimes', 'stats'));
    }

    // CUTI (Leave)
    public function leaveIndex()
    {
        $user = Auth::user();
        $leaves = LeaveRequest::byUser($user->id)
                             ->with(['leaveType', 'approvedBy'])
                             ->latest()
                             ->paginate(10);

        $leaveBalance = $this->getLeaveBalance($user->id);

        // Calculate statistics
        $totalRemainingDays = collect($leaveBalance)->sum('remaining');
        $approvedLeaves = LeaveRequest::byUser($user->id)->thisYear()->approved()->sum('total_days');
        $pendingLeaves = LeaveRequest::byUser($user->id)->pending()->count();
        $totalLeaves = LeaveRequest::byUser($user->id)->count();

        return view('permits.leave.index', compact(
            'leaves',
            'leaveBalance',
            'totalRemainingDays',
            'approvedLeaves',
            'pendingLeaves',
            'totalLeaves'
        ));
    }

    public function leaveCreate()
    {
        $user = Auth::user();
        $leaveTypes = LeaveType::active()->get();

        // Calculate leave balance
        $leaveBalance = $this->getLeaveBalance($user->id);
        $totalRemainingDays = collect($leaveBalance)->sum('remaining');

        // Calculate pending leave days
        $pendingLeaveDays = LeaveRequest::byUser($user->id)->pending()->sum('total_days');

        return view('permits.leave.create', compact(
            'leaveTypes',
            'leaveBalance',
            'totalRemainingDays',
            'pendingLeaveDays'
        ));
    }

    public function leaveStore(Request $request)
    {
        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'emergency_contact' => 'nullable|string|max:100',
            'emergency_phone' => 'nullable|string|max:20',
            'work_handover' => 'nullable|string|max:1000',
            'is_half_day' => 'boolean',
            'half_day_type' => 'nullable|in:morning,afternoon',
            'attachments.*' => 'file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $user = Auth::user();

        $leave = new LeaveRequest([
            'user_id' => $user->id,
            'leave_type_id' => $request->leave_type_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'notes' => $request->notes,
            'emergency_contact' => $request->emergency_contact,
            'emergency_phone' => $request->emergency_phone,
            'work_handover' => $request->work_handover,
            'is_half_day' => $request->boolean('is_half_day'),
            'half_day_type' => $request->half_day_type,
        ]);

        $leave->total_days = $leave->calculateTotalDays();

        // Check for conflicts
        if ($leave->hasConflict()) {
            return back()->withErrors(['error' => 'Sudah ada pengajuan cuti pada periode tersebut.']);
        }

        // Check leave balance
        if (!$leave->hasEnoughBalance()) {
            return back()->withErrors(['error' => 'Sisa cuti tidak mencukupi.']);
        }

        $leave->save();

        // Handle file uploads
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/leaves'), $filename);
                $leave->addAttachment($filename, $file->getClientOriginalName(), $file->getSize());
            }
        }

        return redirect()->route('permits.leave.index')
                        ->with('success', 'Pengajuan cuti berhasil dibuat.');
    }

    private function getLeaveBalance($userId)
    {
        $leaveTypes = LeaveType::active()->get();
        $balance = [];

        foreach ($leaveTypes as $type) {
            $used = LeaveRequest::byUser($userId)
                              ->byLeaveType($type->id)
                              ->thisYear()
                              ->approved()
                              ->sum('total_days');
            
            $balance[] = [
                'type' => $type,
                'max_days' => $type->max_days_per_year,
                'used' => $used,
                'remaining' => max(0, $type->max_days_per_year - $used),
            ];
        }

        return $balance;
    }

    // MANAGEMENT METHODS FOR HRD/ADMIN
    public function leaveManagement()
    {
        try {
            $user = Auth::user();

            // Check if user has permission to manage leaves
            $hasPermission = $user->hasAnyRole(['Admin', 'HRD', 'HR', 'Manager']);

            if (!$hasPermission) {
                abort(403, 'Unauthorized action.');
            }

            $leaves = LeaveRequest::with(['user', 'leaveType', 'approvedBy'])
                                 ->latest()
                                 ->paginate(15);

            $leaveTypes = LeaveType::active()->get();

            $stats = [
                'total' => LeaveRequest::count(),
                'pending' => LeaveRequest::where('status', 'pending')->count(),
                'approved' => LeaveRequest::where('status', 'approved')->count(),
                'rejected' => LeaveRequest::where('status', 'rejected')->count(),
                'total_pending' => LeaveRequest::where('status', 'pending')->count(),
                'total_approved' => LeaveRequest::where('status', 'approved')->count(),
                'total_this_month' => LeaveRequest::whereMonth('created_at', now()->month)->count(),
                'total_days_this_month' => LeaveRequest::whereMonth('created_at', now()->month)
                                                      ->where('status', 'approved')
                                                      ->sum('total_days'),
            ];

            return view('permits.leave.management-simple', compact('leaves', 'stats', 'leaveTypes'));
        } catch (\Exception $e) {
            \Log::error('Error in leaveManagement: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function overtimeManagement()
    {
        $user = Auth::user();

        // Check if user has permission to manage overtime
        if (!$user->hasAnyRole(['Admin', 'HRD', 'HR', 'Manager'])) {
            abort(403, 'Unauthorized action.');
        }

        $overtimes = OvertimeRequest::with(['user.employee', 'approvedBy'])
                                   ->latest()
                                   ->paginate(15);

        $stats = [
            'total' => OvertimeRequest::count(),
            'pending' => OvertimeRequest::where('status', 'pending')->count(),
            'approved' => OvertimeRequest::where('status', 'approved')->count(),
            'rejected' => OvertimeRequest::where('status', 'rejected')->count(),
            'total_hours' => OvertimeRequest::where('status', 'approved')->sum('actual_hours'),
            'total_amount' => OvertimeRequest::where('status', 'approved')->sum('overtime_amount'),
        ];

        return view('permits.overtime.management', compact('overtimes', 'stats'));
    }

    public function leaveShow(LeaveRequest $leave)
    {
        // Check if user owns this leave request or has permission to view all
        $user = Auth::user();
        if ($leave->user_id !== $user->id && !$user->hasAnyRole(['Admin', 'HRD', 'HR', 'Manager'])) {
            abort(403, 'Unauthorized');
        }
        return view('permits.leave.show', compact('leave'));
    }

    public function leaveApprove(LeaveRequest $leave)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Admin', 'HRD', 'HR', 'Manager'])) {
            abort(403, 'Unauthorized action.');
        }

        // User cannot approve their own leave request
        if ($leave->user_id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menyetujui pengajuan cuti Anda sendiri.');
        }

        if ($leave->status !== 'pending') {
            return back()->with('error', 'Pengajuan cuti sudah diproses sebelumnya.');
        }

        $leave->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Pengajuan cuti berhasil disetujui.');
    }

    public function leaveReject(Request $request, LeaveRequest $leave)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Admin', 'HRD', 'HR', 'Manager'])) {
            abort(403, 'Unauthorized action.');
        }

        // User cannot reject their own leave request
        if ($leave->user_id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menolak pengajuan cuti Anda sendiri.');
        }

        if ($leave->status !== 'pending') {
            return back()->with('error', 'Pengajuan cuti sudah diproses sebelumnya.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $leave->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return back()->with('success', 'Pengajuan cuti berhasil ditolak.');
    }

    public function leaveBulkApprove(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Admin', 'HRD', 'HR', 'Manager'])) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'leave_ids' => 'required|array',
            'leave_ids.*' => 'exists:leave_requests,id'
        ]);

        $leaveIds = $request->leave_ids;
        $leaves = LeaveRequest::whereIn('id', $leaveIds)
                             ->where('status', 'pending')
                             ->get();

        if ($leaves->isEmpty()) {
            return back()->with('error', 'Tidak ada pengajuan cuti yang dapat disetujui.');
        }

        $approvedCount = 0;
        foreach ($leaves as $leave) {
            // User cannot approve their own leave request
            if ($leave->user_id === auth()->id()) {
                continue;
            }

            $leave->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            $approvedCount++;
        }

        return back()->with('success', "Berhasil menyetujui {$approvedCount} pengajuan cuti.");
    }

    public function leaveSlip(LeaveRequest $leave)
    {
        $user = Auth::user();

        // Check if user owns this leave request or has permission to view all
        if ($leave->user_id !== $user->id && !$user->hasAnyRole(['Admin', 'HRD', 'HR', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        // Only approved leave can have slips
        if ($leave->status !== 'approved') {
            return back()->with('error', 'Slip hanya dapat dicetak untuk cuti yang sudah disetujui.');
        }

        return view('permits.leave.slip', compact('leave'));
    }
}
