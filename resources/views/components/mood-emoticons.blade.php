<style>
    .mood-emoticons-container {
        padding: 15px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        border: 1px solid #e0e0e0;
        margin-bottom: 20px;
    }

    .emoticon-background {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background-color: #f0f0f068;
        display: flex;
        justify-content: center;
        align-items: center;
        border: 2px solid transparent;
        /* 2. TAMBAHKAN INI: Transisi untuk hover dan animasi */
        transition: transform 0.2s ease-out, border-color 0.2s ease-out;
    }

    .emoticon-link {
        display: inline-block;
        text-decoration: none;
    }

    /* 1. TAMBAHKAN INI: Definisi animasi float */
    @keyframes floatAnimation {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-8px); /* Bergerak ke atas 8px */
        }
    }

    /* 3. TAMBAHKAN INI: Efek hover biasa (sedikit terangkat) */
    .emoticon-link:hover .emoticon-background {
        transform: translateY(-4px);
        border-color: #d98695; /* Warna border saat di-hover */
    }

    /* 4. TAMBAHKAN INI: Animasi float SAAT DIKLIK (selama Turbo memuat) */
    .emoticon-link[aria-busy="true"] .emoticon-background {
        animation: floatAnimation 1.2s ease-in-out infinite;
        /* Pastikan border tetap ada selama loading */
        border-color: #d98695;
    }

    .mood-emoticons-grid > div > div {
        margin: 0 5px;
    }

    @media (min-width: 768px) {
        .mood-emoticons-grid > div > div {
            margin: 0 90px !important;
        }
    }

    @media (max-width: 767px) {
        .mood-emoticons-grid > div > div {
            margin: 0 3px !important;
        }
        
        .emoticon-background {
            width: 60px;
            height: 60px;
        }
        
        .mood-emoticons-container {
            padding: 15px 10px;
        }
    }
    
</style>

<div class="mood-emoticons-container" style="background-color: #d98695; border-radius: 15px; margin-top: 0px; text-align: center;">
    <h3 class="d-none d-sm-block mb-0" style="color: white;">Bagaimana kabarmu hari ini?</h3>
    <h5 class="d-block d-sm-none mb-0" style="color: white;">Bagaimana kabarmu hari ini?</h5>

    <div class="mood-emoticons-grid mt-3">
        <div class="d-flex justify-content-center align-items-center" style="flex-wrap: nowrap; margin: 0 -15px;">
            
            {{-- Senyum --}}
            <div class="text-center mx-2 mx-md-5">
                <a href="{{ route('mood.modal', ['mood' => 'senyum']) }}" data-turbo-frame="mood_modal_content" class="emoticon-link">
                    <div class="emoticon-background">
                        {{-- HAPUS 'transition' dari inline style --}}
                        <img src="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/senyum1.png') : asset('logo/senyum.png') }}" alt="Senyum" class="mood-emoticon" data-mood="senyum" style="width: 50px; height: 50px; display: block;"
                             srcset="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/senyum1.png') : asset('logo/senyum.png') }} 1x, {{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/senyum1.png') : asset('logo/senyum.png') }} 2x">
                    </div>
                </a>
            </div>

            {{-- Sedih --}}
            <div class="text-center mx-2 mx-md-5">
                <a href="{{ route('mood.modal', ['mood' => 'sedih']) }}" data-turbo-frame="mood_modal_content" class="emoticon-link">
                    <div class="emoticon-background">
                        {{-- HAPUS 'transition' dari inline style --}}
                        <img src="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/sedih1.png') : asset('logo/sedih.png') }}" alt="Sedih" class="mood-emoticon" data-mood="sedih" style="width: 50px; height: 50px; display: block;"
                             srcset="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/sedih1.png') : asset('logo/sedih.png') }} 1x, {{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/sedih1.png') : asset('logo/sedih.png') }} 2x">
                    </div>
                </a>
            </div>

            {{-- Netral --}}
            <div class="text-center mx-2 mx-md-5">
                <a href="{{ route('mood.modal', ['mood' => 'netral']) }}" data-turbo-frame="mood_modal_content" class="emoticon-link">
                    <div class="emoticon-background">
                        {{-- HAPUS 'transition' dari inline style --}}
                        <img src="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/netral1.png') : asset('logo/netral.png') }}" alt="Netral" class="mood-emoticon" data-mood="netral" style="width: 50px; height: 50px; display: block;"
                             srcset="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/netral1.png') : asset('logo/netral.png') }} 1x, {{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/netral1.png') : asset('logo/netral.png') }} 2x">
                    </div>
                </a>
            </div>

            {{-- Lelah --}}
            <div class="text-center mx-2 mx-md-5">
                <a href="{{ route('mood.modal', ['mood' => 'lelah']) }}" data-turbo-frame="mood_modal_content" class="emoticon-link">
                    <div class="emoticon-background">
                        {{-- HAPUS 'transition' dari inline style --}}
                        <img src="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/lelah1.png') : asset('logo/lelah.png') }}" alt="Lelah" class="mood-emoticon" data-mood="lelah" style="width: 50px; height: 50px; display: block;"
                             srcset="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/lelah1.png') : asset('logo/lelah.png') }} 1x, {{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/lelah1.png') : asset('logo/lelah.png') }} 2x">
                    </div>
                </a>
            </div>

            {{-- Marah --}}
            <div class="text-center mx-2 mx-md-5">
                <a href="{{ route('mood.modal', ['mood' => 'marah']) }}" data-turbo-frame="mood_modal_content" class="emoticon-link">
                    <div class="emoticon-background">
                        {{-- HAPUS 'transition' dari inline style --}}
                        <img src="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/marah1.png') : asset('logo/marah.png') }}" alt="Marah" class="mood-emoticon" data-mood="marah" style="width: 50px; height: 50px; display: block;"
                             srcset="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/marah1.png') : asset('logo/marah.png') }} 1x, {{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/marah1.png') : asset('logo/marah.png') }} 2x">
                    </div>
                </a>
            </div>

        </div>
    </div>
</div>