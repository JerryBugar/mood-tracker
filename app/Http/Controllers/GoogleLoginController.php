<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;

class GoogleLoginController extends Controller
{
    public function redirect()
    {
        \Log::info('Google OAuth redirect initiated');
        Session::forget('google_user_data');
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        \Log::info('Google OAuth callback initiated');
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            \Log::info('Successfully retrieved user from Google', [
                'google_id' => $googleUser->getId(),
                'email' => $googleUser->getEmail()
            ]);

            // Cek jika user sudah ada DAN terverifikasi
            $user = User::where('google_id', $googleUser->getId())->where('is_verified', true)->first();
            \Log::info('Looking for existing verified user', [
                'google_id' => $googleUser->getId(),
                'found_user' => $user ? true : false,
                'user_verified' => $user ? $user->is_verified : null
            ]);

            if ($user) {
                \Log::info('Existing verified user logging in', ['user_id' => $user->id]);
                Auth::login($user);
                return redirect('/home'); // Langsung ke dashboard jika sudah terverifikasi
            }

            \Log::info('User not verified or not exist, redirecting to verification page', [
                'google_id' => $googleUser->getId(),
                'email' => $googleUser->getEmail()
            ]);

            // Jika belum, simpan data Google di session dan arahkan ke verifikasi
            Session::put('google_user_data', [
                'id' => $googleUser->getId(),
                'email' => $googleUser->getEmail(),
                'avatar' => $googleUser->getAvatar(),
            ]);

            return redirect()->route('verification.show');

        } catch (\Exception $e) {
            \Log::error('Google login callback error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // You can log the error or redirect to an error page
            return redirect('/')->with('error', 'Login with Google failed. Please try again.');
        }
    }
}