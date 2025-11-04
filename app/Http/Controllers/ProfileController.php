<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Menampilkan halaman profil
     */
    public function index()
    {
        return view('profile.index');
    }

    /**
     * Memperbarui informasi profil pengguna
     */
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'division' => 'nullable|string|max:255',
            'jenis_kelamin' => 'nullable|in:Laki-laki,Cewek',
        ]);

        $user = Auth::user();
        $user->update([
            'name' => $request->name,
            'division' => $request->division,
            'jenis_kelamin' => $request->jenis_kelamin,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'division' => $user->division,
                'jenis_kelamin' => $user->jenis_kelamin,
            ]
        ]);
    }
}