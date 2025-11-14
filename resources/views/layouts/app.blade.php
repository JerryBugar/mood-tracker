<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" type="image/png" href="{{ route('logo.serve', ['filename' => 'favicons.png']) }}">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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
                console.log('Form submission successful');
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
