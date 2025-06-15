<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Schedule;

class ScheduleReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $schedule;

    public function __construct(Schedule $schedule)
    {
        $this->schedule = $schedule;
    }

    public function via($notifiable)
    {
        $channels = ['database'];
        
        // Check user notification settings
        $settings = $notifiable->notificationSettings()
            ->where('event', 'schedule_reminder')
            ->where('enabled', true)
            ->get();

        foreach ($settings as $setting) {
            if ($setting->type === 'email') {
                $channels[] = 'mail';
            }
            // Add other channels as needed
        }

        return $channels;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Pengingat Jadwal Kerja - ' . $this->schedule->schedule_date->format('d/m/Y'))
            ->greeting('Halo ' . $notifiable->first_name . '!')
            ->line('Anda memiliki jadwal kerja besok:')
            ->line('**Tanggal:** ' . $this->schedule->schedule_date->format('l, d F Y'))
            ->line('**Tipe Kerja:** ' . $this->schedule->work_type)
            ->line('**Shift:** ' . $this->schedule->shift->name . ' (' . $this->schedule->shift->formatted_start_time . ' - ' . $this->schedule->shift->formatted_end_time . ')')
            ->when($this->schedule->office, function ($message) {
                return $message->line('**Kantor:** ' . $this->schedule->office->name);
            })
            ->when($this->schedule->notes, function ($message) {
                return $message->line('**Catatan:** ' . $this->schedule->notes);
            })
            ->action('Lihat Jadwal', route('schedules.show', $this->schedule))
            ->line('Jangan lupa untuk melakukan absensi tepat waktu!');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'schedule_reminder',
            'title' => 'Pengingat Jadwal Kerja',
            'message' => 'Anda memiliki jadwal ' . $this->schedule->work_type . ' besok (' . $this->schedule->schedule_date->format('d/m/Y') . ')',
            'schedule_id' => $this->schedule->id,
            'schedule_date' => $this->schedule->schedule_date->format('Y-m-d'),
            'work_type' => $this->schedule->work_type,
            'shift_name' => $this->schedule->shift->name,
            'office_name' => $this->schedule->office?->name,
            'action_url' => route('schedules.show', $this->schedule),
        ];
    }
}
