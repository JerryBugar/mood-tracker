{{-- 
Komponen ini menampilkan avatar pengguna
Props:
- avatarUrl: URL dari avatar pengguna
- jenisKelamin: Jenis kelamin pengguna ('Laki-laki', 'Perempuan', etc.)
- size: ukuran avatar (default: 70px)
--}}
@props([
    'avatarUrl' => '',
    'jenisKelamin' => '',
    'size' => '70px' // Default size
])

@php
    // Gunakan avatar yang diupload jika tersedia
    $avatarToShow = $avatarUrl;
    
    // Jika tidak ada avatar yang diupload, tentukan avatar berdasarkan jenis kelamin
    if (!$avatarToShow) {
        // Atur avatar default berdasarkan jenis kelamin menggunakan logika yang konsisten 
        // dengan mood-emoticon-item.blade.php
        $isFemale = ($jenisKelamin === 'Perempuan' || $jenisKelamin === 'Cewek');
        
        // Secara default, gunakan avatar laki-laki
        $avatarToShow = asset('logo/netral.png');
        
        // Jika perempuan, gunakan avatar perempuan
        if ($isFemale) {
            $avatarToShow = asset('logo/netral1.png');
        }
    }
@endphp

<img 
    src="{{ $avatarToShow }}" 
    alt="User Avatar" 
    class="mood-avatar mb-2" 
    style="border: 3px solid #ffffff; border-radius: 50%; width: {{ $size }}; height: {{ $size }}; object-fit: cover;"
>