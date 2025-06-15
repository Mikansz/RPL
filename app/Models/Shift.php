<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'is_active',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function users()
    {
        return $this->hasManyThrough(User::class, Schedule::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helper methods
    public function calculateLateMinutes($clockInTime)
    {
        $startTime = Carbon::parse($this->start_time);
        $clockIn = Carbon::parse($clockInTime);

        if ($clockIn->gt($startTime)) {
            // Get tolerance from default attendance rule
            $defaultRule = \App\Models\AttendanceRule::where('is_default', true)->first();
            $toleranceMinutes = $defaultRule ? $defaultRule->late_tolerance_minutes : 15;

            // Hitung menit terlambat, dikurangi toleransi
            // Pastikan perhitungan yang benar: clock_in - start_time
            $diffInSeconds = $clockIn->timestamp - $startTime->timestamp;
            $lateMinutes = $diffInSeconds / 60;
            return max(0, $lateMinutes - $toleranceMinutes);
        }

        return 0;
    }

    /**
     * Calculate attendance status based on clock in time
     * Returns: 'early', 'on_time', 'late'
     */
    public function calculateAttendanceStatus($clockInTime)
    {
        $startTime = Carbon::parse($this->start_time);
        $clockIn = Carbon::parse($clockInTime);

        // Get tolerance from default attendance rule
        $defaultRule = \App\Models\AttendanceRule::where('is_default', true)->first();
        $toleranceMinutes = $defaultRule ? $defaultRule->late_tolerance_minutes : 15;

        if ($clockIn->lt($startTime)) {
            return 'early'; // Terlalu dini
        } elseif ($clockIn->eq($startTime)) {
            return 'on_time'; // Tepat waktu
        } else {
            // Hitung selisih menit setelah waktu mulai shift
            // Pastikan perhitungan yang benar: clock_in - start_time
            $diffInSeconds = $clockIn->timestamp - $startTime->timestamp;
            $lateMinutes = $diffInSeconds / 60;

            if ($lateMinutes <= $toleranceMinutes) {
                return 'on_time'; // Tepat waktu (dengan toleransi)
            } else {
                return 'late'; // Terlambat
            }
        }
    }

    /**
     * Get early minutes (how many minutes before start time)
     */
    public function calculateEarlyMinutes($clockInTime)
    {
        $startTime = Carbon::parse($this->start_time);
        $clockIn = Carbon::parse($clockInTime);

        if ($clockIn->lt($startTime)) {
            return $startTime->diffInMinutes($clockIn);
        }

        return 0;
    }

    public function calculateEarlyLeaveMinutes($clockOutTime)
    {
        $endTime = Carbon::parse($this->end_time);
        $clockOut = Carbon::parse($clockOutTime);

        if ($clockOut->lt($endTime)) {
            // Get tolerance from default attendance rule
            $defaultRule = \App\Models\AttendanceRule::where('is_default', true)->first();
            $toleranceMinutes = $defaultRule ? $defaultRule->early_leave_tolerance_minutes : 15;

            // Pastikan perhitungan yang benar: end_time - clock_out
            $diffInSeconds = $endTime->timestamp - $clockOut->timestamp;
            $earlyMinutes = $diffInSeconds / 60;
            return max(0, $earlyMinutes - $toleranceMinutes);
        }

        return 0;
    }

    public function calculateOvertimeMinutes($clockOutTime)
    {
        $endTime = Carbon::parse($this->end_time);
        $clockOut = Carbon::parse($clockOutTime);

        if ($clockOut->gt($endTime)) {
            // Pastikan perhitungan yang benar: clock_out - end_time
            $diffInSeconds = $clockOut->timestamp - $endTime->timestamp;
            $overtimeMinutes = $diffInSeconds / 60;
            return max(0, $overtimeMinutes);
        }

        return 0;
    }

    public function getWorkDurationMinutes()
    {
        $startTime = Carbon::parse($this->start_time);
        $endTime = Carbon::parse($this->end_time);

        return $endTime->diffInMinutes($startTime);
    }

    public function getFormattedStartTimeAttribute()
    {
        return Carbon::parse($this->start_time)->format('H:i');
    }

    public function getFormattedEndTimeAttribute()
    {
        return Carbon::parse($this->end_time)->format('H:i');
    }

    public function getShiftDurationAttribute()
    {
        $startTime = Carbon::parse($this->start_time);
        $endTime = Carbon::parse($this->end_time);
        
        return $endTime->diff($startTime)->format('%H:%I');
    }
}
