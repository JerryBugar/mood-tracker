<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleLoginController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\MoodController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CalendarController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard.index');
});

Route::get('/auth/google/redirect', [GoogleLoginController::class, 'redirect']);
Route::get('/auth/google/callback', [GoogleLoginController::class, 'callback']);

Route::get('/home', [HomeController::class, 'index'])->middleware(['auth', 'verified']);

Route::get('/auth/verify', [VerificationController::class, 'show'])->name('verification.show');
Route::post('/auth/verify', [VerificationController::class, 'verify'])->name('verification.verify');

Route::get('/calendar', [CalendarController::class, 'index'])->middleware(['auth', 'verified'])->name('calendar.index');
Route::get('/calendar/day/{date}', [CalendarController::class, 'showDay'])->middleware(['auth', 'verified'])->name('calendar.day');

Route::get('/notif', function () {
    return view('notif.index');
})->middleware(['auth', 'verified']);

Route::get('/profile', function () {
    return view('profile.index');
})->middleware(['auth', 'verified']);

// Routes untuk mood
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/mood/modal', [MoodController::class, 'showMoodModal'])->name('mood.modal');
    Route::post('/mood/save', [MoodController::class, 'saveMood'])->name('mood.save');
    Route::get('/mood/quote', [MoodController::class, 'getRandomQuote'])->name('mood.quote');
    
    // Route untuk pagination mood records
    Route::get('/home/pagination', [HomeController::class, 'pagination'])->name('home.pagination');
});

