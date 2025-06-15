<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'total_work_minutes',
        'late_minutes',
        'early_minutes',
        'early_leave_minutes',
        'overtime_minutes',
        'status',
        'clock_in_ip',
        'clock_out_ip',
        'clock_in_lat',
        'clock_in_lng',
        'clock_out_lat',
        'clock_out_lng',
        'office_id',
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime:H:i:s',
        'clock_out' => 'datetime:H:i:s',
        'clock_in_lat' => 'decimal:8',
        'clock_in_lng' => 'decimal:8',
        'clock_out_lat' => 'decimal:8',
        'clock_out_lng' => 'decimal:8',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function schedule()
    {
        return $this->hasOne(Schedule::class, 'user_id', 'user_id')
                    ->where('schedule_date', $this->date);
    }

    // Scopes
    public function scopeByDate($query, $date)
    {
        return $query->where('date', $date);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Helper methods
    public function isPresent()
    {
        return in_array($this->status, ['present', 'late', 'early', 'early_leave', 'half_day']);
    }

    public function isLate()
    {
        return $this->late_minutes > 0 || $this->status === 'late';
    }

    public function isEarly()
    {
        return $this->early_minutes > 0;
    }

    public function isEarlyLeave()
    {
        return $this->early_leave_minutes > 0 || $this->status === 'early_leave';
    }

    public function hasOvertime()
    {
        return $this->overtime_minutes > 0;
    }

    public function getStatusIndonesian()
    {
        $statusLabels = [
            'present' => 'Hadir',
            'late' => 'Terlambat',
            'early_leave' => 'Pulang Awal',
            'absent' => 'Alpha',
            'sick' => 'Sakit',
            'leave' => 'Cuti',
            'holiday' => 'Libur',
            'half_day' => 'Setengah Hari'
        ];

        return $statusLabels[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColor()
    {
        $statusColors = [
            'present' => 'success',
            'late' => 'warning',
            'early_leave' => 'info',
            'absent' => 'danger',
            'sick' => 'secondary',
            'leave' => 'primary',
            'holiday' => 'dark',
            'half_day' => 'light'
        ];

        return $statusColors[$this->status] ?? 'secondary';
    }

    public function getTotalWorkHoursAttribute()
    {
        return round($this->total_work_minutes / 60, 2);
    }

    public function getTotalOvertimeHoursAttribute()
    {
        return round($this->overtime_minutes / 60, 2);
    }

    public function getWorkDurationAttribute()
    {
        if (!$this->clock_in || !$this->clock_out) {
            return null;
        }

        $clockIn = Carbon::parse($this->clock_in);
        $clockOut = Carbon::parse($this->clock_out);
        
        return $clockOut->diff($clockIn)->format('%H:%I');
    }
}
