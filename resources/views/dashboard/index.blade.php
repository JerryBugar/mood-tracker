@extends('layouts.app')

@push('head')
{{-- Preload hanya gambar yang digunakan di halaman ini --}}
<link rel="preload" as="image" href="{{ route('logo.serve', ['filename' => 'favicons.png']) }}" fetchpriority="high">
<link rel="preload" as="image" href="{{ route('logo.serve', ['filename' => 'google.png']) }}">
<link rel="preload" as="image" href="{{ route('logo.serve', ['filename' => 'love.png']) }}">
@endpush

@section('content')
<!-- Animated splash logo -->
<div id="splash-logo-container">
    <img src="{{ asset('logo/favicons.png') }}" alt="App Logo Splash" class="splash-logo-animated">
</div>

<!-- Main content, initially hidden -->
<div id="main-content-wrapper" class="hidden">
    <div class="d-flex flex-column align-items-center justify-content-center min-vh-100 text-center">
        <img id="final-logo-position" src="{{ asset('logo/favicons.png') }}" alt="Ikon utama aplikasi" class="center-icon img-fluid">
        <h1 class="hero-title mt-5 text-center">
            Selamat datang, <br> Tim Hebat Cerebrum!
        </h1>
        <a href="{{ url('/auth/google/redirect') }}" class="btn btn-lg tracking-btn mt-5 d-inline-flex align-items-center justify-content-center" role="button" data-turbo="false">
            <img src="{{ asset('logo/google.png') }}" alt="Google logo" class="me-2" style="height: 1.5em; width: auto;">
            <span>Login With Google</span>
        </a>
        <p class="tracking-subtext mt-5">
            <span>Bagikan mood hari ini,</span>
            <span>karena mood sehat bikin kerja makin hebat<img src="{{ asset('logo/love.png') }}" alt="Ikon hati"></span>
        </p>
    </div>
</div>

@if(Auth::check() && Auth::user() && Auth::user()->is_verified)
<script>
    // Jika user sudah login, langsung redirect ke homepage
    // Ini sebagai fallback jika server-side redirect tidak bekerja karena Turbo atau cache
    (function() {
        // Guard untuk mencegah redirect ganda yang bisa menyebabkan splash screen terload 2 kali
        const redirectKey = 'dashboard-redirect-executed';
        if (sessionStorage.getItem(redirectKey)) {
            return;
        }
        
        // Tandai redirect sudah dieksekusi
        sessionStorage.setItem(redirectKey, 'true');
        
        // Pastikan redirect bekerja di PWA dengan menggunakan window.location.replace
        // Ini akan memastikan redirect bekerja bahkan jika service worker meng-cache halaman
        const homeUrl = '{{ route("home") }}';
        
        // Gunakan setTimeout untuk memastikan script dieksekusi setelah DOM ready
        // Tapi jangan terlalu cepat agar tidak mengganggu splash screen
        const redirect = function() {
            // Hapus flag redirect setelah redirect selesai (untuk next visit)
            sessionStorage.removeItem(redirectKey);
            // Gunakan replace untuk mencegah back button kembali ke dashboard
            window.location.replace(homeUrl);
        };
        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                // Tunggu sedikit untuk memastikan tidak mengganggu splash screen
                setTimeout(redirect, 100);
            });
        } else {
            // DOM sudah ready, tunggu sedikit sebelum redirect
            setTimeout(redirect, 100);
        }
    })();
</script>
@endif
@endsection