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
        // Cek apakah ada file yang diupload tapi melebihi batas ukuran server
        if ($request->hasFile('avatar') && $request->file('avatar')->getError() !== UPLOAD_ERR_OK) {
            return response()->json([
                'success' => false,
                'message' => 'Ukuran file terlalu besar atau terjadi kesalahan upload.'
            ], 422);
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'division' => 'nullable|string|max:255',
            'role' => 'nullable|string|max:255',
            'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3072', // Validasi untuk avatar (3MB)
        ]);

        $user = Auth::user();
        
        // Jika ada file avatar yang diupload
        if ($request->hasFile('avatar')) {
            // Validasi file avatar
            // Tidak perlu melakukan validasi ulang karena sudah di validasi sebelumnya
            
            // Hapus avatar lama jika bukan avatar default dan bukan URL eksternal
            if ($user->avatar && !str_starts_with($user->avatar, 'http')) {
                // Jika avatar disimpan secara lokal, hapus file lama
                // Di sini kita mengasumsikan avatar disimpan dalam folder 'public/avatars'
                $oldAvatarPath = public_path($user->avatar);
                if (file_exists($oldAvatarPath)) {
                    if (!unlink($oldAvatarPath)) {
                        \Log::warning("Tidak bisa menghapus avatar lama: " . $oldAvatarPath);
                    }
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

        $updateData = [
            'name' => $request->name,
            'division' => $request->division,
            'role' => $request->role,
            'jenis_kelamin' => $jenisKelamin,
        ];
        
        // Hanya tambahkan field avatar ke update jika ada avatar baru
        if ($request->hasFile('avatar')) {
            $updateData['avatar'] = $user->avatar;
        }

        $user->update($updateData);

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