<?php

namespace App\Http\Controllers;

use App\Models\WorkScheduleTemplate;
use App\Models\Shift;
use App\Models\Office;
use App\Models\User;
use App\Models\EmployeeScheduleTemplate;
use App\Services\ScheduleGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkScheduleTemplateController extends Controller
{
    protected $scheduleGenerator;

    public function __construct(ScheduleGeneratorService $scheduleGenerator)
    {
        $this->scheduleGenerator = $scheduleGenerator;
    }

    public function index(Request $request)
    {
        $query = WorkScheduleTemplate::with(['shift', 'office']);
        
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }
        
        $templates = $query->orderBy('name')->paginate(20);
        
        return view('schedule-templates.index', compact('templates'));
    }

    public function create()
    {
        $shifts = Shift::active()->orderBy('name')->get();
        $offices = Office::active()->orderBy('name')->get();
        
        return view('schedule-templates.create', compact('shifts', 'offices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:work_schedule_templates,name',
            'description' => 'nullable|string|max:500',
            'shift_id' => 'required|exists:shifts,id',
            'office_id' => 'nullable|exists:offices,id',
            'work_type' => 'required|in:WFO,WFA',
            'work_days' => 'required|array|min:1',
            'work_days.*' => 'integer|between:1,7',
            'exclude_sundays' => 'boolean',
            'exclude_holidays' => 'boolean',
            'is_permanent' => 'boolean',
            'effective_from' => 'nullable|date|required_if:is_permanent,false',
            'effective_until' => 'nullable|date|after_or_equal:effective_from|prohibited_if:is_permanent,true',
            'is_active' => 'boolean',
        ]);

        // Validate office_id for WFO
        if ($request->work_type === 'WFO' && !$request->office_id) {
            return back()->withErrors(['office_id' => 'Kantor wajib dipilih untuk tipe WFO.']);
        }

        WorkScheduleTemplate::create([
            'name' => $request->name,
            'description' => $request->description,
            'shift_id' => $request->shift_id,
            'office_id' => $request->work_type === 'WFO' ? $request->office_id : null,
            'work_type' => $request->work_type,
            'work_days' => $request->work_days,
            'exclude_sundays' => $request->has('exclude_sundays'),
            'exclude_holidays' => $request->has('exclude_holidays'),
            'effective_from' => $request->has('is_permanent') ? null : $request->effective_from,
            'effective_until' => $request->has('is_permanent') ? null : $request->effective_until,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('schedule-templates.index')
                        ->with('success', 'Template jadwal kerja berhasil dibuat.');
    }

    public function show(WorkScheduleTemplate $scheduleTemplate)
    {
        $scheduleTemplate->load(['shift', 'office', 'employees']);
        
        return view('schedule-templates.show', compact('scheduleTemplate'));
    }

    public function edit(WorkScheduleTemplate $scheduleTemplate)
    {
        $shifts = Shift::active()->orderBy('name')->get();
        $offices = Office::active()->orderBy('name')->get();
        
        return view('schedule-templates.edit', compact('scheduleTemplate', 'shifts', 'offices'));
    }

    public function update(Request $request, WorkScheduleTemplate $scheduleTemplate)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:work_schedule_templates,name,' . $scheduleTemplate->id,
            'description' => 'nullable|string|max:500',
            'shift_id' => 'required|exists:shifts,id',
            'office_id' => 'nullable|exists:offices,id',
            'work_type' => 'required|in:WFO,WFA',
            'work_days' => 'required|array|min:1',
            'work_days.*' => 'integer|between:1,7',
            'exclude_sundays' => 'boolean',
            'exclude_holidays' => 'boolean',
            'is_permanent' => 'boolean',
            'effective_from' => 'nullable|date|required_if:is_permanent,false',
            'effective_until' => 'nullable|date|after_or_equal:effective_from|prohibited_if:is_permanent,true',
            'is_active' => 'boolean',
        ]);

        // Validate office_id for WFO
        if ($request->work_type === 'WFO' && !$request->office_id) {
            return back()->withErrors(['office_id' => 'Kantor wajib dipilih untuk tipe WFO.']);
        }

        $scheduleTemplate->update([
            'name' => $request->name,
            'description' => $request->description,
            'shift_id' => $request->shift_id,
            'office_id' => $request->work_type === 'WFO' ? $request->office_id : null,
            'work_type' => $request->work_type,
            'work_days' => $request->work_days,
            'exclude_sundays' => $request->has('exclude_sundays'),
            'exclude_holidays' => $request->has('exclude_holidays'),
            'effective_from' => $request->has('is_permanent') ? null : $request->effective_from,
            'effective_until' => $request->has('is_permanent') ? null : $request->effective_until,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('schedule-templates.index')
                        ->with('success', 'Template jadwal kerja berhasil diperbarui.');
    }

    public function destroy(WorkScheduleTemplate $scheduleTemplate)
    {
        // Check if template has assigned employees
        if ($scheduleTemplate->employeeAssignments()->exists()) {
            return back()->withErrors(['error' => 'Template tidak dapat dihapus karena masih ada karyawan yang menggunakan template ini.']);
        }

        $scheduleTemplate->delete();

        return redirect()->route('schedule-templates.index')
                        ->with('success', 'Template jadwal kerja berhasil dihapus.');
    }

    public function assignEmployees(WorkScheduleTemplate $scheduleTemplate)
    {
        $employees = User::whereHas('employee')->orderBy('name')->get();
        $assignedEmployees = $scheduleTemplate->employeeAssignments()
                                            ->with('user')
                                            ->active()
                                            ->get();

        return view('schedule-templates.assign-employees', compact('scheduleTemplate', 'employees', 'assignedEmployees'));
    }

    public function storeEmployeeAssignment(Request $request, WorkScheduleTemplate $scheduleTemplate)
    {
        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'assigned_from' => 'required|date',
            'assigned_until' => 'nullable|date|after_or_equal:assigned_from',
        ]);

        foreach ($request->user_ids as $userId) {
            // Check if user already has active assignment for this template
            $existingAssignment = EmployeeScheduleTemplate::where('user_id', $userId)
                                                         ->where('work_schedule_template_id', $scheduleTemplate->id)
                                                         ->active()
                                                         ->first();

            if ($existingAssignment) {
                continue; // Skip if already assigned
            }

            EmployeeScheduleTemplate::create([
                'user_id' => $userId,
                'work_schedule_template_id' => $scheduleTemplate->id,
                'assigned_from' => $request->assigned_from,
                'assigned_until' => $request->assigned_until,
                'is_active' => true,
            ]);
        }

        return redirect()->route('schedule-templates.assign-employees', $scheduleTemplate)
                        ->with('success', 'Karyawan berhasil ditugaskan ke template.');
    }

    public function removeEmployeeAssignment(WorkScheduleTemplate $scheduleTemplate, EmployeeScheduleTemplate $assignment)
    {
        $assignment->update(['is_active' => false]);

        return redirect()->route('schedule-templates.assign-employees', $scheduleTemplate)
                        ->with('success', 'Penugasan karyawan berhasil dihapus.');
    }

    public function generateSchedules(Request $request, WorkScheduleTemplate $scheduleTemplate)
    {
        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
        ]);

        try {
            // Get all users assigned to this template
            $userIds = $scheduleTemplate->employeeAssignments()
                                      ->active()
                                      ->pluck('user_id')
                                      ->toArray();

            if (empty($userIds)) {
                return back()->withErrors(['error' => 'Tidak ada karyawan yang ditugaskan ke template ini.']);
            }

            $totalGenerated = 0;
            $totalSkipped = 0;

            foreach ($userIds as $userId) {
                $result = $this->scheduleGenerator->generateSchedules(
                    $request->start_date,
                    $request->end_date,
                    $userId
                );

                if ($result['success']) {
                    $totalGenerated += $result['generated'];
                    $totalSkipped += $result['skipped'];
                }
            }

            return redirect()->route('schedule-templates.show', $scheduleTemplate)
                            ->with('success', "Berhasil generate {$totalGenerated} jadwal, {$totalSkipped} dilewati.");

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal generate jadwal: ' . $e->getMessage()]);
        }
    }
}
