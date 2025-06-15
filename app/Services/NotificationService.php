<?php

namespace App\Services;

use App\Models\User;
use App\Models\Schedule;
use App\Models\NotificationSetting;
use App\Notifications\ScheduleReminderNotification;
use App\Notifications\AttendanceReminderNotification;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class NotificationService
{
    public function sendScheduleReminders()
    {
        // Get schedules for tomorrow
        $tomorrow = Carbon::tomorrow();
        $schedules = Schedule::with(['user', 'shift', 'office'])
            ->where('schedule_date', $tomorrow)
            ->where('status', 'approved')
            ->get();

        foreach ($schedules as $schedule) {
            $user = $schedule->user;
            
            // Check if user has enabled schedule reminders
            if ($this->isNotificationEnabled($user, 'schedule_reminder')) {
                $user->notify(new ScheduleReminderNotification($schedule));
            }
        }

        return $schedules->count();
    }

    public function sendAttendanceReminders()
    {
        $today = Carbon::today();
        $now = Carbon::now();
        
        // Get today's schedules
        $schedules = Schedule::with(['user', 'shift', 'office'])
            ->where('schedule_date', $today)
            ->where('status', 'approved')
            ->get();

        $remindersSent = 0;

        foreach ($schedules as $schedule) {
            $user = $schedule->user;
            $shift = $schedule->shift;
            
            if (!$this->isNotificationEnabled($user, 'attendance_reminder')) {
                continue;
            }

            // Check if user has attendance today
            $attendance = $user->attendances()->where('date', $today)->first();

            // Clock in reminder (15 minutes before shift start)
            $shiftStart = Carbon::parse($shift->start_time);
            $reminderTime = $shiftStart->copy()->subMinutes(15);
            
            if ($now->between($reminderTime, $shiftStart) && (!$attendance || !$attendance->clock_in)) {
                $user->notify(new AttendanceReminderNotification($schedule, 'clock_in'));
                $remindersSent++;
            }

            // Clock out reminder (at shift end time)
            $shiftEnd = Carbon::parse($shift->end_time);
            
            if ($now->between($shiftEnd, $shiftEnd->copy()->addMinutes(30)) && 
                $attendance && $attendance->clock_in && !$attendance->clock_out) {
                $user->notify(new AttendanceReminderNotification($schedule, 'clock_out'));
                $remindersSent++;
            }
        }

        return $remindersSent;
    }

    public function sendLateAttendanceAlerts()
    {
        $today = Carbon::today();
        $now = Carbon::now();
        
        $schedules = Schedule::with(['user', 'shift'])
            ->where('schedule_date', $today)
            ->where('status', 'approved')
            ->get();

        $alertsSent = 0;

        foreach ($schedules as $schedule) {
            $user = $schedule->user;
            $shift = $schedule->shift;
            
            // Check if user is late (30 minutes after shift start)
            $shiftStart = Carbon::parse($shift->start_time);
            $lateThreshold = $shiftStart->copy()->addMinutes(30);
            
            if ($now->gt($lateThreshold)) {
                $attendance = $user->attendances()->where('date', $today)->first();
                
                if (!$attendance || !$attendance->clock_in) {
                    // Send late/missing attendance notification
                    $this->sendLateAttendanceNotification($user, $schedule);
                    $alertsSent++;
                }
            }
        }

        return $alertsSent;
    }

    public function sendScheduleApprovedNotification(Schedule $schedule)
    {
        $user = $schedule->user;
        
        if ($this->isNotificationEnabled($user, 'schedule_approved')) {
            $user->notify(new \App\Notifications\ScheduleApprovedNotification($schedule));
        }
    }

    public function sendScheduleCancelledNotification(Schedule $schedule)
    {
        $user = $schedule->user;
        
        if ($this->isNotificationEnabled($user, 'schedule_cancelled')) {
            $user->notify(new \App\Notifications\ScheduleCancelledNotification($schedule));
        }
    }

    protected function isNotificationEnabled(User $user, string $event): bool
    {
        $setting = $user->notificationSettings()
            ->where('event', $event)
            ->where('enabled', true)
            ->first();

        return $setting !== null;
    }

    protected function sendLateAttendanceNotification(User $user, Schedule $schedule)
    {
        if ($this->isNotificationEnabled($user, 'late_attendance')) {
            $user->notify(new \App\Notifications\LateAttendanceNotification($schedule));
        }
    }

    public function createDefaultNotificationSettings(User $user)
    {
        $defaultSettings = [
            ['type' => 'email', 'event' => 'schedule_reminder', 'enabled' => true],
            ['type' => 'email', 'event' => 'schedule_approved', 'enabled' => true],
            ['type' => 'email', 'event' => 'attendance_reminder', 'enabled' => true],
            ['type' => 'in_app', 'event' => 'schedule_reminder', 'enabled' => true],
            ['type' => 'in_app', 'event' => 'schedule_approved', 'enabled' => true],
            ['type' => 'in_app', 'event' => 'attendance_reminder', 'enabled' => true],
            ['type' => 'in_app', 'event' => 'late_attendance', 'enabled' => true],
        ];

        foreach ($defaultSettings as $setting) {
            NotificationSetting::firstOrCreate([
                'user_id' => $user->id,
                'type' => $setting['type'],
                'event' => $setting['event'],
            ], [
                'enabled' => $setting['enabled'],
                'settings' => [],
            ]);
        }
    }
}
