<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;

class SendAttendanceReminders extends Command
{
    protected $signature = 'notifications:attendance-reminders';
    protected $description = 'Send attendance reminders to users';

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    public function handle()
    {
        $this->info('Sending attendance reminders...');
        
        $count = $this->notificationService->sendAttendanceReminders();
        
        $this->info("Attendance reminders sent: {$count}");
        
        // Also send late attendance alerts
        $this->info('Checking for late attendance...');
        $lateCount = $this->notificationService->sendLateAttendanceAlerts();
        $this->info("Late attendance alerts sent: {$lateCount}");
        
        return 0;
    }
}
