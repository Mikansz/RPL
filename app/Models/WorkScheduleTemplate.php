<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class WorkScheduleTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'shift_id',
        'office_id',
        'work_type',
        'work_days',
        'exclude_sundays',
        'exclude_holidays',
        'is_active',
        'effective_from',
        'effective_until',
    ];

    protected $casts = [
        'work_days' => 'array',
        'exclude_sundays' => 'boolean',
        'exclude_holidays' => 'boolean',
        'is_active' => 'boolean',
        'effective_from' => 'date',
        'effective_until' => 'date',
    ];

    // Relationships
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function employees()
    {
        return $this->belongsToMany(User::class, 'employee_schedule_templates')
                    ->withPivot(['assigned_from', 'assigned_until', 'is_active'])
                    ->withTimestamps();
    }

    public function employeeAssignments()
    {
        return $this->hasMany(EmployeeScheduleTemplate::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeEffectiveOn($query, $date)
    {
        $date = Carbon::parse($date);

        return $query->where(function ($q) use ($date) {
            // Permanent schedules (both dates null) are always effective
            $q->where(function ($permanentQ) {
                $permanentQ->whereNull('effective_from')
                          ->whereNull('effective_until');
            })
            // Or schedules with date ranges that include the given date
            ->orWhere(function ($dateRangeQ) use ($date) {
                $dateRangeQ->where('effective_from', '<=', $date)
                          ->where(function ($subQ) use ($date) {
                              $subQ->whereNull('effective_until')
                                   ->orWhere('effective_until', '>=', $date);
                          });
            });
        });
    }

    // Helper methods
    public function isPermanent()
    {
        return is_null($this->effective_from) && is_null($this->effective_until);
    }

    public function getEffectivePeriodAttribute()
    {
        if ($this->isPermanent()) {
            return 'Berlaku Selamanya';
        }

        $from = $this->effective_from ? $this->effective_from->format('d/m/Y') : 'Tidak terbatas';
        $until = $this->effective_until ? $this->effective_until->format('d/m/Y') : 'Tidak terbatas';

        return "{$from} - {$until}";
    }



    // Helper methods
    public function isEffectiveOn($date)
    {
        $date = Carbon::parse($date);
        
        if ($this->effective_from && $date->lt($this->effective_from)) {
            return false;
        }
        
        if ($this->effective_until && $date->gt($this->effective_until)) {
            return false;
        }
        
        return true;
    }

    public function shouldWorkOn($date)
    {
        $date = Carbon::parse($date);
        
        // Check if template is effective on this date
        if (!$this->isEffectiveOn($date)) {
            return false;
        }
        
        // Check if it's Sunday and should be excluded
        if ($this->exclude_sundays && $date->isSunday()) {
            return false;
        }
        
        // Check if it's a holiday and should be excluded
        if ($this->exclude_holidays && Holiday::isHoliday($date)) {
            return false;
        }
        
        // Check if the day of week is in work_days
        // Carbon: 1 = Monday, 7 = Sunday
        $dayOfWeek = $date->dayOfWeek;
        if ($dayOfWeek === 0) { // Sunday in Carbon is 0
            $dayOfWeek = 7;
        }
        
        return in_array($dayOfWeek, $this->work_days ?? []);
    }

    public function getWorkDaysTextAttribute()
    {
        $days = [
            1 => 'Senin',
            2 => 'Selasa', 
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu'
        ];
        
        $workDayNames = [];
        foreach ($this->work_days ?? [] as $day) {
            if (isset($days[$day])) {
                $workDayNames[] = $days[$day];
            }
        }
        
        return implode(', ', $workDayNames);
    }
}
