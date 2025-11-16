<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class AdminNotification extends Notification
{
    use Queueable;

    protected $message;
    protected $type;
    protected $notificationId;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $message, string $type = 'all', ?int $notificationId = null)
    {
        $this->message = $message;
        $this->type = $type;
        $this->notificationId = $notificationId;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    /**
     * Get the web push representation of the notification.
     */
    public function toWebPush(object $notifiable, $notification): WebPushMessage
    {
        $title = 'Ceremood';
        
        // Tentukan title berdasarkan type
        if ($this->type === 'individual') {
            $title = 'Notifikasi Individu';
        } elseif ($this->type === 'group') {
            $title = 'Notifikasi Divisi';
        } else {
            $title = 'Notifikasi Semua Karyawan';
        }

        return (new WebPushMessage)
            ->title($title)
            ->body($this->message)
            ->icon('/logo/favicons.png')
            ->badge('/logo/favicons.png')
            ->data([
                'url' => route('notif.index'),
                'notification_id' => $this->notificationId
            ])
            ->options([
                'TTL' => 86400, // 24 jam
                'urgency' => 'normal',
            ]);
    }
}
