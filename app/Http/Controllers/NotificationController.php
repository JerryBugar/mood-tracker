<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;
use App\Services\NotificationService;
use App\Helpers\TurboStreamHelper;
use NotificationChannels\WebPush\PushSubscription;

class NotificationController extends Controller
{
    /**
     * Menampilkan halaman notifikasi
     */
    public function index(NotificationService $service, Request $request)
    {
        $user = Auth::user();
        
        // Polling fallback: Proses notifikasi yang sudah waktunya (jika queue worker tidak jalan)
        // Ini memastikan notifikasi tetap muncul meski queue worker tidak berjalan
        $service->processScheduledNotifications();
        
        // Ambil notifikasi user yang sudah di-attach (tanpa pagination)
        // Gunakan fresh() untuk memastikan data selalu terbaru dari database
        // Reload user untuk memastikan relasi fresh
        $user->load('notifications');
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->get();

        // Gunakan response() helper untuk wrap view menjadi Response object
        $response = response()->view('notif.index', compact('notifications'));
        
        // Tambahkan cache control headers untuk memastikan data selalu fresh
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        return $response;
    }

    /**
     * Tandai notifikasi sebagai sudah dibaca
     */
    public function markAsRead($id, Request $request)
    {
        $user = Auth::user();
        
        $notification = $user->notifications()->where('notifications.id', $id)->first();
        
        if (!$notification) {
            $acceptHeader = $request->header('Accept', '');
            if (strpos($acceptHeader, 'text/vnd.turbo-stream.html') !== false) {
                return response('', 404);
            }
            return response()->json([
                'success' => false,
                'message' => 'Notifikasi tidak ditemukan'
            ], 404);
        }

        // Update pivot table
        $user->notifications()->updateExistingPivot($id, [
            'is_read' => true,
            'read_at' => now()
        ]);

        // Jika request Turbo Stream, return Turbo Stream response
        $acceptHeader = $request->header('Accept', '');
        if (strpos($acceptHeader, 'text/vnd.turbo-stream.html') !== false) {
            // Reload notifications frame dengan data fresh (tanpa pagination)
            // Reload user untuk memastikan relasi fresh
            $user->load('notifications');
            $notifications = $user->notifications()
                ->orderBy('created_at', 'desc')
                ->get();
            
            $notificationsContent = view('notif._partials.notifications_list', compact('notifications'))->render();
            
            return response(
                TurboStreamHelper::replace('notifications_frame', $notificationsContent),
                200,
                [
                    'Content-Type' => 'text/vnd.turbo-stream.html',
                    'Cache-Control' => 'no-cache, no-store, must-revalidate, max-age=0',
                    'Pragma' => 'no-cache',
                    'Expires' => '0'
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi ditandai sebagai sudah dibaca'
        ]);
    }

    /**
     * Tandai semua notifikasi sebagai sudah dibaca
     */
    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();
        
        // Update semua notifikasi yang belum dibaca menggunakan DB facade
        DB::table('notification_user')
            ->where('user_id', $user->id)
            ->where(function($query) {
                $query->where('is_read', false)
                      ->orWhereNull('is_read');
            })
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        // Jika request Turbo Stream, return Turbo Stream response
        $acceptHeader = $request->header('Accept', '');
        if (strpos($acceptHeader, 'text/vnd.turbo-stream.html') !== false) {
            // Reload notifications frame dengan data fresh (tanpa pagination)
            // Reload user untuk memastikan relasi fresh
            $user->load('notifications');
            $notifications = $user->notifications()
                ->orderBy('created_at', 'desc')
                ->get();
            
            $notificationsContent = view('notif._partials.notifications_list', compact('notifications'))->render();
            
            return response(
                TurboStreamHelper::replace('notifications_frame', $notificationsContent),
                200,
                [
                    'Content-Type' => 'text/vnd.turbo-stream.html',
                    'Cache-Control' => 'no-cache, no-store, must-revalidate, max-age=0',
                    'Pragma' => 'no-cache',
                    'Expires' => '0'
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi ditandai sebagai sudah dibaca'
        ]);
    }

    /**
     * Hapus semua notifikasi yang sudah dibaca
     */
    public function deleteAll(Request $request)
    {
        $user = Auth::user();
        
        try {
            DB::beginTransaction();
            
            // Ambil semua notification ID yang sudah dibaca oleh user
            $readNotificationIds = DB::table('notification_user')
                ->where('user_id', $user->id)
                ->where('is_read', true)
                ->pluck('notification_id')
                ->toArray();
            
            if (empty($readNotificationIds)) {
                $acceptHeader = $request->header('Accept', '');
                if (strpos($acceptHeader, 'text/vnd.turbo-stream.html') !== false) {
                    return response('', 400);
                }
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada notifikasi yang bisa dihapus'
                ], 400);
            }
            
            // Hapus relasi dari pivot table
            DB::table('notification_user')
                ->where('user_id', $user->id)
                ->where('is_read', true)
                ->delete();
            
            // Hapus notification individual yang hanya untuk user ini
            Notification::whereIn('id', $readNotificationIds)
                ->where('type', 'individual')
                ->where('target_user_id', $user->id)
                ->delete();
            
            // Hapus notification yang tidak memiliki relasi lagi (tidak ada user yang terhubung)
            $notificationsWithoutUsers = DB::table('notifications')
                ->whereIn('id', $readNotificationIds)
                ->whereNotExists(function($query) {
                    $query->select(DB::raw(1))
                          ->from('notification_user')
                          ->whereColumn('notification_user.notification_id', 'notifications.id');
                })
                ->pluck('id')
                ->toArray();
            
            if (!empty($notificationsWithoutUsers)) {
                Notification::whereIn('id', $notificationsWithoutUsers)->delete();
            }
            
            DB::commit();
            
            // Jika request Turbo Stream, return Turbo Stream response
            $acceptHeader = $request->header('Accept', '');
            if (strpos($acceptHeader, 'text/vnd.turbo-stream.html') !== false) {
                // Reload notifications frame dengan data fresh (tanpa pagination)
                // Reload user untuk memastikan relasi fresh
                $user->load('notifications');
                $notifications = $user->notifications()
                    ->orderBy('created_at', 'desc')
                    ->get();
                
                $notificationsContent = view('notif._partials.notifications_list', compact('notifications'))->render();
                
                return response(
                    TurboStreamHelper::replace('notifications_frame', $notificationsContent),
                    200,
                    ['Content-Type' => 'text/vnd.turbo-stream.html']
                );
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Semua notifikasi berhasil dihapus'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            $acceptHeader = $request->header('Accept', '');
            if (strpos($acceptHeader, 'text/vnd.turbo-stream.html') !== false) {
                return response('', 500);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus notifikasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Subscribe push notification
     */
    public function subscribePush(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'endpoint' => 'required|url',
            'keys' => 'required|array',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
        ]);

        try {
            // Gunakan firstOrNew untuk memastikan semua field terisi saat insert
            $subscription = PushSubscription::firstOrNew(
                [
                    'endpoint' => $request->endpoint,
                    'subscribable_type' => get_class($user),
                    'subscribable_id' => $user->id,
                ]
            );
            
            // Set values
            $subscription->public_key = $request->keys['p256dh'];
            $subscription->auth_token = $request->keys['auth'];
            $subscription->subscribable_type = get_class($user);
            $subscription->subscribable_id = $user->id;
            
            $subscription->save();

            return response()->json([
                'success' => true,
                'message' => 'Push notification berhasil diaktifkan',
                'subscription' => $subscription
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal subscribe push notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unsubscribe push notification
     */
    public function unsubscribePush(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'endpoint' => 'required|url',
        ]);

        try {
            $deleted = PushSubscription::where('endpoint', $request->endpoint)
                ->where('subscribable_type', get_class($user))
                ->where('subscribable_id', $user->id)
                ->delete();

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Push notification berhasil dinonaktifkan'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Subscription tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal unsubscribe push notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cek status push subscription
     */
    public function checkPushStatus()
    {
        $user = Auth::user();
        
        $subscriptions = PushSubscription::where('subscribable_type', get_class($user))
            ->where('subscribable_id', $user->id)
            ->get();
        
        return response()->json([
            'success' => true,
            'subscribed' => $subscriptions->isNotEmpty(),
            'count' => $subscriptions->count()
        ]);
    }
}
