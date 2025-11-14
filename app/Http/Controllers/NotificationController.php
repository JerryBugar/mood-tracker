<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;
use App\Services\NotificationService;

class NotificationController extends Controller
{
    /**
     * Menampilkan halaman notifikasi
     */
    public function index(NotificationService $service)
    {
        $user = Auth::user();
        
        // Polling fallback: Proses notifikasi yang sudah waktunya (jika queue worker tidak jalan)
        // Ini memastikan notifikasi tetap muncul meski queue worker tidak berjalan
        $service->processScheduledNotifications();
        
        // Ambil notifikasi user yang sudah di-attach
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('notif.index', compact('notifications'));
    }

    /**
     * Tandai notifikasi sebagai sudah dibaca
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        
        $notification = $user->notifications()->where('notifications.id', $id)->first();
        
        if (!$notification) {
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

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi ditandai sebagai sudah dibaca'
        ]);
    }

    /**
     * Tandai semua notifikasi sebagai sudah dibaca
     */
    public function markAllAsRead()
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

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi ditandai sebagai sudah dibaca'
        ]);
    }
}
