<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Schedule;

class AttendanceReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $schedule;
    protected $reminderType;

    public function __construct(Schedule $schedule, $reminderType = 'clock_in')
    {
        $this->schedule = $schedule;
        $this->reminderType = $reminderType;
    }

    public function via($notifiable)
    {
        $channels = ['database'];
        
        $settings = $notifiable->notificationSettings()
            ->where('event', 'attendance_reminder')
            ->where('enabled', true)
            ->get();

        foreach ($settings as $setting) {
            if ($setting->type === 'email') {
                $channels[] = 'mail';
            }
        }

        return $channels;
    }

    public function toMail($notifiable)
    {
        $subject = $this->reminderType === 'clock_in' ? 'Pengingat Clock In' : 'Pengingat Clock Out';
        $message = $this->reminderType === 'clock_in' 
            ? 'Jangan lupa untuk melakukan clock in hari ini!'
            : 'Jangan lupa untuk melakukan clock out!';

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Halo ' . $notifiable->first_name . '!')
            ->line($message)
            ->line('**Jadwal Hari Ini:**')
            ->line('**Tipe Kerja:** ' . $this->schedule->work_type)
            ->line('**Shift:** ' . $this->schedule->shift->name . ' (' . $this->schedule->shift->formatted_start_time . ' - ' . $this->schedule->shift->formatted_end_time . ')')
            ->when($this->schedule->office, function ($message) {
                return $message->line('**Kantor:** ' . $this->schedule->office->name);
            })
            ->action('Buka Absensi', route('attendance.clock'))
            ->line('Terima kasih!');
    }

    public function toArray($notifiable)
    {
        $title = $this->reminderType === 'clock_in' ? 'Pengingat Clock In' : 'Pengingat Clock Out';
        $message = $this->reminderType === 'clock_in' 
            ? 'Jangan lupa clock in untuk jadwal ' . $this->schedule->work_type . ' hari ini'
            : 'Jangan lupa clock out untuk mengakhiri jam kerja';

        return [
            'type' => 'attendance_reminder',
            'reminder_type' => $this->reminderType,
            'title' => $title,
            'message' => $message,
            'schedule_id' => $this->schedule->id,
            'work_type' => $this->schedule->work_type,
            'shift_name' => $this->schedule->shift->name,
            'action_url' => route('attendance.clock'),
        ];
    }
}
