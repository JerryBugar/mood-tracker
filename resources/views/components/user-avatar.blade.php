{{-- 
Komponen ini menampilkan avatar pengguna
Props:
- avatarUrl: URL dari avatar pengguna
- size: ukuran avatar (default: 70px)
--}}
@props([
    'avatarUrl' => '',
    'size' => '70px' // Default size
])

@if($avatarUrl)
    <img 
        src="{{ $avatarUrl }}" 
        alt="User Avatar" 
        class="mood-avatar mb-2" 
        style="border: 3px solid #ffffff; border-radius: 50%; width: {{ $size }}; height: {{ $size }}; object-fit: cover;"
    >
@endif