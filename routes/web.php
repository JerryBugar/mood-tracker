<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use App\Http\Controllers\Auth\GoogleLoginController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\MoodController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\Mood\MoodRecordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LogoController;

Route::get('/', function () {
    return view('welcome');
});

// Route untuk offline page
Route::get('/offline', function () {
    return response()->file(public_path('offline.html'));
})->name('offline');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Route untuk favicon.ico - Serve PNG langsung untuk Google Search
// Google Search akan mencari /favicon.ico, jadi kita serve PNG dari sini
Route::get('/favicon.ico', function () {
    $pngPath = public_path('logo/favicons.png');
    if (file_exists($pngPath)) {
        return response()->file($pngPath, [
            'Content-Type' => 'image/png', // Serve sebagai PNG
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
    abort(404);
});

// Route untuk serve logo dengan cache headers optimal
Route::get('/logo/{filename}', [LogoController::class, 'serve'])->where('filename', '[a-zA-Z0-9._-]+\.(png|jpeg|jpg)')->name('logo.serve');

Route::get('/auth/google/redirect', [GoogleLoginController::class, 'redirect']);
Route::get('/auth/google/callback', [GoogleLoginController::class, 'callback']);

// Route login mengarah ke Google OAuth redirect
Route::get('/login', [GoogleLoginController::class, 'redirect'])->name('login');

Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/dashboard');
})->middleware(['auth'])->name('logout');

Route::get('/home', [HomeController::class, 'index'])->middleware(['auth', 'verified'])->name('home');

Route::get('/auth/verify', [VerificationController::class, 'show'])->name('verification.show');
Route::post('/auth/verify', [VerificationController::class, 'verify'])->name('verification.verify');

Route::get('/calendar', [CalendarController::class, 'index'])->middleware(['auth', 'verified'])->name('calendar.index');
Route::get('/calendar/day/{date}', [CalendarController::class, 'showDay'])->middleware(['auth', 'verified'])->name('calendar.day');

Route::get('/notif', [App\Http\Controllers\NotificationController::class, 'index'])->middleware(['auth', 'verified'])->name('notif.index');
Route::post('/notif/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->middleware(['auth', 'verified'])->name('notif.read');
Route::post('/notif/read-all', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->middleware(['auth', 'verified'])->name('notif.read-all');
Route::delete('/notif/delete-all', [App\Http\Controllers\NotificationController::class, 'deleteAll'])->middleware(['auth', 'verified'])->name('notif.delete-all');
Route::post('/notif/push/subscribe', [App\Http\Controllers\NotificationController::class, 'subscribePush'])->middleware(['auth', 'verified'])->name('notif.push.subscribe');
Route::post('/notif/push/unsubscribe', [App\Http\Controllers\NotificationController::class, 'unsubscribePush'])->middleware(['auth', 'verified'])->name('notif.push.unsubscribe');
Route::get('/notif/push/status', [App\Http\Controllers\NotificationController::class, 'checkPushStatus'])->middleware(['auth', 'verified'])->name('notif.push.status');

Route::get('/profile', [ProfileController::class, 'index'])->middleware(['auth', 'verified'])->name('profile.index');
Route::put('/profile', [ProfileController::class, 'update'])->middleware(['auth', 'verified'])->name('profile.update');

// Routes untuk mood
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/mood/modal', [MoodController::class, 'showMoodModal'])->name('mood.modal');
    Route::post('/mood/save', [MoodController::class, 'saveMood'])->name('mood.save');
    Route::get('/mood/quote', [MoodController::class, 'getRandomQuote'])->name('mood.quote');

    // Route untuk pagination mood records - sekarang menggunakan MoodRecordController
    Route::get('/mood/records', [MoodRecordController::class, 'index'])->name('mood.records.index');
    Route::get('/mood/records/pagination', [MoodRecordController::class, 'pagination'])->name('mood.records.pagination');

    // Route lama untuk kompatibilitas - nanti bisa dihapus
    Route::get('/home/pagination', [HomeController::class, 'pagination'])->name('home.pagination');
});

// Routes untuk admin panel
Route::prefix('admin')->middleware(['admin.desktop'])->group(function () {
    Route::get('/login', function () {
        // Pass rate limit status ke view
        $key = 'admin-login:' . request()->ip();
        $isBlocked = RateLimiter::tooManyAttempts($key, 3);
        $secondsRemaining = $isBlocked ? RateLimiter::availableIn($key) : 0;
        $attempts = RateLimiter::attempts($key);
        
        return view('admin.login', [
            'rateLimitBlocked' => $isBlocked,
            'rateLimitSeconds' => $secondsRemaining,
            'rateLimitAttempts' => $attempts,
            'rateLimitRemaining' => max(0, 3 - $attempts)
        ]);
    })->name('admin.login');

    Route::post('/login', function (\Illuminate\Http\Request $request) {
        // Cek rate limit DI AWAL sebelum validasi apapun
        $key = 'admin-login:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            $minutes = max(1, ceil($seconds / 60));
            \Log::warning('Admin login rate limit exceeded - request blocked', [
                'ip' => $request->ip(),
                'username' => $request->username,
                'seconds_remaining' => $seconds
            ]);
            return redirect()->back()->withErrors([
                'credentials' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$minutes} menit."
            ]);
        }

        $adminUsername = env('ADMIN_USERNAME');
        $adminPassword = env('ADMIN_PASSWORD');

        if ($request->username === $adminUsername && $request->password === $adminPassword) {
            // Jika login berhasil, reset rate limiter untuk IP ini
            RateLimiter::clear('admin-login:' . $request->ip());
            
            // Clear intended URL untuk menghindari redirect ke URL yang tidak diinginkan
            $request->session()->forget('url.intended');
            $request->session()->put('is_admin_authenticated', true);
            return redirect('/admin/dashboard');
        }

        // Jika login gagal, hitung attempt (increment counter) - 15 menit = 900 detik
        RateLimiter::hit($key, 900);
        $attempts = RateLimiter::attempts($key);
        $remaining = 3 - $attempts;

        $errorMessage = 'Username atau password salah.';
        if ($remaining > 0) {
            $errorMessage .= " Sisa percobaan: {$remaining}.";
        } else {
            $seconds = RateLimiter::availableIn($key);
            $minutes = max(1, ceil($seconds / 60));
            $errorMessage = "Terlalu banyak percobaan login. Silakan coba lagi dalam {$minutes} menit.";
        }

        return redirect()->back()->withErrors(['credentials' => $errorMessage]);
    })->name('admin.authenticate');

    Route::middleware(['admin.auth'])->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');
        
        // Routes untuk tab dashboard
        Route::get('/dashboard/overview', [App\Http\Controllers\Admin\DashboardController::class, 'overviewTab'])->name('admin.dashboard.overview');
        Route::get('/dashboard/employees', [App\Http\Controllers\Admin\DashboardController::class, 'employeesTab'])->name('admin.dashboard.employees');
        Route::get('/dashboard/notifications', [App\Http\Controllers\Admin\DashboardController::class, 'notificationsTab'])->name('admin.dashboard.notifications');

        Route::get('/mood-monitoring', [App\Http\Controllers\Admin\DashboardController::class, 'moodMonitoring'])->name('admin.mood.monitoring');
        
        // Routes untuk bagian mood monitoring
        Route::get('/mood-monitoring/filters', [App\Http\Controllers\Admin\DashboardController::class, 'moodMonitoring'])->name('admin.mood-monitoring.filters');
        Route::get('/mood-monitoring/records', [App\Http\Controllers\Admin\DashboardController::class, 'moodMonitoring'])->name('admin.mood-monitoring.records');
        Route::get('/mood-monitoring/chart', [App\Http\Controllers\Admin\DashboardController::class, 'moodMonitoring'])->name('admin.mood-monitoring.chart');

        Route::get('/dashboard/chart-data', [App\Http\Controllers\Admin\DashboardController::class, 'getChartData'])->name('admin.dashboard.chart-data');

        Route::get('/user/{id}/detail', [App\Http\Controllers\Admin\DashboardController::class, 'getUserDetail'])->name('admin.user.detail');
        
        Route::post('/mood-record/{recordId}/response', [App\Http\Controllers\Admin\DashboardController::class, 'saveAdminResponse'])->name('admin.mood-record.response');
        
        Route::post('/notification/send', [App\Http\Controllers\Admin\DashboardController::class, 'sendNotification'])->name('admin.notification.send');
    });

    Route::post('/logout', function (\Illuminate\Http\Request $request) {
        $request->session()->forget('is_admin_authenticated');
        return redirect('/admin/login');
    })->name('admin.logout');
});