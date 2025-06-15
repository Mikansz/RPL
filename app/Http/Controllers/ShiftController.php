<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shift;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::orderBy('start_time')->paginate(20);
        return view('shifts.index', compact('shifts'));
    }

    public function create()
    {
        return view('shifts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:shifts,name',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'is_active' => 'boolean',
        ]);

        Shift::create([
            'name' => $request->name,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'is_active' => $request->has('is_active') ? $request->is_active : true,
        ]);

        return redirect()->route('shifts.index')
                        ->with('success', 'Shift berhasil dibuat.');
    }

    public function show($id)
    {
        $shift = Shift::with(['schedules.user'])->findOrFail($id);
        return view('shifts.show', compact('shift'));
    }

    public function edit($id)
    {
        $shift = Shift::findOrFail($id);
        return view('shifts.edit', compact('shift'));
    }

    public function update(Request $request, $id)
    {
        $shift = Shift::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:50|unique:shifts,name,' . $id,
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'is_active' => 'boolean',
        ]);

        // Check if deactivating shift with active schedules
        if ($request->has('is_active') && !$request->is_active) {
            $activeSchedulesCount = $shift->schedules()
                                         ->where('status', 'approved')
                                         ->where('schedule_date', '>=', today())
                                         ->count();

            if ($activeSchedulesCount > 0) {
                return back()->withErrors([
                    'is_active' => "Tidak dapat menonaktifkan shift yang memiliki {$activeSchedulesCount} jadwal aktif."
                ]);
            }
        }

        $shift->update([
            'name' => $request->name,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'is_active' => $request->has('is_active') ? $request->is_active : $shift->is_active,
        ]);

        return redirect()->route('shifts.index')
                        ->with('success', 'Shift berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $shift = Shift::findOrFail($id);
        
        // Check if shift has active schedules
        if ($shift->schedules()->where('status', 'approved')->exists()) {
            return redirect()->back()
                            ->with('error', 'Cannot delete shift with active schedules.');
        }

        $shift->delete();

        return redirect()->route('shifts.index')
                        ->with('success', 'Shift deleted successfully.');
    }
}
