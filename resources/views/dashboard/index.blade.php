@extends('layouts.app')

@section('content')
<!-- Animated splash logo -->
<div id="splash-logo-container">
    <img src="{{ asset('logo/icon.jpeg') }}" alt="App Logo Splash" class="splash-logo-animated">
</div>

<!-- Main content, initially hidden -->
<div id="main-content-wrapper" class="hidden">
    <div class="d-flex flex-column align-items-center justify-content-center min-vh-100 text-center">
        <div class="account-icon" aria-label="Profil akun">
            <img src="{{ asset('logo/person.png') }}" alt="Ikon akun" />
        </div>
        <img id="final-logo-position" src="{{ asset('logo/icon.jpeg') }}" alt="Ikon utama aplikasi" class="center-icon img-fluid">
        <h1 class="hero-title mt-5 text-center">
            Selamat datang, <br> Tim Hebat Cerebrum!
        </h1>
        <a href="{{ url('/dashboard') }}" class="btn btn-lg tracking-btn mt-5 d-inline-flex align-items-center justify-content-center" role="button">
            <img src="{{ asset('logo/google.png') }}" alt="Google logo" class="me-2" style="height: 1.5em; width: auto;">
            <span>Login With Google</span>
        </a>
        <p class="tracking-subtext mt-5">
            <span>Bagikan mood hari ini,</span>
            <span>karena mood sehat bikin kerja makin hebat<img src="{{ asset('logo/love.png') }}" alt="Ikon hati"></span>
        </p>
    </div>
</div>
@endsection