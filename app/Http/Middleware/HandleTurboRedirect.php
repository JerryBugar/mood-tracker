<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleTurboRedirect
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Jika response adalah redirect dan request dari Turbo
        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            $isTurboRequest = $request->header('Turbo-Frame') || 
                             $request->header('Accept') === 'text/vnd.turbo-stream.html' ||
                             strpos($request->header('Accept', ''), 'text/vnd.turbo-stream.html') !== false;

            if ($isTurboRequest) {
                // Tambahkan header Turbo-Location untuk memberitahu Turbo melakukan full page redirect
                $response->headers->set('Turbo-Location', $response->getTargetUrl());
            }
        }

        return $response;
    }
}
