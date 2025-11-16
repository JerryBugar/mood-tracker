<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class VerificationController extends \App\Http\Controllers\Controller
{
    public function show()
    {
        if (!Session::has('google_user_data')) {
            return redirect('/');
        }
        
        // Pass rate limit status ke view
        $key = 'verification:' . request()->ip();
        $isBlocked = RateLimiter::tooManyAttempts($key, 3);
        $secondsRemaining = $isBlocked ? RateLimiter::availableIn($key) : 0;
        $attempts = RateLimiter::attempts($key);
        
        return view('auth.verify', [
            'rateLimitBlocked' => $isBlocked,
            'rateLimitSeconds' => $secondsRemaining,
            'rateLimitAttempts' => $attempts,
            'rateLimitRemaining' => max(0, 3 - $attempts)
        ]);
    }

    public function verify(Request $request)
    {
        if (!Session::has('google_user_data')) {
            \Log::info('No google_user_data in session, redirecting to home');
            return redirect('/');
        }

        // Cek rate limit DI AWAL sebelum validasi apapun
        $key = 'verification:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            $minutes = max(1, ceil($seconds / 60));
            \Log::warning('Verification rate limit exceeded - request blocked', [
                'ip' => $request->ip(),
                'seconds_remaining' => $seconds
            ]);
            return back()->withErrors([
                'company_code' => "Terlalu banyak percobaan. Silakan coba lagi dalam {$minutes} menit."
            ])->withInput();
        }

        $request->validate([
            'name' => ['required', 'string', 'max:60', 'regex:/^[a-zA-Z\\s]+$/'],
            'division' => 'required|string|in:Marketing,Product,IT,CDS,HRD',
            'role' => ['required', 'string', 'max:60'],
            'jenis_kelamin' => 'required|string|in:Laki-laki,Perempuan',
            'company_code' => 'required|string',
        ],[
            'name.regex' => 'Nama Lengkap hanya boleh berisi huruf dan spasi.',
            'name.max' => 'Nama Lengkap tidak boleh lebih dari :max karakter.',
            'division.required' => 'Divisi harus dipilih.',
            'division.in' => 'Divisi yang dipilih tidak valid.',
            'role.max' => 'Role / Jabatan tidak boleh lebih dari :max karakter.',
        ]);

        // Bandingkan kode yang diinput dengan yang ada di .env
        // Trim whitespace dan ubah ke huruf besar untuk perbandingan case-insensitive
        $inputCode = strtoupper(trim($request->company_code));
        $expectedCode = strtoupper(trim(env('COMPANY_VERIFICATION_CODE', '')));
        
        if ($inputCode !== $expectedCode) {
            // Hitung attempt (increment counter) - 15 menit = 900 detik
            RateLimiter::hit($key, 900);
            $attempts = RateLimiter::attempts($key);
            $remaining = 3 - $attempts;

            \Log::info('Company code mismatch', [
                'input_raw' => $request->company_code, 
                'input_processed' => $inputCode, 
                'expected_raw' => env('COMPANY_VERIFICATION_CODE'), 
                'expected_processed' => $expectedCode,
                'attempts' => $attempts,
                'remaining_attempts' => $remaining
            ]);

            $errorMessage = 'Kode perusahaan tidak valid.';
            if ($remaining > 0) {
                $errorMessage .= " Sisa percobaan: {$remaining}.";
            } else {
                $seconds = RateLimiter::availableIn($key);
                $minutes = max(1, ceil($seconds / 60));
                $errorMessage = "Terlalu banyak percobaan. Silakan coba lagi dalam {$minutes} menit.";
            }

            return back()->withErrors(['company_code' => $errorMessage])->withInput();
        }

        // Jika kode benar, reset rate limiter untuk IP ini
        RateLimiter::clear('verification:' . $request->ip());

        $googleUserData = Session::get('google_user_data');
        \Log::info('Processing verification for user', ['email' => $googleUserData['email']]);

        // Buat user baru atau update yang sudah ada, lalu tandai sebagai terverifikasi
        $user = User::updateOrCreate(
            ['email' => $googleUserData['email']],
            [
                'name' => $request->name,
                'google_id' => $googleUserData['id'],
                'avatar' => $googleUserData['avatar'],
                'password' => Hash::make(now()),
                'division' => $request->division,
                'role' => $request->role,
                'jenis_kelamin' => $request->jenis_kelamin,
                'is_verified' => true,
            ]
        );

        \Log::info('User verification status updated', ['user_id' => $user->id, 'is_verified' => $user->is_verified]);

        Session::forget('google_user_data');
        
        // Logout terlebih dahulu untuk memastikan sesi bersih
        Auth::logout();
        
        // Login user dengan data terbaru
        Auth::login($user);
        \Log::info('User logged in after verification', ['user_id' => $user->id, 'is_verified' => $user->is_verified]);

        // Buat sesi baru untuk memastikan status terbaru terbaca
        $request->session()->regenerate();
        
        \Log::info('Session regenerated after login', ['user_id' => $user->id, 'session_id' => session()->getId()]);

        // Untuk memastikan redirect bekerja dengan baik di Turbo, kita kembalikan 
        // ke pendekatan sebelumnya yang terbukti berhasil
        return redirect('/home');
    }
}