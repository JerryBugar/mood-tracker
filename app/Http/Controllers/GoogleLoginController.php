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
        Session::forget('google_user_data');
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Cek jika user sudah ada DAN terverifikasi
            $user = User::where('google_id', $googleUser->getId())->where('is_verified', true)->first();

            if ($user) {
                Auth::login($user);
                return redirect('/home'); // Langsung ke dashboard jika sudah terverifikasi
            }

            // Jika belum, simpan data Google di session dan arahkan ke verifikasi
            Session::put('google_user_data', [
                'id' => $googleUser->getId(),
                'email' => $googleUser->getEmail(),
                'avatar' => $googleUser->getAvatar(),
            ]);

            return redirect()->route('verification.show');

        } catch (\Exception $e) {
            // You can log the error or redirect to an error page
            return redirect('/')->with('error', 'Login with Google failed. Please try again.');
        }
    }
}