<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\AttendanceRule;
use App\Models\User;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Attendance::with('user');

        // Filter based on user role
        if ($user->hasRole('karyawan')) {
            $query->where('user_id', $user->id);
        }

        // Apply filters
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        if ($request->filled('user_id') && !$user->hasRole('karyawan')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $attendances = $query->orderBy('date', 'desc')
                           ->orderBy('clock_in', 'desc')
                           ->paginate(20);

        $users = $user->hasRole('karyawan') ? collect() : User::whereHas('employee')->get();

        return view('attendance.index', compact('attendances', 'users'));
    }

    public function clockIn(Request $request)
    {
        $user = Auth::user();
        $today = today();

        // Check if already clocked in today
        $attendance = Attendance::where('user_id', $user->id)
                                ->where('date', $today)
                                ->first();

        if ($attendance && $attendance->clock_in) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan clock in hari ini.'
            ]);
        }

        $clockInTime = now();
        $rule = AttendanceRule::where('is_default', true)->first();

        if (!$rule) {
            return response()->json([
                'success' => false,
                'message' => 'Aturan absensi tidak ditemukan.'
            ]);
        }

        $lateMinutes = $rule->calculateLateMinutes($clockInTime);
        $status = $lateMinutes > 0 ? 'late' : 'present';

        $attendanceData = [
            'user_id' => $user->id,
            'date' => $today,
            'clock_in' => $clockInTime,
            'late_minutes' => $lateMinutes,
            'status' => $status,
            'clock_in_ip' => $request->ip(),
        ];

        // Add location if provided
        if ($request->filled('latitude') && $request->filled('longitude')) {
            $attendanceData['clock_in_lat'] = $request->latitude;
            $attendanceData['clock_in_lng'] = $request->longitude;
        }

        if ($attendance) {
            $attendance->update($attendanceData);
        } else {
            $attendance = Attendance::create($attendanceData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Clock in berhasil.',
            'data' => $attendance
        ]);
    }

    public function clockOut(Request $request)
    {
        $user = Auth::user();
        $today = today();

        $attendance = Attendance::where('user_id', $user->id)
                                ->where('date', $today)
                                ->first();

        if (!$attendance || !$attendance->clock_in) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum melakukan clock in hari ini.'
            ]);
        }

        if ($attendance->clock_out) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan clock out hari ini.'
            ]);
        }

        $clockOutTime = now();
        $rule = AttendanceRule::where('is_default', true)->first();

        $earlyLeaveMinutes = $rule->calculateEarlyLeaveMinutes($clockOutTime);
        $overtimeMinutes = $rule->calculateOvertimeMinutes($clockOutTime);

        // Calculate total work minutes
        $clockIn = Carbon::parse($attendance->clock_in);
        $clockOut = Carbon::parse($clockOutTime);
        $totalWorkMinutes = $clockOut->diffInMinutes($clockIn);

        // Subtract break time if any
        if ($attendance->break_start && $attendance->break_end) {
            $breakStart = Carbon::parse($attendance->break_start);
            $breakEnd = Carbon::parse($attendance->break_end);
            $totalWorkMinutes -= $breakEnd->diffInMinutes($breakStart);
        }

        $updateData = [
            'clock_out' => $clockOutTime,
            'early_leave_minutes' => $earlyLeaveMinutes,
            'overtime_minutes' => $overtimeMinutes,
            'total_work_minutes' => $totalWorkMinutes,
            'clock_out_ip' => $request->ip(),
        ];

        // Add location if provided
        if ($request->filled('latitude') && $request->filled('longitude')) {
            $updateData['clock_out_lat'] = $request->latitude;
            $updateData['clock_out_lng'] = $request->longitude;
        }

        $attendance->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Clock out berhasil.',
            'data' => $attendance
        ]);
    }

    public function startBreak(Request $request)
    {
        $user = Auth::user();
        $today = today();

        $attendance = Attendance::where('user_id', $user->id)
                                ->where('date', $today)
                                ->first();

        if (!$attendance || !$attendance->clock_in) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum melakukan clock in hari ini.'
            ]);
        }

        if ($attendance->break_start) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah memulai istirahat hari ini.'
            ]);
        }

        $attendance->update([
            'break_start' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Istirahat dimulai.',
            'data' => $attendance
        ]);
    }

    public function endBreak(Request $request)
    {
        $user = Auth::user();
        $today = today();

        $attendance = Attendance::where('user_id', $user->id)
                                ->where('date', $today)
                                ->first();

        if (!$attendance || !$attendance->break_start) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum memulai istirahat hari ini.'
            ]);
        }

        if ($attendance->break_end) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah mengakhiri istirahat hari ini.'
            ]);
        }

        $breakEnd = now();
        $breakStart = Carbon::parse($attendance->break_start);
        $totalBreakMinutes = $breakEnd->diffInMinutes($breakStart);

        $attendance->update([
            'break_end' => $breakEnd,
            'total_break_minutes' => $totalBreakMinutes
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Istirahat selesai.',
            'data' => $attendance
        ]);
    }

    public function getTodayAttendance()
    {
        $user = Auth::user();
        $attendance = Attendance::where('user_id', $user->id)
                                ->where('date', today())
                                ->first();

        return response()->json([
            'success' => true,
            'data' => $attendance
        ]);
    }

    public function edit($id)
    {
        $attendance = Attendance::with('user')->findOrFail($id);
        
        // Check permission
        $user = Auth::user();
        if (!$user->hasPermission('attendance.edit')) {
            abort(403);
        }

        return view('attendance.edit', compact('attendance'));
    }

    public function update(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        
        $request->validate([
            'clock_in' => 'nullable|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i',
            'break_start' => 'nullable|date_format:H:i',
            'break_end' => 'nullable|date_format:H:i',
            'status' => 'required|in:present,absent,late,half_day,sick,leave,holiday',
            'notes' => 'nullable|string',
        ]);

        $data = $request->only([
            'clock_in', 'clock_out', 'break_start', 'break_end', 
            'status', 'notes'
        ]);

        // Recalculate work minutes if times are provided
        if ($data['clock_in'] && $data['clock_out']) {
            $clockIn = Carbon::parse($data['clock_in']);
            $clockOut = Carbon::parse($data['clock_out']);
            $totalWorkMinutes = $clockOut->diffInMinutes($clockIn);

            if ($data['break_start'] && $data['break_end']) {
                $breakStart = Carbon::parse($data['break_start']);
                $breakEnd = Carbon::parse($data['break_end']);
                $totalWorkMinutes -= $breakEnd->diffInMinutes($breakStart);
                $data['total_break_minutes'] = $breakEnd->diffInMinutes($breakStart);
            }

            $data['total_work_minutes'] = $totalWorkMinutes;

            // Recalculate late and overtime
            $rule = AttendanceRule::where('is_default', true)->first();
            if ($rule) {
                $data['late_minutes'] = $rule->calculateLateMinutes($data['clock_in']);
                $data['overtime_minutes'] = $rule->calculateOvertimeMinutes($data['clock_out']);
            }
        }

        $attendance->update($data);

        return redirect()->route('attendance.index')
                        ->with('success', 'Data absensi berhasil diperbarui.');
    }

    public function report(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        $departmentId = $request->get('department_id');

        $query = Attendance::with(['user.employee.department', 'user.employee.position'])
                          ->whereBetween('date', [$startDate, $endDate]);

        if ($departmentId) {
            $query->whereHas('user.employee', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        $attendances = $query->orderBy('date', 'desc')->get();

        $summary = [
            'total_present' => $attendances->where('status', 'present')->count(),
            'total_late' => $attendances->where('status', 'late')->count(),
            'total_absent' => $attendances->where('status', 'absent')->count(),
            'total_sick' => $attendances->where('status', 'sick')->count(),
            'total_leave' => $attendances->where('status', 'leave')->count(),
        ];

        return view('attendance.report', compact('attendances', 'summary', 'startDate', 'endDate'));
    }
}
