<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    /**
     * Memproses notifikasi yang sudah waktunya dikirim
     * 
     * @param int|null $notificationId ID notifikasi spesifik (optional)
     * @return array
     */
    public function processScheduledNotifications(?int $notificationId = null): array
    {
        $processedCount = 0;
        $errors = [];

        // Query notifikasi yang perlu diproses
        // Gunakan UTC untuk konsistensi dengan database
        $now = now()->utc();
        $query = Notification::whereNotNull('scheduled_at')
            ->whereRaw('scheduled_at <= ?', [$now])
            ->whereDoesntHave('users');

        // Jika ada notificationId, proses hanya notifikasi tersebut
        if ($notificationId) {
            $query->where('id', $notificationId);
        }

        $notifications = $query->get();

        if ($notifications->isEmpty()) {
            return [
                'processed' => 0,
                'errors' => []
            ];
        }

        foreach ($notifications as $notification) {
            try {
                DB::beginTransaction();

                // Tentukan user yang akan menerima notifikasi
                $users = $this->getTargetUsers($notification);

                if ($users->isEmpty()) {
                    DB::rollBack();
                    $errors[] = "Notifikasi ID {$notification->id}: Tidak ada user target";
                    continue;
                }

                // Attach users ke notification
                $userIds = $users->pluck('id')->toArray();
                $notification->users()->attach($userIds);
                $processedCount++;

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $errors[] = "Notifikasi ID {$notification->id}: " . $e->getMessage();
            }
        }

        return [
            'processed' => $processedCount,
            'errors' => $errors
        ];
    }

    /**
     * Mendapatkan user target berdasarkan tipe notifikasi
     * 
     * @param Notification $notification
     * @return \Illuminate\Support\Collection
     */
    protected function getTargetUsers(Notification $notification)
    {
        $users = collect();

        if ($notification->type === 'individual') {
            if ($notification->target_user_id) {
                $users = User::where('id', $notification->target_user_id)->get();
            }
        } elseif ($notification->type === 'group') {
            $users = User::where('division', $notification->division)->get();
        } elseif ($notification->type === 'all') {
            $users = User::all();
        }

        return $users;
    }
}

