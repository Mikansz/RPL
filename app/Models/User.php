<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'employee_id',
        'username',
        'email',
        'password',
        'first_name',
        'last_name',
        'phone',
        'gender',
        'birth_date',
        'address',
        'profile_photo',
        'status',
        'last_login_at',
        'last_login_ip',
        'force_password_change',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'birth_date' => 'date',
        'last_login_at' => 'datetime',
        'force_password_change' => 'boolean',
        'password' => 'hashed',
    ];

    // Relationships
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles')
                    ->withPivot(['assigned_at', 'expires_at', 'is_active'])
                    ->withTimestamps();
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    public function salaryComponents()
    {
        return $this->belongsToMany(SalaryComponent::class, 'employee_salary_components')
                    ->withPivot(['amount', 'effective_date', 'end_date', 'is_active'])
                    ->withTimestamps();
    }

    public function supervisedEmployees()
    {
        return $this->hasMany(Employee::class, 'supervisor_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function createdSchedules()
    {
        return $this->hasMany(Schedule::class, 'created_by');
    }

    public function approvedSchedules()
    {
        return $this->hasMany(Schedule::class, 'approved_by');
    }

    public function schedule()
    {
        return $this->hasOne(Schedule::class);
    }

    public function shift()
    {
        return $this->hasOneThrough(Shift::class, Schedule::class, 'user_id', 'id', 'id', 'shift_id');
    }

    public function office()
    {
        return $this->hasOneThrough(Office::class, Schedule::class, 'user_id', 'id', 'id', 'office_id');
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    // Helper methods
    public function hasRole($roleName)
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    public function hasPermission($permissionName)
    {
        return $this->roles()
                    ->whereHas('permissions', function ($query) use ($permissionName) {
                        $query->where('name', $permissionName);
                    })->exists();
    }

    public function hasAnyRole($roles)
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }

        return $this->roles()->whereIn('name', $roles)->exists();
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function getCurrentSalaryComponents()
    {
        return $this->salaryComponents()
                    ->where('salary_components.is_active', true)
                    ->wherePivot('is_active', true)
                    ->wherePivot('effective_date', '<=', now());
    }

    // Schedule helper methods
    public function getTodaySchedule()
    {
        return $this->schedules()
                    ->with(['shift', 'office'])
                    ->where('schedule_date', today())
                    ->where('status', '!=', 'cancelled')
                    ->first();
    }

    public function getScheduleForDate($date)
    {
        return $this->schedules()
                    ->where('schedule_date', $date)
                    ->where('status', '!=', 'cancelled')
                    ->first();
    }

    public function hasScheduleForDate($date)
    {
        return $this->schedules()
                    ->where('schedule_date', $date)
                    ->where('status', '!=', 'cancelled')
                    ->exists();
    }

    public function canClockInToday()
    {
        $todaySchedule = $this->getTodaySchedule();
        if ($todaySchedule) {
            return true;
        }

        // Check if can create schedule using default settings
        if (!$this->employee || !$this->employee->default_shift_id) {
            return false;
        }

        // For WFO, office is required
        if ($this->employee->default_work_type === 'WFO' && !$this->employee->default_office_id) {
            return false;
        }

        return true;
    }

    public function getTodayWorkType()
    {
        $todaySchedule = $this->getTodaySchedule();
        return $todaySchedule ? $todaySchedule->work_type : null;
    }

    public function getOrCreateTodaySchedule()
    {
        // First try to get existing schedule
        $todaySchedule = $this->getTodaySchedule();
        if ($todaySchedule) {
            return $todaySchedule;
        }

        // If no schedule exists, create one using employee's default settings
        if (!$this->employee) {
            return null;
        }

        $employee = $this->employee;

        // Check if employee has default shift and office (for WFO)
        if (!$employee->default_shift_id) {
            return null;
        }

        // For WFO, office is required
        if ($employee->default_work_type === 'WFO' && !$employee->default_office_id) {
            return null;
        }

        // Create new schedule for today
        $scheduleData = [
            'user_id' => $this->id,
            'shift_id' => $employee->default_shift_id,
            'office_id' => $employee->default_work_type === 'WFO' ? $employee->default_office_id : null,
            'schedule_date' => today(),
            'work_type' => $employee->default_work_type,
            'status' => 'approved',
            'notes' => 'Auto-generated schedule',
            'created_by' => $this->id,
            'approved_by' => $this->id,
            'approved_at' => now(),
        ];

        $schedule = \App\Models\Schedule::create($scheduleData);

        // Load relationships
        return $schedule->load(['shift', 'office']);
    }

    public function notificationSettings()
    {
        return $this->hasMany(NotificationSetting::class);
    }

    public function pushSubscriptions()
    {
        return $this->hasMany(PushSubscription::class);
    }
}
