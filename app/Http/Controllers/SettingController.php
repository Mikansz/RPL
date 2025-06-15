<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRule;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'company_name' => config('app.name'),
            'company_address' => 'Jl. Contoh No. 123, Jakarta',
            'company_phone' => '021-12345678',
            'company_email' => 'info@company.com',
            'working_hours_start' => '08:00',
            'working_hours_end' => '17:00',
            'late_tolerance_minutes' => 15,
            'overtime_rate' => 1.5,
        ];
        
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'company_address' => 'required|string',
            'company_phone' => 'required|string|max:20',
            'company_email' => 'required|email|max:255',
            'working_hours_start' => 'required|date_format:H:i',
            'working_hours_end' => 'required|date_format:H:i',
            'late_tolerance_minutes' => 'required|integer|min:0|max:60',
            'overtime_rate' => 'required|numeric|min:1|max:5',
        ]);

        // Update default attendance rule
        $defaultRule = AttendanceRule::where('is_default', true)->first();
        if ($defaultRule) {
            $defaultRule->update([
                'work_start_time' => $request->working_hours_start,
                'work_end_time' => $request->working_hours_end,
                'late_tolerance_minutes' => $request->late_tolerance_minutes,
                'overtime_multiplier' => $request->overtime_rate,
            ]);
        }

        // Cache settings for quick access
        Cache::put('company_settings', $request->only([
            'company_name', 'company_address', 'company_phone', 'company_email'
        ]), now()->addDays(30));

        return redirect()->route('settings.index')
                        ->with('success', 'Settings updated successfully.');
    }

    public function attendanceRules()
    {
        $rules = AttendanceRule::paginate(10);
        return view('settings.attendance-rules', compact('rules'));
    }

    public function leaveTypes()
    {
        $leaveTypes = LeaveType::paginate(10);
        return view('settings.leave-types', compact('leaveTypes'));
    }
}
