<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (Auth::check()) {
            $user = Auth::user();
            \Log::info('EnsureUserIsVerified middleware check', [
                'user_id' => $user->id, 
                'is_verified' => $user->is_verified,
                'request_path' => $request->path()
            ]);
            
            // Check if user is verified
            if (!$user->is_verified) {
                // Jangan redirect jika sudah di halaman verification atau auth
                if ($request->is('auth/*') || $request->is('logout')) {
                    return $next($request);
                }

                \Log::warning('User not verified, redirecting to verification page', [
                    'user_id' => $user->id,
                    'is_verified' => $user->is_verified
                ]);
                
                // Jika tidak ada google_user_data di session, logout user dan redirect ke login
                if (!Session::has('google_user_data')) {
                    \Log::warning('User not verified and no google_user_data, logging out', [
                        'user_id' => $user->id
                    ]);
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return redirect('/')->with('error', 'Silakan login ulang untuk melanjutkan verifikasi.');
                }
                
                // If user is not verified, redirect to verification page
                return redirect()->route('verification.show');
            }
        } else {
            \Log::info('User not authenticated, proceeding without verification check');
        }

        return $next($request);
    }
}