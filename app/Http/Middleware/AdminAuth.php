<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Periksa apakah session berisi informasi admin
        if ($request->session()->get('is_admin_authenticated')) {
            return $next($request);
        }
        
        // Redirect ke login admin jika tidak otentikasi
        return redirect('/admin/login');
    }
}