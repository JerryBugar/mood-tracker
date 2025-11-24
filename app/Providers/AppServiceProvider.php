<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }


        // Rate limiter untuk verifikasi kode perusahaan
        // 3 attempts per 15 menit berdasarkan IP address
        RateLimiter::for('verification', function (Request $request) {
            return Limit::perMinutes(15, 3)->by($request->ip());
        });

        // Rate limiter untuk admin login
        // 3 attempts per 15 menit berdasarkan IP address
        RateLimiter::for('admin-login', function (Request $request) {
            return Limit::perMinutes(15, 3)->by($request->ip());
        });
    }
}
