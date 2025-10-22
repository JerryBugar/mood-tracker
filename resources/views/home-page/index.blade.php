@extends('layouts.internal')

@section('main-content')
    <div class="container-fluid">
        @if (Auth::check())
            <h1 style="color: #82272c; margin-top: 20px;">Haloo, {{ explode(' ', Auth::user()->name)[0] }}</h1>
        @else
            <h1 style="color: #82272c; margin-top: 20px;">Welcome to Home Page!</h1>
        @endif

        <style>
            .emoticon-background {
                display: inline-block;
                background-color: rgba(255, 255, 255, 0.2);
                border-radius: 50%;
                padding: 10px;
                margin: 5px;
            }
        </style>
        
        <div class="mood-input-container" style="margin-bottom: 8px;">
            <div class="mood-input-box">
                @if (Auth::check() && Auth::user()->avatar)
                    <img src="{{ Auth::user()->avatar }}" alt="User Avatar" class="mood-avatar">
                @endif
                <div class="mood-text-content">
                    <h3 id="greeting-text"></h3>
                    <p class="mood-quote">Dibalik setiap kesulitan,<br>tersimpan sebuah kesempatan.</p>
                    <small class="mood-author">- Albert Einstein</small>
                </div>
            </div>
        </div>

        <div style="background-color: #d98695; border-radius: 15px; padding: 20px; margin-top: 0px; text-align: center;">
            <h3 class="d-none d-sm-block mb-0" style="color: white;">Bagaimana kabarmu hari ini?</h3>
            <h5 class="d-block d-sm-none mb-0" style="color: white;">Bagaimana kabarmu hari ini?</h6>
            
            <div class="mood-emoticons-grid mt-3">
                <div class="d-flex justify-content-around align-items-center" style="gap: 10px;">
                    <div class="d-md-none text-center">
                        <div class="emoticon-background">
                            <img src="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/netral1.png') : asset('logo/netral.png') }}" alt="Netral" class="mood-emoticon emoticon-clickable" style="width: 50px; height: 50px; transition: transform 0.2s ease;" onclick="this.style.transform='scale(1.2)'; setTimeout(() => this.style.transform='scale(1)', 300);">
                        </div>
                    </div>
                    <div class="d-none d-md-block text-center">
                        <div class="emoticon-background">
                            <img src="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/netral1.png') : asset('logo/netral.png') }}" alt="Netral" class="mood-emoticon emoticon-clickable" style="width: 70px; height: 70px; transition: transform 0.2s ease;" onclick="this.style.transform='scale(1.2)'; setTimeout(() => this.style.transform='scale(1)', 300);">
                        </div>
                    </div>
                    
                    <div class="d-md-none text-center">
                        <div class="emoticon-background">
                            <img src="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/senyum1.png') : asset('logo/senyum.png') }}" alt="Senyum" class="mood-emoticon emoticon-clickable" style="width: 50px; height: 50px; transition: transform 0.2s ease;" onclick="this.style.transform='scale(1.2)'; setTimeout(() => this.style.transform='scale(1)', 300);">
                        </div>
                    </div>
                    <div class="d-none d-md-block text-center">
                        <div class="emoticon-background">
                            <img src="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/senyum1.png') : asset('logo/senyum.png') }}" alt="Senyum" class="mood-emoticon emoticon-clickable" style="width: 70px; height: 70px; transition: transform 0.2s ease;" onclick="this.style.transform='scale(1.2)'; setTimeout(() => this.style.transform='scale(1)', 300);">
                        </div>
                    </div>
                    
                    <div class="d-md-none text-center">
                        <div class="emoticon-background">
                            <img src="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/sedih1.png') : asset('logo/sedih.png') }}" alt="Sedih" class="mood-emoticon emoticon-clickable" style="width: 50px; height: 50px; transition: transform 0.2s ease;" onclick="this.style.transform='scale(1.2)'; setTimeout(() => this.style.transform='scale(1)', 300);">
                        </div>
                    </div>
                    <div class="d-none d-md-block text-center">
                        <div class="emoticon-background">
                            <img src="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/sedih1.png') : asset('logo/sedih.png') }}" alt="Sedih" class="mood-emoticon emoticon-clickable" style="width: 70px; height: 70px; transition: transform 0.2s ease;" onclick="this.style.transform='scale(1.2)'; setTimeout(() => this.style.transform='scale(1)', 300);">
                        </div>
                    </div>
                    
                    <div class="d-md-none text-center">
                        <div class="emoticon-background">
                            <img src="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/nervous1.png') : asset('logo/nervous.png') }}" alt="Nervous" class="mood-emoticon emoticon-clickable" style="width: 50px; height: 50px; transition: transform 0.2s ease;" onclick="this.style.transform='scale(1.2)'; setTimeout(() => this.style.transform='scale(1)', 300);">
                        </div>
                    </div>
                    <div class="d-none d-md-block text-center">
                        <div class="emoticon-background">
                            <img src="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/nervous1.png') : asset('logo/nervous.png') }}" alt="Nervous" class="mood-emoticon emoticon-clickable" style="width: 70px; height: 70px; transition: transform 0.2s ease;" onclick="this.style.transform='scale(1.2)'; setTimeout(() => this.style.transform='scale(1)', 300);">
                        </div>
                    </div>
                    
                    <div class="d-md-none text-center">
                        <div class="emoticon-background">
                            <img src="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/marah1.png') : asset('logo/marah.png') }}" alt="Marah" class="mood-emoticon emoticon-clickable" style="width: 50px; height: 50px; transition: transform 0.2s ease;" onclick="this.style.transform='scale(1.2)'; setTimeout(() => this.style.transform='scale(1)', 300);">
                        </div>
                    </div>
                    <div class="d-none d-md-block text-center">
                        <div class="emoticon-background">
                            <img src="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/marah1.png') : asset('logo/marah.png') }}" alt="Marah" class="mood-emoticon emoticon-clickable" style="width: 70px; height: 70px; transition: transform 0.2s ease;" onclick="this.style.transform='scale(1.2)'; setTimeout(() => this.style.transform='scale(1)', 300);">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Fungsi untuk menentukan salam berdasarkan waktu
        function updateGreeting() {
            const now = new Date();
            const hours = now.getHours();
            
            let greeting = '';
            
            if (hours >= 4 && hours < 10) {
                greeting = 'Selamat Pagi';
            } else if (hours >= 10 && hours < 14) {
                greeting = 'Selamat Siang';
            } else if (hours >= 14 && hours < 18) {
                greeting = 'Selamat Sore';
            } else {
                greeting = 'Selamat Malam';
            }
            
            document.getElementById('greeting-text').textContent = greeting;
        }
        
        // Langsung eksekusi fungsi saat script dimuat untuk mendapatkan salam langsung
        updateGreeting();
        
        // Update salam setiap 15 detik agar tetap akurat
        setInterval(updateGreeting, 15000);
    </script>

@endsection
