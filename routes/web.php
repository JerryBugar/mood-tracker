<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleLoginController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\MoodController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\Mood\MoodRecordController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard.index');
});

Route::get('/auth/google/redirect', [GoogleLoginController::class, 'redirect']);
Route::get('/auth/google/callback', [GoogleLoginController::class, 'callback']);

// Route login mengarah ke Google OAuth redirect
Route::get('/login', [GoogleLoginController::class, 'redirect'])->name('login');

Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->middleware(['auth'])->name('logout');

Route::get('/home', [HomeController::class, 'index'])->middleware(['auth', 'verified']);

Route::get('/auth/verify', [VerificationController::class, 'show'])->name('verification.show');
Route::post('/auth/verify', [VerificationController::class, 'verify'])->name('verification.verify');

Route::get('/calendar', [CalendarController::class, 'index'])->middleware(['auth', 'verified'])->name('calendar.index');
Route::get('/calendar/day/{date}', [CalendarController::class, 'showDay'])->middleware(['auth', 'verified'])->name('calendar.day');

Route::get('/notif', function () {
    return view('notif.index');
})->middleware(['auth', 'verified']);

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
Route::prefix('admin')->group(function () {
    Route::get('/login', function () {
        return view('admin.login');
    })->name('admin.login');

    Route::post('/login', function (\Illuminate\Http\Request $request) {
        $adminUsername = env('ADMIN_USERNAME');
        $adminPassword = env('ADMIN_PASSWORD');

        if ($request->username === $adminUsername && $request->password === $adminPassword) {
            $request->session()->put('is_admin_authenticated', true);
            return redirect()->intended('/admin/dashboard');
        }

        return redirect()->back()->withErrors(['credentials' => 'Username atau password salah']);
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
    });

    Route::post('/logout', function (\Illuminate\Http\Request $request) {
        $request->session()->forget('is_admin_authenticated');
        return redirect('/admin/login');
    })->name('admin.logout');
});