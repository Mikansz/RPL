<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;

class SendScheduleReminders extends Command
{
    protected $signature = 'notifications:schedule-reminders';
    protected $description = 'Send schedule reminders to users';

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    public function handle()
    {
        $this->info('Sending schedule reminders...');
        
        $count = $this->notificationService->sendScheduleReminders();
        
        $this->info("Schedule reminders sent to {$count} users.");
        
        return 0;
    }
}
