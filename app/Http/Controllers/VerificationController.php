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
            return redirect('/');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'division' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'jenis_kelamin' => 'required|string|in:Cowok,Cewek',
            'company_code' => 'required|string',
        ]);

        // Bandingkan kode yang diinput dengan yang ada di .env
        if ($request->company_code !== env('COMPANY_VERIFICATION_CODE')) {
            return back()->withErrors(['company_code' => 'Kode perusahaan tidak valid.'])->withInput();
        }

        $googleUserData = Session::get('google_user_data');

        // Buat user baru atau update yang sudah ada, lalu tandai sebagai terverifikasi
        $user = User::updateOrCreate(
            ['email' => $googleUserData['email']],
            [
                'name' => $request->name,
                'google_id' => $googleUserData['id'],
                'password' => Hash::make(now()),
                'division' => $request->division,
                'role' => $request->role,
                'jenis_kelamin' => $request->jenis_kelamin,
                'is_verified' => true,
            ]
        );

        Session::forget('google_user_data');
        Auth::login($user);

        return redirect('/dashboard');
    }
}