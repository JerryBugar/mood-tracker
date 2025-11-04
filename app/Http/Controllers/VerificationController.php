<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;

class VerificationController extends Controller
{
    public function show()
    {
        if (!Session::has('google_user_data')) {
            return redirect('/');
        }
        return view('auth.verify');
    }

    public function verify(Request $request)
    {
        if (!Session::has('google_user_data')) {
            \Log::info('No google_user_data in session, redirecting to home');
            return redirect('/');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:60', 'regex:/^[a-zA-Z\\s]+$/'],
            'division' => ['required', 'string', 'max:60'],
            'role' => ['required', 'string', 'max:60'],
            'jenis_kelamin' => 'required|string|in:Laki-laki,Perempuan',
            'company_code' => 'required|string',
        ],[
            'name.regex' => 'Nama Lengkap hanya boleh berisi huruf dan spasi.',
            'name.max' => 'Nama Lengkap tidak boleh lebih dari :max karakter.',
            'division.max' => 'Divisi tidak boleh lebih dari :max karakter.',
            'role.max' => 'Role / Jabatan tidak boleh lebih dari :max karakter.',
        ]);

        // Bandingkan kode yang diinput dengan yang ada di .env
        // Trim whitespace dan ubah ke huruf besar untuk perbandingan case-insensitive
        $inputCode = strtoupper(trim($request->company_code));
        $expectedCode = strtoupper(trim(env('COMPANY_VERIFICATION_CODE', '')));
        
        if ($inputCode !== $expectedCode) {
            \Log::info('Company code mismatch', [
                'input_raw' => $request->company_code, 
                'input_processed' => $inputCode, 
                'expected_raw' => env('COMPANY_VERIFICATION_CODE'), 
                'expected_processed' => $expectedCode
            ]);
            return back()->withErrors(['company_code' => 'Kode perusahaan tidak valid.'])->withInput();
        }

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