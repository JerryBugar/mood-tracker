@extends('layouts.internal')

@section('main-content')
    <div class="container-fluid">
        @if (Auth::check())
            <h1 style="color: #82272c; margin-top: 20px;">Haloo, {{ explode(' ', Auth::user()->name)[0] }}</h1>
        @else
            <h1 style="color: #82272c; margin-top: 20px;">Welcome to Home Page!</h1>
        @endif

        <div class="mood-input-container">
            <div class="mood-input-box">
                @if (Auth::check() && Auth::user()->avatar)
                    <img src="{{ Auth::user()->avatar }}" alt="User Avatar" class="mood-avatar">
                @endif
                <div class="mood-text-content">
                    <h3>Selamat Pagi</h3>
                    <p class="mood-quote">Dibalik setiap kesulitan,<br>tersimpan sebuah kesempatan.</p>
                    <small class="mood-author">- Albert Einstein</small>
                </div>
            </div>
        </div>

        <div style="background-color: #d98695; border-radius: 15px; padding: 20px; margin-top: 20px; text-align: center;">
            <h3 style="font-weight: bold; color: white;">Bagaimana kabarmu hari ini?</h3>
        </div>
    </div>


@endsection
