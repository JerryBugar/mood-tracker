<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Helpers\TurboStreamHelper;

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
        
        // Refresh user data dari database
        $user->refresh();

        // Jika request Turbo Stream, return Turbo Stream response
        $acceptHeader = $request->header('Accept', '');
        if (strpos($acceptHeader, 'text/vnd.turbo-stream.html') !== false) {
            // Update profile content
            $profileContent = view('profile._partials.profile_content')->render();
            
            // Update form modal dengan data terbaru
            $formContent = view('profile._partials.profile_form')->render();
            
            // Gabungkan stream untuk update profile dan form
            $streams = [
                TurboStreamHelper::replace('profile-content', $profileContent),
                TurboStreamHelper::replace('edit-profile-form-container', $formContent)
            ];
            
            // Update emoticons di homepage jika container ada (user sedang di homepage)
            // Turbo Stream akan otomatis skip jika target tidak ada
            $emoticonsContent = view('components.mood-emoticons')->render();
            $streams[] = TurboStreamHelper::replace('mood-emoticons-container', $emoticonsContent);
            
            return response(
                TurboStreamHelper::combine($streams),
                200,
                ['Content-Type' => 'text/vnd.turbo-stream.html']
            );
        }

        // Fallback ke JSON response
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