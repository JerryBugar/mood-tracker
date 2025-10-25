<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleLoginController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\MoodController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard.index');
});

Route::get('/auth/google/redirect', [GoogleLoginController::class, 'redirect']);
Route::get('/auth/google/callback', [GoogleLoginController::class, 'callback']);

Route::get('/home', function () {
    return view('home-page.index');
})->middleware('auth');

Route::get('/auth/verify', [VerificationController::class, 'show'])->name('verification.show');
Route::post('/auth/verify', [VerificationController::class, 'verify'])->name('verification.verify');

Route::get('/calendar', function () {
    return view('calendar.index');
});

Route::get('/notif', function () {
    return view('notif.index');
});

Route::get('/profile', function () {
    return view('profile.index');
});

// Routes untuk mood
Route::middleware('auth')->group(function () {
    Route::get('/mood/modal', [MoodController::class, 'showMoodModal'])->name('mood.modal');
    Route::post('/mood/save', [MoodController::class, 'saveMood'])->name('mood.save');
    Route::get('/mood/quote', [MoodController::class, 'getRandomQuote'])->name('mood.quote');
});

