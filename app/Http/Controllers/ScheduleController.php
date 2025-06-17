<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Office;
use App\Models\Shift;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Schedule::with(['user', 'shift', 'office', 'createdBy', 'approvedBy']);

        // Filter based on user role
        if ($user->hasRole('karyawan')) {
            $query->where('user_id', $user->id);
        }

        // Apply filters
        if ($request->filled('date_from')) {
            $query->where('schedule_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('schedule_date', '<=', $request->date_to);
        }

        if ($request->filled('user_id') && !$user->hasRole('karyawan')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('work_type')) {
            $query->where('work_type', $request->work_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $schedules = $query->orderBy('schedule_date', 'desc')
                          ->paginate(20);

        $users = $user->hasRole('karyawan') ? collect() : User::whereHas('employee')->get();
        $offices = Office::active()->get();
        $shifts = Shift::active()->get();

        return view('schedules.index', compact('schedules', 'users', 'offices', 'shifts'));
    }

    public function create()
    {
        $users = User::whereHas('employee')->get();
        $offices = Office::active()->get();
        $shifts = Shift::active()->get();

        return view('schedules.create', compact('users', 'offices', 'shifts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'shift_id' => 'required|exists:shifts,id',
            'office_id' => 'nullable|exists:offices,id',
            'schedule_date' => 'required|date|after_or_equal:today',
            'work_type' => 'required|in:WFO,WFA',
            'notes' => 'nullable|string|max:500',
        ]);

        // Validate office_id for WFO
        if ($request->work_type === 'WFO' && !$request->office_id) {
            return back()->withErrors(['office_id' => 'Office is required for WFO schedule.']);
        }

        // Check if schedule already exists for this user and date
        $existingSchedule = Schedule::where('user_id', $request->user_id)
                                  ->where('schedule_date', $request->schedule_date)
                                  ->first();

        if ($existingSchedule) {
            return back()->withErrors(['schedule_date' => 'Schedule already exists for this user on this date.']);
        }

        $schedule = Schedule::create([
            'user_id' => $request->user_id,
            'shift_id' => $request->shift_id,
            'office_id' => $request->work_type === 'WFO' ? $request->office_id : null,
            'schedule_date' => $request->schedule_date,
            'work_type' => $request->work_type,
            'status' => 'approved',
            'notes' => $request->notes,
            'created_by' => Auth::id(),
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('schedules.index')
                        ->with('success', 'Schedule created successfully.');
    }

    public function show($id)
    {
        $schedule = Schedule::with(['user', 'shift', 'office', 'createdBy', 'approvedBy'])
                           ->findOrFail($id);

        return view('schedules.show', compact('schedule'));
    }

    public function edit($id)
    {
        $schedule = Schedule::findOrFail($id);
        $users = User::whereHas('employee')->get();
        $offices = Office::active()->get();
        $shifts = Shift::active()->get();

        return view('schedules.edit', compact('schedule', 'users', 'offices', 'shifts'));
    }

    public function update(Request $request, $id)
    {
        $schedule = Schedule::findOrFail($id);
        $user = Auth::user();

        // Check permission based on user role
        if ($user->hasRole('karyawan') && $schedule->user_id !== $user->id) {
            abort(403, 'Anda tidak dapat mengedit jadwal orang lain.');
        }

        if ($user->hasRole('manager')) {
            // Manager can only edit schedules of their supervised employees
            $supervisedEmployeeIds = $user->supervisedEmployees()->pluck('user_id')->toArray();
            if (!in_array($schedule->user_id, $supervisedEmployeeIds) && $schedule->user_id !== $user->id) {
                abort(403, 'Anda hanya dapat mengedit jadwal karyawan yang Anda supervisi.');
            }
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'shift_id' => 'required|exists:shifts,id',
            'office_id' => 'nullable|exists:offices,id',
            'schedule_date' => 'required|date',
            'work_type' => 'required|in:WFO,WFA',
            'status' => 'required|in:scheduled,approved,cancelled',
            'notes' => 'nullable|string|max:500',
        ]);

        // Validate office_id for WFO
        if ($request->work_type === 'WFO' && !$request->office_id) {
            return back()->withErrors(['office_id' => 'Office is required for WFO schedule.']);
        }

        // Check if schedule already exists for this user and date (excluding current)
        $existingSchedule = Schedule::where('user_id', $request->user_id)
                                  ->where('schedule_date', $request->schedule_date)
                                  ->where('id', '!=', $id)
                                  ->first();

        if ($existingSchedule) {
            return back()->withErrors(['schedule_date' => 'Schedule already exists for this user on this date.']);
        }

        // Prevent editing past schedules unless admin/hr
        if ($request->schedule_date < today() && !$user->hasAnyRole(['admin', 'hr', 'hrd'])) {
            return back()->withErrors(['schedule_date' => 'Cannot edit past schedules.']);
        }

        $updateData = [
            'user_id' => $request->user_id,
            'shift_id' => $request->shift_id,
            'office_id' => $request->work_type === 'WFO' ? $request->office_id : null,
            'schedule_date' => $request->schedule_date,
            'work_type' => $request->work_type,
            'status' => $request->status,
            'notes' => $request->notes,
        ];

        // Track who made the update
        if ($schedule->status !== $request->status && $request->status === 'approved') {
            $updateData['approved_by'] = $user->id;
            $updateData['approved_at'] = now();
        }

        $schedule->update($updateData);

        return redirect()->route('schedules.index')
                        ->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function bulkEdit(Request $request)
    {
        $scheduleIds = $request->input('schedule_ids', []);

        if (empty($scheduleIds)) {
            return back()->with('error', 'Pilih minimal satu jadwal untuk diedit.');
        }

        $schedules = Schedule::whereIn('id', $scheduleIds)->get();
        $users = User::whereHas('employee')->get();
        $offices = Office::active()->get();
        $shifts = Shift::active()->get();

        return view('schedules.bulk-edit', compact('schedules', 'users', 'offices', 'shifts'));
    }

    public function bulkUpdate(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'schedule_ids' => 'required|array|min:1',
            'schedule_ids.*' => 'exists:schedules,id',
            'bulk_action' => 'required|in:update_shift,update_work_type,update_status,update_office',
            'shift_id' => 'nullable|exists:shifts,id',
            'work_type' => 'nullable|in:WFO,WFA',
            'status' => 'nullable|in:scheduled,approved,cancelled',
            'office_id' => 'nullable|exists:offices,id',
        ]);

        $scheduleIds = $request->input('schedule_ids');
        $bulkAction = $request->input('bulk_action');
        $updatedCount = 0;

        foreach ($scheduleIds as $scheduleId) {
            $schedule = Schedule::find($scheduleId);
            if (!$schedule) continue;

            $updateData = [];

            switch ($bulkAction) {
                case 'update_shift':
                    if ($request->shift_id) {
                        $updateData['shift_id'] = $request->shift_id;
                    }
                    break;

                case 'update_work_type':
                    if ($request->work_type) {
                        $updateData['work_type'] = $request->work_type;
                        if ($request->work_type === 'WFA') {
                            $updateData['office_id'] = null;
                        } elseif ($request->work_type === 'WFO' && $request->office_id) {
                            $updateData['office_id'] = $request->office_id;
                        }
                    }
                    break;

                case 'update_status':
                    if ($request->status) {
                        $updateData['status'] = $request->status;
                        if ($request->status === 'approved') {
                            $updateData['approved_by'] = $user->id;
                            $updateData['approved_at'] = now();
                        }
                    }
                    break;

                case 'update_office':
                    if ($request->office_id) {
                        $updateData['office_id'] = $request->office_id;
                        $updateData['work_type'] = 'WFO';
                    }
                    break;
            }

            if (!empty($updateData)) {
                $schedule->update($updateData);
                $updatedCount++;
            }
        }

        return redirect()->route('schedules.index')
                        ->with('success', "Berhasil memperbarui {$updatedCount} jadwal.");
    }

    public function destroy($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->delete();

        return redirect()->route('schedules.index')
                        ->with('success', 'Schedule deleted successfully.');
    }

    public function cancel($id)
    {
        $schedule = Schedule::findOrFail($id);

        $schedule->update([
            'status' => 'cancelled',
        ]);

        return redirect()->back()
                        ->with('success', 'Schedule cancelled successfully.');
    }

    public function calendar(Request $request)
    {
        $user = Auth::user();
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $query = Schedule::with(['user', 'shift', 'office'])
                        ->whereBetween('schedule_date', [$startDate, $endDate]);

        if ($user->hasRole('karyawan')) {
            $query->where('user_id', $user->id);
        }

        $schedules = $query->get();

        return view('schedules.calendar', compact('schedules', 'month', 'year'));
    }
}
