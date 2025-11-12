<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Admin</title>
    <link rel="icon" type="image/png" href="{{ route('logo.serve', ['filename' => 'favicons.png']) }}">
    
    {{-- Preload logo images untuk loading yang lebih cepat --}}
    @php
        $preloadLogos = [
            'favicons.png',
            'netral.png',
            'netral1.png',
            'senyum.png',
            'senyum1.png',
            'sedih.png',
            'sedih1.png',
            'lelah.png',
            'lelah1.png',
            'marah.png',
            'marah1.png'
        ];
    @endphp
    @foreach($preloadLogos as $logo)
        <link rel="preload" as="image" href="{{ route('logo.serve', ['filename' => $logo]) }}" fetchpriority="high">
    @endforeach

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
    
    @stack('scripts')
</body>
</html>