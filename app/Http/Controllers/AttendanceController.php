<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\AttendanceRule;
use App\Models\User;
use App\Models\Schedule;
use App\Models\Office;
use App\Models\Shift;
use App\Services\GeofencingService;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    protected $geofencingService;

    public function __construct(GeofencingService $geofencingService)
    {
        $this->geofencingService = $geofencingService;
    }
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

        // Check if user has active schedule for today
        $todaySchedule = $user->getTodaySchedule();
        if (!$todaySchedule) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki jadwal kerja untuk hari ini. Silakan hubungi HR untuk mengatur jadwal.'
            ]);
        }

        // Enhanced location validation with geofencing
        if (!$request->filled('latitude') || !$request->filled('longitude')) {
            return response()->json([
                'success' => false,
                'message' => 'Lokasi GPS diperlukan untuk absensi.'
            ]);
        }

        $locationValidation = $this->geofencingService->validateScheduleLocation(
            $request->latitude,
            $request->longitude,
            $user->id,
            today()
        );

        if (!$locationValidation['valid']) {
            return response()->json([
                'success' => false,
                'message' => $locationValidation['message'],
                'location_data' => [
                    'distance' => $locationValidation['distance'] ?? null,
                    'required_radius' => $locationValidation['required_radius'] ?? null,
                    'office' => $locationValidation['office'] ?? null
                ]
            ]);
        }

        // Track location for audit
        $this->geofencingService->trackLocationHistory(
            $user->id,
            $request->latitude,
            $request->longitude,
            'clock_in'
        );

        $clockInTime = now();

        // Use shift-specific timing to determine status
        $shift = $todaySchedule->shift;
        $attendanceStatus = $shift->calculateAttendanceStatus($clockInTime);

        // Calculate minutes based on status
        $lateMinutes = 0;
        $earlyMinutes = 0;
        $status = 'present'; // Default status

        switch ($attendanceStatus) {
            case 'early':
                $earlyMinutes = $shift->calculateEarlyMinutes($clockInTime);
                $status = 'early';
                break;
            case 'on_time':
                $status = 'present';
                break;
            case 'late':
                $lateMinutes = $shift->calculateLateMinutes($clockInTime);
                $status = 'late';
                break;
        }

        $attendanceData = [
            'user_id' => $user->id,
            'date' => $today,
            'clock_in' => $clockInTime,
            'late_minutes' => $lateMinutes,
            'early_minutes' => $earlyMinutes,
            'status' => $status,
            'clock_in_ip' => $request->ip(),
            'office_id' => $todaySchedule->office_id,
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

        // Generate status message based on attendance status
        $statusMessage = '';
        switch ($status) {
            case 'early':
                $statusMessage = "Clock in berhasil - Terlalu dini {$earlyMinutes} menit";
                break;
            case 'late':
                $statusMessage = "Clock in berhasil - Terlambat {$lateMinutes} menit";
                break;
            case 'present':
            default:
                $statusMessage = "Clock in berhasil - Tepat waktu";
                break;
        }

        return response()->json([
            'success' => true,
            'message' => $statusMessage,
            'data' => [
                'attendance' => $attendance,
                'schedule' => $todaySchedule,
                'work_type' => $todaySchedule->work_type,
                'status' => $status,
                'late_minutes' => $lateMinutes,
                'early_minutes' => $earlyMinutes
            ]
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

        // Get today's schedule to use shift timing
        $todaySchedule = $user->getTodaySchedule();
        if (!$todaySchedule) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal kerja tidak ditemukan.'
            ]);
        }

        // Validate location for WFO clock out
        if ($todaySchedule->isWFO()) {
            if ($request->filled('latitude') && $request->filled('longitude')) {
                if (!$todaySchedule->canClockInAtLocation($request->latitude, $request->longitude)) {
                    $distance = $todaySchedule->getDistanceFromOffice($request->latitude, $request->longitude);
                    return response()->json([
                        'success' => false,
                        'message' => "Anda berada di luar radius kantor untuk clock out. Jarak Anda: " . round($distance) . " meter dari kantor " . $todaySchedule->office->name . "."
                    ]);
                }
            }
        }

        $clockOutTime = now();
        $shift = $todaySchedule->shift;

        $earlyLeaveMinutes = $shift->calculateEarlyLeaveMinutes($clockOutTime);
        $overtimeMinutes = $shift->calculateOvertimeMinutes($clockOutTime);

        // Calculate total work minutes
        $clockIn = Carbon::parse($attendance->clock_in);
        $clockOut = Carbon::parse($clockOutTime);
        $totalWorkMinutes = $clockOut->diffInMinutes($clockIn);

        // Determine final status based on early leave and existing status
        $finalStatus = $attendance->status;
        if ($earlyLeaveMinutes > 0) {
            $finalStatus = 'early_leave';
        }

        $updateData = [
            'clock_out' => $clockOutTime,
            'early_leave_minutes' => $earlyLeaveMinutes,
            'overtime_minutes' => $overtimeMinutes,
            'total_work_minutes' => $totalWorkMinutes,
            'status' => $finalStatus,
            'clock_out_ip' => $request->ip(),
        ];

        // Add location if provided
        if ($request->filled('latitude') && $request->filled('longitude')) {
            $updateData['clock_out_lat'] = $request->latitude;
            $updateData['clock_out_lng'] = $request->longitude;
        }

        $attendance->update($updateData);

        // Generate status message
        $statusMessage = 'Clock out berhasil';
        if ($overtimeMinutes > 0) {
            $overtimeHours = floor($overtimeMinutes / 60);
            $overtimeRemainingMinutes = $overtimeMinutes % 60;
            if ($overtimeHours > 0) {
                $statusMessage .= " - Lembur {$overtimeHours} jam";
                if ($overtimeRemainingMinutes > 0) {
                    $statusMessage .= " {$overtimeRemainingMinutes} menit";
                }
            } else {
                $statusMessage .= " - Lembur {$overtimeRemainingMinutes} menit";
            }
        } elseif ($earlyLeaveMinutes > 0) {
            $statusMessage .= " - Pulang awal {$earlyLeaveMinutes} menit";
        } else {
            $statusMessage .= " - Tepat waktu";
        }

        return response()->json([
            'success' => true,
            'message' => $statusMessage,
            'data' => [
                'attendance' => $attendance->fresh(),
                'schedule' => $todaySchedule,
                'work_type' => $todaySchedule->work_type,
                'status' => $finalStatus,
                'overtime_minutes' => $overtimeMinutes,
                'early_leave_minutes' => $earlyLeaveMinutes,
                'total_work_minutes' => $totalWorkMinutes
            ]
        ]);
    }



    public function getTodayAttendance()
    {
        try {
            $user = Auth::user();
            $attendance = Attendance::where('user_id', $user->id)
                                    ->where('date', today())
                                    ->first();

            $todaySchedule = $user->getTodaySchedule();

            // Debug logging
            \Log::info('Today schedule data:', [
                'user_id' => $user->id,
                'schedule' => $todaySchedule ? $todaySchedule->toArray() : null,
                'shift' => $todaySchedule && $todaySchedule->shift ? $todaySchedule->shift->toArray() : null
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'attendance' => $attendance,
                    'schedule' => $todaySchedule,
                    'can_clock_in' => $user->canClockInToday(),
                    'work_type' => $user->getTodayWorkType(),
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading today attendance: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data absensi. Silakan coba lagi.',
                'data' => [
                    'attendance' => null,
                    'schedule' => null,
                    'can_clock_in' => false,
                    'work_type' => null,
                ]
            ], 500);
        }
    }

    public function getRecentAttendance()
    {
        try {
            $user = Auth::user();
            $recentAttendances = Attendance::where('user_id', $user->id)
                                          ->with(['user'])
                                          ->orderBy('date', 'desc')
                                          ->limit(7)
                                          ->get();

            return response()->json([
                'success' => true,
                'data' => $recentAttendances
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading recent attendance: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat riwayat absensi.',
                'data' => []
            ], 500);
        }
    }

    public function attempt(Request $request)
    {
        $user = Auth::user();
        $today = today();

        // Check if already has attendance today
        $attendance = Attendance::where('user_id', $user->id)
                                ->where('date', $today)
                                ->first();

        if (!$attendance || !$attendance->clock_in) {
            // Perform clock in
            return $this->clockIn($request);
        } elseif (!$attendance->clock_out) {
            // Perform clock out
            return $this->clockOut($request);
        } else {
            // Already completed
            return response()->json([
                'success' => false,
                'message' => 'Absensi hari ini sudah lengkap.'
            ]);
        }
    }

    /**
     * Validate current location for attendance
     */
    public function validateCurrentLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $user = Auth::user();
        $todaySchedule = $user->getTodaySchedule();

        if (!$todaySchedule) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada jadwal kerja untuk hari ini',
                'data' => [
                    'valid' => false,
                    'work_type' => null,
                    'office' => null,
                    'distance' => null,
                    'required_radius' => null
                ]
            ]);
        }

        // For WFA, location is always valid
        if ($todaySchedule->work_type === 'WFA') {
            return response()->json([
                'success' => true,
                'message' => 'Work From Anywhere - lokasi valid',
                'data' => [
                    'valid' => true,
                    'work_type' => 'WFA',
                    'office' => null,
                    'distance' => null,
                    'required_radius' => null
                ]
            ]);
        }

        // For WFO, validate against office location
        $locationValidation = $this->geofencingService->validateScheduleLocation(
            $request->latitude,
            $request->longitude,
            $user->id,
            today()
        );

        return response()->json([
            'success' => true,
            'message' => $locationValidation['message'],
            'data' => [
                'valid' => $locationValidation['valid'],
                'work_type' => $locationValidation['work_type'] ?? 'WFO',
                'office' => $locationValidation['office'] ?? null,
                'distance' => $locationValidation['distance'] ?? null,
                'required_radius' => $locationValidation['required_radius'] ?? null
            ]
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
            'late_minutes' => 'nullable|integer|min:0',
            'early_minutes' => 'nullable|integer|min:0',
            'early_leave_minutes' => 'nullable|integer|min:0',
            'overtime_minutes' => 'nullable|integer|min:0',
            'status' => 'required|in:present,absent,late,early,half_day,sick,leave,holiday',
        ]);

        $data = $request->only([
            'clock_in', 'clock_out',
            'late_minutes', 'early_minutes', 'early_leave_minutes', 'overtime_minutes',
            'status'
        ]);

        // Recalculate work minutes if times are provided
        if ($data['clock_in'] && $data['clock_out']) {
            $clockIn = Carbon::parse($data['clock_in']);
            $clockOut = Carbon::parse($data['clock_out']);
            $totalWorkMinutes = $clockOut->diffInMinutes($clockIn);

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
