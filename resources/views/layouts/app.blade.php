<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if(request()->path() === '/' || request()->path() === 'welcome')
    <meta name="description" content="Ceremood adalah aplikasi mood tracker dari Cerebrum untuk HRD, memantau kesejahteraan karyawan & mendukung keputusan strategis.">
    <meta name="keywords" content="ceremood, mood tracker, cerebrum, employee wellness, mood tracking, kesehatan mental, productivity, mood calendar, real-time notification">
    <meta name="author" content="Cerebrum">
    <title>Ceremood – Mood Tracker untuk Memahami Perasaan Karyawanmu</title>
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:title" content="Ceremood – Mood Tracker untuk Memahami Perasaan Karyawanmu">
    <meta property="og:description" content="Ceremood adalah aplikasi mood tracker dari Cerebrum untuk HRD, memantau kesejahteraan karyawan & mendukung keputusan strategis.">
    <meta property="og:image" content="{{ url(route('logo.serve', ['filename' => 'favicons.png'])) }}">
    <meta property="og:image:width" content="512">
    <meta property="og:image:height" content="512">
    <meta property="og:image:type" content="image/png">
    <meta property="og:site_name" content="Ceremood">
    
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ url('/') }}">
    <meta name="twitter:title" content="Ceremood – Mood Tracker untuk Memahami Perasaan Karyawanmu">
    <meta name="twitter:description" content="Ceremood adalah aplikasi mood tracker dari Cerebrum untuk HRD, memantau kesejahteraan karyawan & mendukung keputusan strategis.">
    <meta name="twitter:image" content="{{ url(route('logo.serve', ['filename' => 'favicons.png'])) }}">
    @else
    <title>{{ config('app.name', 'Ceremood') }}</title>
    @endif
    
    <!-- Favicon PNG untuk semua browser dan Google Search -->
    <!-- Route /favicon.ico akan serve PNG untuk kompatibilitas dengan Google Search -->
    <link rel="icon" type="image/png" href="{{ asset('logo/favicons.png') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('logo/favicons.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('logo/favicons.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('logo/favicons.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('logo/favicons.png') }}">

    <!-- PWA Meta Tags - Disabled untuk welcome page -->
    @if(request()->path() !== '/' && request()->path() !== 'welcome')
    <meta name="theme-color" content="#83282f">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'Ceremood') }}">
    <link rel="apple-touch-icon" href="{{ url(route('logo.serve', ['filename' => 'favicons.png'])) }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    @else
    <meta name="theme-color" content="#83282f">
    <!-- Manifest link dihapus untuk mencegah install prompt di welcome page -->
    @endif

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- VAPID Public Key untuk Push Notification -->
    @if(config('webpush.vapid.public_key'))
    <meta name="vapid-public-key" content="{{ config('webpush.vapid.public_key') }}">
    @endif
    
    @stack('styles')
    @stack('head')
</head>
<body class="antialiased">
    <div class="min-h-screen bg-gray-100">
        @yield('content')
    </div>
    
    <script>
        // Optimasi Turbo untuk mencegah reload yang tidak perlu
        document.addEventListener('turbo:load', function() {
            // Inisialisasi elemen-elemen Bootstrap setelah setiap Turbo load
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
        
        // Konfigurasi perilaku Turbo
        document.addEventListener('turbo:before-visit', function(event) {
            // Mencegah kunjungan ke URL yang sama (mencegah reload tidak perlu)
            if (event.detail.url === window.location.href) {
                event.preventDefault();
            }
        });
        
        // Menangani kondisi ketika Turbo memutuskan untuk melakukan full page reload
        document.addEventListener('turbo:submit-end', function(event) {
            if (event.detail.success) {
                // Jika submit form berhasil, kita bisa mencegah reload pada kondisi tertentu
            }
        });
    </script>
    
    <!-- PWA Service Worker Registration -->
    <script src="{{ asset('js/pwa.js') }}"></script>
    
    <!-- Push Notification Handler - Hanya untuk user yang sudah login dan verified -->
    @auth
    @if(auth()->user()->is_verified)
    <script src="{{ asset('js/push-notification.js') }}"></script>
    @endif
    @endauth
    
    @stack('scripts')
</body>
</html>
