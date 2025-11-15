<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Admin</title>
    <link rel="icon" type="image/png" href="{{ route('logo.serve', ['filename' => 'favicons.png']) }}">

    <!-- PWA Meta Tags - Disabled untuk admin pages -->
    <!-- Admin pages tidak boleh diinstall sebagai PWA -->
    <meta name="theme-color" content="#6366f1">
    <!-- Manifest link dihapus untuk mencegah install prompt di admin pages -->

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    @stack('styles')
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen">
        <!-- Header Admin -->
        <header class="bg-white shadow-sm">
            <div class="container-fluid px-4">
                <div class="d-flex justify-content-between align-items-center py-3">
                    <h1 class="h4 mb-0 text-gray-800">{{ config('app.name', 'Laravel') }} - Admin Panel</h1>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main>
            @yield('main-content')
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- PWA Service Worker Registration - Disabled untuk admin pages -->
    <!-- Admin pages tidak boleh menggunakan PWA features -->
    <script>
        // Service Worker tetap didaftarkan untuk offline support, tapi install prompt dinonaktifkan
        if ('serviceWorker' in navigator && !window.location.pathname.startsWith('/admin')) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(function(registration) {
                        console.log('[PWA] Service Worker registered successfully:', registration.scope);
                    })
                    .catch(function(error) {
                        console.error('[PWA] Service Worker registration failed:', error);
                    });
            });
        }
    </script>
    
    @stack('scripts')
</body>
</html>