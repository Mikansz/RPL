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
        // Check if user owns this overtime request or has management permission
        $user = auth()->user();
        $canView = $overtime->user_id === $user->id ||
                   $user->hasAnyRole(['Admin', 'HRD', 'HR', 'Manager']) ||
                   $user->hasPermission('overtime.view_all');

        if (!$canView) {
            abort(403, 'Unauthorized');
        }
        return view('permits.overtime.show', compact('overtime'));
    }

    public function overtimeEdit(OvertimeRequest $overtime)
    {
        // Check if user owns this overtime request or has management permission
        $user = auth()->user();
        $canEdit = $overtime->user_id === $user->id ||
                   $user->hasAnyRole(['Admin', 'HRD', 'HR']) ||
                   $user->hasPermission('overtime.edit');

        if (!$canEdit) {
            abort(403, 'Unauthorized');
        }

        // For regular users, check if it can be edited
        if ($overtime->user_id === $user->id && !$overtime->canBeEdited()) {
            return back()->with('error', 'Pengajuan tidak dapat diubah.');
        }

        return view('permits.overtime.edit', compact('overtime'));
    }

    public function overtimeUpdate(Request $request, OvertimeRequest $overtime)
    {
        // Check if user owns this overtime request or has management permission
        $user = auth()->user();
        $canEdit = $overtime->user_id === $user->id ||
                   $user->hasAnyRole(['Admin', 'HRD', 'HR']) ||
                   $user->hasPermission('overtime.edit');

        if (!$canEdit) {
            abort(403, 'Unauthorized');
        }

        // For regular users, check if it can be edited
        if ($overtime->user_id === $user->id && !$overtime->canBeEdited()) {
            return back()->with('error', 'Pengajuan tidak dapat diubah.');
        }

        // Different validation rules for HRD vs regular users
        $isHRD = $user->hasAnyRole(['Admin', 'HRD', 'HR']) || $user->hasPermission('overtime.edit');

        if ($isHRD) {
            $request->validate([
                'overtime_date' => 'required|date',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'work_description' => 'required|string|max:1000',
                'reason' => 'required|string|max:500',
                'actual_hours' => 'nullable|numeric|min:0|max:12',
                'overtime_amount' => 'nullable|numeric|min:0',
            ]);

            $overtime->update([
                'overtime_date' => $request->overtime_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'work_description' => $request->work_description,
                'reason' => $request->reason,
                'actual_hours' => $request->actual_hours,
                'overtime_amount' => $request->overtime_amount,
                'planned_hours' => $overtime->calculatePlannedHours(),
            ]);
        } else {
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
        }

        $redirectRoute = $isHRD ? 'permits.overtime.management' : 'permits.overtime.index';
        return redirect()->route($redirectRoute)
                        ->with('success', 'Pengajuan lembur berhasil diperbarui.');
    }

    public function overtimeDestroy(OvertimeRequest $overtime)
    {
        // Check if user owns this overtime request or has management permission
        $user = auth()->user();
        $canDelete = $overtime->user_id === $user->id ||
                     $user->hasAnyRole(['Admin', 'HRD', 'HR']) ||
                     $user->hasPermission('overtime.delete');

        if (!$canDelete) {
            abort(403, 'Unauthorized');
        }

        // For regular users, check if it can be edited
        if ($overtime->user_id === $user->id && !$overtime->canBeEdited()) {
            return back()->with('error', 'Pengajuan tidak dapat dihapus.');
        }

        // For HRD, only allow deletion of pending requests
        $isHRD = $user->hasAnyRole(['Admin', 'HRD', 'HR']) || $user->hasPermission('overtime.delete');
        if ($isHRD && $overtime->status !== 'pending') {
            return back()->with('error', 'Hanya pengajuan lembur yang masih pending yang dapat dihapus.');
        }

        $overtime->delete();

        $redirectRoute = $isHRD ? 'permits.overtime.management' : 'permits.overtime.index';
        return redirect()->route($redirectRoute)
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

        // Return JSON response for AJAX requests
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengajuan lembur berhasil disetujui!',
                'show_url' => route('permits.overtime.show', $overtime)
            ]);
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

    // HRD Management Functions
    public function overtimeManagement()
    {
        // Check if user has HRD/Admin permission
        $user = auth()->user();
        if (!$user->hasAnyRole(['Admin', 'HRD', 'HR']) && !$user->hasPermission('overtime.manage')) {
            abort(403, 'Unauthorized to access overtime management');
        }

        // Get statistics
        $stats = [
            'total_pending' => OvertimeRequest::where('status', 'pending')->count(),
            'total_approved' => OvertimeRequest::where('status', 'approved')->count(),
            'total_this_month' => OvertimeRequest::whereMonth('overtime_date', now()->month)->count(),
            'total_hours_this_month' => OvertimeRequest::whereMonth('overtime_date', now()->month)
                                                     ->where('status', 'approved')
                                                     ->sum('actual_hours'),
            'total_amount_this_month' => OvertimeRequest::whereMonth('overtime_date', now()->month)
                                                       ->where('status', 'approved')
                                                       ->sum('overtime_amount'),
        ];

        // Get recent overtime requests
        $recentOvertimes = OvertimeRequest::with(['user.employee', 'approvedBy'])
                                         ->latest()
                                         ->take(10)
                                         ->get();

        // Get all overtime requests with filters
        $query = OvertimeRequest::with(['user.employee', 'approvedBy']);

        if (request('status')) {
            $query->where('status', request('status'));
        }

        if (request('start_date')) {
            $query->where('overtime_date', '>=', request('start_date'));
        }

        if (request('end_date')) {
            $query->where('overtime_date', '<=', request('end_date'));
        }

        $overtimes = $query->latest()->paginate(15);

        return view('permits.overtime.management', compact('stats', 'recentOvertimes', 'overtimes'));
    }



    public function overtimeBulkApprove(Request $request)
    {
        // Check if user has permission to approve
        $user = auth()->user();
        if (!$user->hasAnyRole(['Admin', 'HRD', 'HR', 'Manager']) && !$user->hasPermission('overtime.approve')) {
            abort(403, 'Unauthorized to approve overtime requests');
        }

        $request->validate([
            'overtime_ids' => 'required|array',
            'overtime_ids.*' => 'exists:overtime_requests,id'
        ]);

        $overtimeIds = $request->overtime_ids;
        $approvedCount = 0;

        foreach ($overtimeIds as $id) {
            $overtime = OvertimeRequest::find($id);
            if ($overtime && $overtime->status === 'pending' && $overtime->user_id !== $user->id) {
                $overtime->update([
                    'status' => 'approved',
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                ]);
                $approvedCount++;
            }
        }

        return back()->with('success', "Berhasil menyetujui {$approvedCount} pengajuan lembur.");
    }

    public function overtimeReports()
    {
        // Check if user has permission to view reports
        $user = auth()->user();
        if (!$user->hasAnyRole(['Admin', 'HRD', 'HR']) && !$user->hasPermission('overtime.reports')) {
            abort(403, 'Unauthorized to view overtime reports');
        }

        $startDate = request('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = request('end_date', now()->endOfMonth()->format('Y-m-d'));

        $query = OvertimeRequest::with(['user.employee'])
                               ->whereBetween('overtime_date', [$startDate, $endDate]);

        $overtimes = $query->get();

        $summary = [
            'total_requests' => $overtimes->count(),
            'total_approved' => $overtimes->where('status', 'approved')->count(),
            'total_pending' => $overtimes->where('status', 'pending')->count(),
            'total_rejected' => $overtimes->where('status', 'rejected')->count(),
            'total_hours' => $overtimes->where('status', 'approved')->sum('actual_hours'),
            'total_amount' => $overtimes->where('status', 'approved')->sum('overtime_amount'),
        ];

        return view('permits.overtime.reports', compact('overtimes', 'summary', 'startDate', 'endDate'));
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

    public function leaveShow(LeaveRequest $leave)
    {
        // Check if user owns this leave request or has management permission
        $user = auth()->user();
        $canView = $leave->user_id === $user->id ||
                   $user->hasAnyRole(['Admin', 'HRD', 'HR', 'Manager']) ||
                   $user->hasPermission('leave.view_all');

        if (!$canView) {
            abort(403, 'Unauthorized');
        }
        return view('permits.leave.show', compact('leave'));
    }

    public function leaveEdit(LeaveRequest $leave)
    {
        // Check if user owns this leave request or has management permission
        $user = auth()->user();
        $canEdit = $leave->user_id === $user->id ||
                   $user->hasAnyRole(['Admin', 'HRD', 'HR']) ||
                   $user->hasPermission('leave.edit');

        if (!$canEdit) {
            abort(403, 'Unauthorized');
        }

        // For regular users, check if it can be edited
        if ($leave->user_id === $user->id && !$leave->canBeEdited()) {
            return back()->with('error', 'Pengajuan cuti tidak dapat diubah.');
        }

        $leaveTypes = LeaveType::active()->get();
        return view('permits.leave.edit', compact('leave', 'leaveTypes'));
    }

    public function leaveUpdate(Request $request, LeaveRequest $leave)
    {
        // Check if user owns this leave request or has management permission
        $user = auth()->user();
        $canEdit = $leave->user_id === $user->id ||
                   $user->hasAnyRole(['Admin', 'HRD', 'HR']) ||
                   $user->hasPermission('leave.edit');

        if (!$canEdit) {
            abort(403, 'Unauthorized');
        }

        // For regular users, check if it can be edited
        if ($leave->user_id === $user->id && !$leave->canBeEdited()) {
            return back()->with('error', 'Pengajuan cuti tidak dapat diubah.');
        }

        // Different validation rules for HRD vs regular users
        $isHRD = $user->hasAnyRole(['Admin', 'HRD', 'HR']) || $user->hasPermission('leave.edit');

        if ($isHRD) {
            $request->validate([
                'leave_type_id' => 'required|exists:leave_types,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'required|string|max:1000',
                'notes' => 'nullable|string|max:1000',
                'emergency_contact' => 'nullable|string|max:100',
                'emergency_phone' => 'nullable|string|max:20',
                'work_handover' => 'nullable|string|max:1000',
                'is_half_day' => 'boolean',
                'half_day_type' => 'nullable|in:morning,afternoon',
            ]);
        } else {
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
            ]);
        }

        $leave->update([
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
            'total_days' => $leave->calculateTotalDays(),
        ]);

        $redirectRoute = $isHRD ? 'permits.leave.management' : 'permits.leave.index';
        return redirect()->route($redirectRoute)
                        ->with('success', 'Pengajuan cuti berhasil diperbarui.');
    }

    public function leaveDestroy(LeaveRequest $leave)
    {
        // Check if user owns this leave request or has management permission
        $user = auth()->user();
        $canDelete = $leave->user_id === $user->id ||
                     $user->hasAnyRole(['Admin', 'HRD', 'HR']) ||
                     $user->hasPermission('leave.delete');

        if (!$canDelete) {
            abort(403, 'Unauthorized');
        }

        // For regular users, check if it can be edited
        if ($leave->user_id === $user->id && !$leave->canBeEdited()) {
            return back()->with('error', 'Pengajuan cuti tidak dapat dihapus.');
        }

        // For HRD, only allow deletion of pending requests
        $isHRD = $user->hasAnyRole(['Admin', 'HRD', 'HR']) || $user->hasPermission('leave.delete');
        if ($isHRD && $leave->status !== 'pending') {
            return back()->with('error', 'Hanya pengajuan cuti yang masih pending yang dapat dihapus.');
        }

        $leave->delete();

        $redirectRoute = $isHRD ? 'permits.leave.management' : 'permits.leave.index';
        return redirect()->route($redirectRoute)
                        ->with('success', 'Pengajuan cuti berhasil dihapus.');
    }

    // HRD Leave Management Functions
    public function leaveManagement()
    {
        // Check if user has HRD/Admin permission
        $user = auth()->user();
        if (!$user->hasAnyRole(['Admin', 'HRD', 'HR']) && !$user->hasPermission('leave.manage')) {
            abort(403, 'Unauthorized to access leave management');
        }

        // Get statistics
        $stats = [
            'total_pending' => LeaveRequest::where('status', 'pending')->count(),
            'total_approved' => LeaveRequest::where('status', 'approved')->count(),
            'total_this_month' => LeaveRequest::whereMonth('start_date', now()->month)->count(),
            'total_days_this_month' => LeaveRequest::whereMonth('start_date', now()->month)
                                                  ->where('status', 'approved')
                                                  ->sum('total_days'),
        ];

        // Get recent leave requests
        $recentLeaves = LeaveRequest::with(['user.employee', 'leaveType', 'approvedBy'])
                                   ->latest()
                                   ->take(10)
                                   ->get();

        // Get all leave requests with filters
        $query = LeaveRequest::with(['user.employee', 'leaveType', 'approvedBy']);

        if (request('status')) {
            $query->where('status', request('status'));
        }

        if (request('leave_type_id')) {
            $query->where('leave_type_id', request('leave_type_id'));
        }

        if (request('start_date')) {
            $query->where('start_date', '>=', request('start_date'));
        }

        if (request('end_date')) {
            $query->where('end_date', '<=', request('end_date'));
        }

        $leaves = $query->latest()->paginate(15);
        $leaveTypes = LeaveType::active()->get();

        return view('permits.leave.management', compact('stats', 'recentLeaves', 'leaves', 'leaveTypes'));
    }

    public function leavePending()
    {
        // Check if user has permission to approve leave
        $user = auth()->user();
        if (!$user->hasAnyRole(['Admin', 'HRD', 'HR', 'Manager']) && !$user->hasPermission('leave.approve')) {
            abort(403, 'Unauthorized to view pending leave requests');
        }

        $leaves = LeaveRequest::with(['user.employee', 'leaveType', 'approvedBy'])
                             ->where('status', 'pending')
                             ->latest()
                             ->paginate(10);

        $stats = [
            'total_pending' => LeaveRequest::where('status', 'pending')->count(),
            'total_approved_today' => LeaveRequest::where('status', 'approved')
                                                 ->whereDate('approved_at', today())
                                                 ->count(),
            'total_days_pending' => LeaveRequest::where('status', 'pending')
                                               ->sum('total_days'),
        ];

        return view('permits.leave.pending', compact('leaves', 'stats'));
    }

    public function leaveApprove(LeaveRequest $leave)
    {
        // Check if user has permission to approve
        $user = auth()->user();
        if (!$user->hasAnyRole(['Admin', 'HRD', 'HR', 'Manager']) && !$user->hasPermission('leave.approve')) {
            abort(403, 'Unauthorized to approve leave requests');
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

        \Log::info('Leave approved', ['leave_id' => $leave->id, 'user_id' => auth()->id()]);

        // Return redirect with success parameter in URL
        $currentUrl = url()->previous();
        $separator = strpos($currentUrl, '?') !== false ? '&' : '?';
        $redirectUrl = $currentUrl . $separator . 'approved=1&message=' . urlencode('Pengajuan cuti berhasil disetujui!');

        return redirect($redirectUrl);
    }

    public function leaveReject(Request $request, LeaveRequest $leave)
    {
        try {
            // Check if user has permission to approve/reject
            $user = auth()->user();
            \Log::info('Leave reject attempt', [
                'user_id' => $user->id,
                'user_roles' => $user->roles->pluck('name')->toArray(),
                'leave_id' => $leave->id,
                'leave_status' => $leave->status
            ]);

            if (!$user->hasAnyRole(['Admin', 'HRD', 'HR', 'Manager']) && !$user->hasPermission('leave.approve')) {
                \Log::warning('Unauthorized leave reject attempt', ['user_id' => $user->id]);
                abort(403, 'Unauthorized to reject leave requests');
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
                'approval_notes' => $request->rejection_reason,
            ]);

            \Log::info('Leave rejected successfully', [
                'leave_id' => $leave->id,
                'user_id' => auth()->id(),
                'reason' => $request->rejection_reason
            ]);

            // Return JSON response for AJAX requests
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pengajuan cuti berhasil ditolak!'
                ]);
            }

            return back()->with('success', 'Pengajuan cuti berhasil ditolak.');

        } catch (\Exception $e) {
            \Log::error('Leave reject error', [
                'error' => $e->getMessage(),
                'leave_id' => $leave->id,
                'user_id' => auth()->id()
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menolak pengajuan cuti.'
                ], 500);
            }

            return back()->with('error', 'Terjadi kesalahan saat menolak pengajuan cuti.');
        }
    }

    public function leaveBulkApprove(Request $request)
    {
        // Check if user has permission to approve
        $user = auth()->user();
        if (!$user->hasAnyRole(['Admin', 'HRD', 'HR', 'Manager']) && !$user->hasPermission('leave.approve')) {
            abort(403, 'Unauthorized to approve leave requests');
        }

        $request->validate([
            'leave_ids' => 'required|array',
            'leave_ids.*' => 'exists:leave_requests,id'
        ]);

        $leaveIds = $request->leave_ids;
        $approvedCount = 0;

        foreach ($leaveIds as $id) {
            $leave = LeaveRequest::find($id);
            if ($leave && $leave->status === 'pending' && $leave->user_id !== $user->id) {
                $leave->update([
                    'status' => 'approved',
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                ]);
                $approvedCount++;
            }
        }

        return back()->with('success', "Berhasil menyetujui {$approvedCount} pengajuan cuti.");
    }

    public function leaveReports()
    {
        // Check if user has permission to view reports
        $user = auth()->user();
        if (!$user->hasAnyRole(['Admin', 'HRD', 'HR']) && !$user->hasPermission('leave.reports')) {
            abort(403, 'Unauthorized to view leave reports');
        }

        $startDate = request('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = request('end_date', now()->endOfMonth()->format('Y-m-d'));

        $query = LeaveRequest::with(['user.employee', 'leaveType'])
                            ->whereBetween('start_date', [$startDate, $endDate]);

        $leaves = $query->get();

        $summary = [
            'total_requests' => $leaves->count(),
            'total_approved' => $leaves->where('status', 'approved')->count(),
            'total_pending' => $leaves->where('status', 'pending')->count(),
            'total_rejected' => $leaves->where('status', 'rejected')->count(),
            'total_days' => $leaves->where('status', 'approved')->sum('total_days'),
        ];

        // Summary by leave type
        $leaveTypeSummary = $leaves->groupBy('leave_type_id')->map(function($group) {
            return [
                'leave_type' => $group->first()->leaveType,
                'total_requests' => $group->count(),
                'approved_requests' => $group->where('status', 'approved')->count(),
                'total_days' => $group->where('status', 'approved')->sum('total_days'),
            ];
        });

        return view('permits.leave.reports', compact('leaves', 'summary', 'leaveTypeSummary', 'startDate', 'endDate'));
    }

    // Generate Leave Slip
    public function leaveSlip(LeaveRequest $leave)
    {
        // Check if user can view this leave request
        $user = auth()->user();
        $canView = $leave->user_id === $user->id ||
                   $user->hasAnyRole(['Admin', 'HRD', 'HR', 'Manager']) ||
                   $user->hasPermission('leave.view_all');

        if (!$canView) {
            abort(403, 'Unauthorized');
        }

        // Only generate slip for approved leave requests
        if ($leave->status !== 'approved') {
            return back()->with('error', 'Slip hanya dapat digenerate untuk cuti yang sudah disetujui.');
        }

        $leave->load(['user.employee.department', 'user.employee.position', 'leaveType', 'approvedBy']);

        return view('permits.leave.slip', compact('leave'));
    }

    // Generate Overtime Slip
    public function overtimeSlip(OvertimeRequest $overtime)
    {
        // Check if user can view this overtime request
        $user = auth()->user();
        $canView = $overtime->user_id === $user->id ||
                   $user->hasAnyRole(['Admin', 'HRD', 'HR', 'Manager']) ||
                   $user->hasPermission('overtime.view_all');

        if (!$canView) {
            abort(403, 'Unauthorized');
        }

        // Only generate slip for approved overtime requests
        if ($overtime->status !== 'approved') {
            return back()->with('error', 'Slip hanya dapat digenerate untuk lembur yang sudah disetujui.');
        }

        $overtime->load(['user.employee.department', 'user.employee.position', 'approvedBy']);

        return view('permits.overtime.slip', compact('overtime'));
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
}
