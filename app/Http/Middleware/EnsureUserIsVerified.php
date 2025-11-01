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
                \Log::warning('User not verified, redirecting to verification page', [
                    'user_id' => $user->id,
                    'is_verified' => $user->is_verified
                ]);
                // If user is not verified, redirect to verification page
                return redirect()->route('verification.show');
            }
        } else {
            \Log::info('User not authenticated, proceeding without verification check');
        }

        return $next($request);
    }
}