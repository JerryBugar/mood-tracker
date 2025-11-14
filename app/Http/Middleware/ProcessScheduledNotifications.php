<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Services\NotificationService;
use Symfony\Component\HttpFoundation\Response;

class ProcessScheduledNotifications
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Hanya proses jika user sudah login
        if (Auth::check()) {
            // Rate limiting: hanya proses sekali per 10 detik untuk menghindari overhead
            // Tapi tetap cukup sering untuk memproses notifikasi tepat waktu
            $cacheKey = 'process_notifications_global';
            
            if (!Cache::has($cacheKey)) {
                try {
                    $service = app(NotificationService::class);
                    $service->processScheduledNotifications();
                    
                    // Set cache untuk 10 detik (lebih sering untuk akurasi lebih baik)
                    Cache::put($cacheKey, true, 10);
                } catch (\Exception $e) {
                    // Log error tapi jangan block request
                    \Log::error('Error processing scheduled notifications: ' . $e->getMessage());
                }
            }
        }

        return $next($request);
    }
}

