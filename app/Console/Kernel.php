<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Schedule reminders - send at 6 PM for next day
        $schedule->command('notifications:schedule-reminders')
                 ->dailyAt('18:00')
                 ->description('Send schedule reminders for tomorrow');

        // Attendance reminders - check every 15 minutes during work hours
        $schedule->command('notifications:attendance-reminders')
                 ->everyFifteenMinutes()
                 ->between('07:00', '19:00')
                 ->description('Send attendance reminders');

        // Clean old notifications - monthly
        $schedule->command('notifications:cleanup')
                 ->monthly()
                 ->description('Clean up old notifications');

        // Generate daily attendance reports
        $schedule->command('reports:daily-attendance')
                 ->dailyAt('23:30')
                 ->description('Generate daily attendance reports');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
