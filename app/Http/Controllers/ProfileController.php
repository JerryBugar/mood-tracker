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
            'role' => 'nullable|string|max:255',
            'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi untuk avatar
        ]);

        $user = Auth::user();
        
        // Jika ada file avatar yang diupload
        if ($request->hasFile('avatar')) {
            // Validasi file avatar
            $request->validate([
                'avatar' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
            
            // Hapus avatar lama jika bukan avatar default
            if ($user->avatar && !str_starts_with($user->avatar, 'http')) {
                // Jika avatar disimpan secara lokal, hapus file lama
                // Di sini kita mengasumsikan avatar disimpan dalam folder 'public/avatars'
                if (file_exists(public_path($user->avatar))) {
                    unlink(public_path($user->avatar));
                }
            }
            
            // Simpan avatar baru
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = '/storage/' . $avatarPath;
        }

        // Konversi nilai 'Cewek' menjadi 'Perempuan' untuk konsistensi
        $jenisKelamin = $request->jenis_kelamin;
        if ($jenisKelamin === 'Cewek') {
            $jenisKelamin = 'Perempuan';
        }

        $user->update([
            'name' => $request->name,
            'division' => $request->division,
            'role' => $request->role,
            'jenis_kelamin' => $jenisKelamin,
            'avatar' => $user->avatar, // Simpan avatar baru jika ada
        ]);

        // Karena modal menggunakan data-turbo-permanent, kembalikan response JSON
        // agar bisa diproses oleh JavaScript untuk menutup modal dan update UI
        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'division' => $user->division,
                'role' => $user->role,
                'jenis_kelamin' => $user->jenis_kelamin,
                'avatar' => $user->avatar,
            ]
        ]);
    }
}