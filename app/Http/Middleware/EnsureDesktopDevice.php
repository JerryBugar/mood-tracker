<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;
use Symfony\Component\HttpFoundation\Response;

class EnsureDesktopDevice
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $agent = new Agent();
        
        // Cek apakah device adalah mobile atau tablet
        if ($agent->isMobile() || $agent->isTablet()) {
            // Jika mobile atau tablet, tampilkan halaman error
            return response()->view('admin.device-restricted', [], 403);
        }
        
        // Jika desktop, lanjutkan request
        return $next($request);
    }
}
