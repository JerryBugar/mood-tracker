@extends('layouts.app')

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
        <a href="{{ url('/auth/google/redirect') }}" class="btn btn-lg tracking-btn mt-5 d-inline-flex align-items-center justify-content-center" role="button">
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
        window.location.href = '{{ route("home") }}';
    })();
</script>
@endif
@endsection