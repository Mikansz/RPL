<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DayExchange;
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
            'pending_day_exchanges' => DayExchange::byUser($user->id)->pending()->count(),
            'pending_overtime' => OvertimeRequest::byUser($user->id)->pending()->count(),
            'pending_leaves' => LeaveRequest::byUser($user->id)->pending()->count(),
            
            'recent_day_exchanges' => DayExchange::byUser($user->id)->latest()->take(5)->get(),
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

    // TUKAR HARI (Day Exchange)
    public function dayExchangeIndex()
    {
        $user = Auth::user();
        $exchanges = DayExchange::byUser($user->id)
                               ->with('approvedBy')
                               ->latest()
                               ->paginate(10);

        return view('permits.day-exchange.index', compact('exchanges'));
    }

    public function dayExchangeCreate()
    {
        return view('permits.day-exchange.create');
    }

    public function dayExchangeStore(Request $request)
    {
        $request->validate([
            'original_work_date' => 'required|date|after:today',
            'replacement_date' => 'required|date|after:today',
            'reason' => 'required|string|max:500',
        ]);

        $user = Auth::user();

        // Validasi hari kerja dan weekend
        $originalDay = Carbon::parse($request->original_work_date)->dayOfWeek;
        $replacementDay = Carbon::parse($request->replacement_date)->dayOfWeek;

        if ($originalDay == Carbon::SATURDAY || $originalDay == Carbon::SUNDAY) {
            return back()->withErrors(['original_work_date' => 'Tanggal asli harus hari kerja (Senin-Jumat).']);
        }

        if ($replacementDay != Carbon::SATURDAY && $replacementDay != Carbon::SUNDAY) {
            return back()->withErrors(['replacement_date' => 'Tanggal pengganti harus hari weekend (Sabtu-Minggu).']);
        }

        $exchange = new DayExchange([
            'user_id' => $user->id,
            'original_work_date' => $request->original_work_date,
            'replacement_date' => $request->replacement_date,
            'reason' => $request->reason,
        ]);

        // Check for conflicts
        if ($exchange->hasConflict()) {
            return back()->withErrors(['error' => 'Sudah ada pengajuan tukar hari pada tanggal tersebut.']);
        }

        $exchange->save();

        return redirect()->route('permits.day-exchange.index')
                        ->with('success', 'Pengajuan tukar hari berhasil dibuat.');
    }

    public function dayExchangeShow(DayExchange $dayExchange)
    {
        $this->authorize('view', $dayExchange);
        return view('permits.day-exchange.show', compact('dayExchange'));
    }

    public function dayExchangeEdit(DayExchange $dayExchange)
    {
        $this->authorize('update', $dayExchange);
        
        if (!$dayExchange->canBeEdited()) {
            return back()->with('error', 'Pengajuan tidak dapat diubah.');
        }

        return view('permits.day-exchange.edit', compact('dayExchange'));
    }

    public function dayExchangeUpdate(Request $request, DayExchange $dayExchange)
    {
        $this->authorize('update', $dayExchange);

        if (!$dayExchange->canBeEdited()) {
            return back()->with('error', 'Pengajuan tidak dapat diubah.');
        }

        $request->validate([
            'original_work_date' => 'required|date|after:today',
            'replacement_date' => 'required|date|after:today',
            'reason' => 'required|string|max:500',
        ]);

        $dayExchange->update($request->only(['original_work_date', 'replacement_date', 'reason']));

        return redirect()->route('permits.day-exchange.index')
                        ->with('success', 'Pengajuan tukar hari berhasil diperbarui.');
    }

    public function dayExchangeDestroy(DayExchange $dayExchange)
    {
        $this->authorize('delete', $dayExchange);

        if (!$dayExchange->canBeEdited()) {
            return back()->with('error', 'Pengajuan tidak dapat dihapus.');
        }

        $dayExchange->delete();

        return redirect()->route('permits.day-exchange.index')
                        ->with('success', 'Pengajuan tukar hari berhasil dihapus.');
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
        $this->authorize('view', $overtime);
        return view('permits.overtime.show', compact('overtime'));
    }

    public function overtimeEdit(OvertimeRequest $overtime)
    {
        $this->authorize('update', $overtime);
        
        if (!$overtime->canBeEdited()) {
            return back()->with('error', 'Pengajuan tidak dapat diubah.');
        }

        return view('permits.overtime.edit', compact('overtime'));
    }

    public function overtimeUpdate(Request $request, OvertimeRequest $overtime)
    {
        $this->authorize('update', $overtime);

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
        $this->authorize('delete', $overtime);

        if (!$overtime->canBeEdited()) {
            return back()->with('error', 'Pengajuan tidak dapat dihapus.');
        }

        $overtime->delete();

        return redirect()->route('permits.overtime.index')
                        ->with('success', 'Pengajuan lembur berhasil dihapus.');
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

        return view('permits.leave.index', compact('leaves', 'leaveBalance'));
    }

    public function leaveCreate()
    {
        $leaveTypes = LeaveType::active()->get();
        return view('permits.leave.create', compact('leaveTypes'));
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
}
