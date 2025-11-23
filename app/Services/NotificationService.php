<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Notifications\AdminNotification;

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
        // Gunakan timezone aplikasi (Asia/Jakarta)
        $now = now()->timezone('Asia/Jakarta');
        $query = Notification::whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', $now)
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

                // Pastikan $users adalah collection dan tidak null
                if (!$users || !($users instanceof \Illuminate\Support\Collection) || $users->isEmpty()) {
                    DB::rollBack();
                    $errors[] = "Notifikasi ID {$notification->id}: Tidak ada user target";
                    continue;
                }

                // Attach users ke notification
                $userIds = $users->pluck('id')->toArray();
                $notification->users()->attach($userIds);
                
                // Kirim push notification ke user yang subscribe
                $this->sendPushNotifications($notification, $users);
                
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

        try {
            if ($notification->type === 'individual') {
                if ($notification->target_user_id) {
                    $foundUsers = User::where('id', $notification->target_user_id)->get();
                    if ($foundUsers) {
                        $users = $foundUsers;
                    }
                }
            } elseif ($notification->type === 'group') {
                if ($notification->division) {
                    $foundUsers = User::where('division', $notification->division)->get();
                    if ($foundUsers) {
                        $users = $foundUsers;
                    }
                }
            } elseif ($notification->type === 'all') {
                $foundUsers = User::all();
                if ($foundUsers) {
                    $users = $foundUsers;
                }
            }
        } catch (\Exception $e) {
            \Log::error("Error dalam getTargetUsers: " . $e->getMessage());
            // Return empty collection jika error
            return collect();
        }

        // Pastikan selalu return collection, bukan null
        return $users instanceof \Illuminate\Support\Collection ? $users : collect();
    }

    /**
     * Mengirim push notification ke user yang subscribe
     * 
     * @param Notification $notification
     * @param \Illuminate\Support\Collection $users
     * @return void
     */
    public function sendPushNotifications(Notification $notification, $users): void
    {
        try {
            // Pastikan $users adalah collection
            if (!$users || !($users instanceof \Illuminate\Support\Collection)) {
                \Log::warning("sendPushNotifications: users bukan collection atau null");
                return;
            }

            // Pastikan users tidak kosong
            if ($users->isEmpty()) {
                return;
            }

            $webPushNotification = new AdminNotification(
                $notification->message,
                $notification->type,
                $notification->id
            );

            foreach ($users as $user) {
                try {
                    // Pastikan user valid
                    if (!$user || !($user instanceof \App\Models\User)) {
                        continue;
                    }

                    // Cek apakah user memiliki push subscription dengan cara yang lebih aman
                    try {
                        $subscriptionCount = $user->pushSubscriptions()->count();
                        
                        if ($subscriptionCount > 0) {
                            $user->notify($webPushNotification);
                        }
                    } catch (\Exception $subscriptionError) {
                        // Jika error saat cek subscription, skip user ini
                        $userId = isset($user) && isset($user->id) ? $user->id : 'unknown';
                        \Log::warning("Tidak bisa cek push subscription untuk user {$userId}: " . $subscriptionError->getMessage());
                        continue;
                    }
                } catch (\Exception $e) {
                    // Log error tapi jangan stop proses
                    $userId = isset($user) && isset($user->id) ? $user->id : 'unknown';
                    \Log::error("Gagal mengirim push notification ke user {$userId}: " . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            // Log error tapi jangan throw exception
            \Log::error("Error dalam sendPushNotifications: " . $e->getMessage());
        }
    }
}

