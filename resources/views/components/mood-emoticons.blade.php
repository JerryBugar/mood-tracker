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
        .emoticons-wrapper {
            flex-wrap: nowrap !important;
            overflow-x: auto;
            overflow-y: visible;
            -webkit-overflow-scrolling: touch;
        }
        
        .emoticons-wrapper::-webkit-scrollbar {
            display: none;
        }
        
        .emoticons-wrapper {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        
        .mood-emoticons-grid > div > div {
            margin: 0 10px !important;
            flex-shrink: 0;
        }
        
        /* Untuk desktop yang lebih lebar */
        @media (min-width: 1200px) {
            .mood-emoticons-grid > div > div {
                margin: 0 20px !important;
            }
        }
        
        /* Untuk desktop yang sangat lebar */
        @media (min-width: 1600px) {
            .mood-emoticons-grid > div > div {
                margin: 0 40px !important;
            }
        }
        
        /* Untuk resolusi sekitar 1536px (desktop non-maximized) */
        @media (min-width: 1400px) and (max-width: 1599px) {
            .mood-emoticons-grid > div > div {
                margin: 0 90px !important;
            }
        }
        
        /* Pastikan container tidak overflow */
        .mood-emoticons-container {
            overflow-x: hidden;
            overflow-y: visible;
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

<div id="mood-emoticons-container" class="mood-emoticons-container" style="background-color: #d98695; border-radius: 15px; margin-top: 0px; text-align: center;">
    <h3 class="d-none d-sm-block mb-0" style="color: white;">Bagaimana kabarmu hari ini?</h3>
    <h5 class="d-block d-sm-none mb-0" style="color: white;">Bagaimana kabarmu hari ini?</h5>

    <div class="mood-emoticons-grid mt-3">
        <div class="d-flex justify-content-center align-items-center emoticons-wrapper" style="flex-wrap: wrap; margin: 0 -15px;">
            
            <x-mood-emoticon-item 
                mood="senyum" 
                title="Senyum" 
                :jenis-kelamin="Auth::check() ? Auth::user()->jenis_kelamin : ''" 
            />
            
            <x-mood-emoticon-item 
                mood="sedih" 
                title="Sedih" 
                :jenis-kelamin="Auth::check() ? Auth::user()->jenis_kelamin : ''" 
            />
            
            <x-mood-emoticon-item 
                mood="netral" 
                title="Netral" 
                :jenis-kelamin="Auth::check() ? Auth::user()->jenis_kelamin : ''" 
            />
            
            <x-mood-emoticon-item 
                mood="lelah" 
                title="Lelah" 
                :jenis-kelamin="Auth::check() ? Auth::user()->jenis_kelamin : ''" 
            />
            
            <x-mood-emoticon-item 
                mood="marah" 
                title="Marah" 
                :jenis-kelamin="Auth::check() ? Auth::user()->jenis_kelamin : ''" 
            />

        </div>
    </div>
</div>