@extends('layouts.app')

@push('head')
<link rel="preload" as="image" href="{{ route('logo.serve', ['filename' => 'favicons.png']) }}" fetchpriority="high">
@endpush

@push('styles')
<style>
    /* Reset untuk welcome page - hapus padding bottom dari app.css */
    /* Padding bottom 80px dari app.css untuk bottom nav tidak diperlukan di welcome page */
    /* Hanya berlaku untuk welcome page dengan selector yang lebih spesifik */
    body.welcome-page {
        padding-bottom: 0 !important;
        overflow-x: hidden;
    }

    body.welcome-page .min-h-screen {
        padding: 0 !important;
        margin: 0 !important;
        height: 100vh;
        overflow: hidden;
    }

    .landing-page {
        height: 100vh;
        background: linear-gradient(135deg, #ffffff 0%, #fff5f7 100%);
        position: relative;
        overflow-x: hidden;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        scroll-behavior: smooth;
    }

    /* Smooth scroll untuk anchor links */
    html {
        scroll-behavior: smooth;
    }

    .landing-page::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(217, 134, 149, 0.15) 0%, transparent 70%);
        border-radius: 50%;
        z-index: 0;
    }

    .landing-page::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -10%;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(131, 40, 47, 0.1) 0%, transparent 70%);
        border-radius: 50%;
        z-index: 0;
    }

    .landing-content {
        position: relative;
        z-index: 1;
        padding: 60px 20px;
        max-width: 1200px;
        margin: 0 auto;
    }

    .hero-section {
        text-align: center;
        margin-bottom: 80px;
    }

    .logo-container {
        margin-bottom: 30px;
        animation: fadeInDown 0.8s ease-out;
    }

    .logo-container img {
        width: clamp(120px, 25vw, 180px);
        height: auto;
        filter: drop-shadow(0 4px 12px rgba(131, 40, 47, 0.2));
    }

    .hero-title {
        color: #82242d;
        font-size: clamp(32px, 7vw, 56px);
        font-weight: 700;
        line-height: 1.2;
        margin-bottom: 20px;
        animation: fadeInUp 0.8s ease-out 0.2s both;
    }

    .hero-subtitle {
        color: #6c757d;
        font-size: clamp(18px, 4vw, 24px);
        font-weight: 400;
        margin-bottom: 40px;
        animation: fadeInUp 0.8s ease-out 0.4s both;
    }

    .cta-button {
        background: linear-gradient(135deg, #83282f 0%, #dc3545 100%);
        border: none;
        border-radius: 50px;
        color: #ffffff;
        font-size: clamp(16px, 3.5vw, 20px);
        font-weight: 600;
        padding: 16px 48px;
        box-shadow: 0 8px 24px rgba(131, 40, 47, 0.3);
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
        animation: fadeInUp 0.8s ease-out 0.6s both;
    }

    .cta-button:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 32px rgba(131, 40, 47, 0.4);
        color: #ffffff;
    }

    .cta-button:active {
        transform: translateY(-1px);
    }

    .features-section {
        margin-top: 100px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 40px;
        animation: fadeInUp 0.8s ease-out 0.8s both;
        scroll-margin-top: 20px;
    }

    .feature-card {
        background: #ffffff;
        border-radius: 20px;
        padding: 40px 30px;
        text-align: center;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .feature-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 8px 30px rgba(217, 134, 149, 0.2);
        border-color: #d98695;
    }

    .feature-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 20px;
        background: linear-gradient(135deg, #d98695 0%, #83282f 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        color: #ffffff;
        box-shadow: 0 4px 16px rgba(217, 134, 149, 0.3);
    }

    .feature-title {
        color: #82242d;
        font-size: 22px;
        font-weight: 600;
        margin-bottom: 12px;
    }

    .feature-description {
        color: #6c757d;
        font-size: 16px;
        line-height: 1.6;
    }

    .about-section {
        margin-top: 100px;
        text-align: center;
        padding: 60px 20px;
        background: linear-gradient(135deg, rgba(217, 134, 149, 0.1) 0%, rgba(131, 40, 47, 0.05) 100%);
        border-radius: 30px;
        animation: fadeInUp 0.8s ease-out 1s both;
        scroll-margin-top: 20px;
    }

    .about-title {
        color: #82242d;
        font-size: clamp(28px, 6vw, 40px);
        font-weight: 700;
        margin-bottom: 20px;
    }

    .about-text {
        color: #495057;
        font-size: clamp(16px, 3.5vw, 18px);
        line-height: 1.8;
        max-width: 800px;
        margin: 0 auto;
    }

    .cerebrum-badge {
        display: inline-block;
        background: linear-gradient(135deg, #83282f 0%, #dc3545 100%);
        color: #ffffff;
        padding: 8px 20px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
        margin-top: 20px;
        box-shadow: 0 4px 12px rgba(131, 40, 47, 0.3);
    }

    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .footer-section {
        margin-top: 120px;
        padding: 60px 20px 30px;
        padding-bottom: 30px;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.95) 0%, #ffffff 100%);
        border-top: 1px solid rgba(217, 134, 149, 0.2);
        animation: fadeInUp 0.8s ease-out 1.2s both;
    }

    .footer-content {
        max-width: 1200px;
        margin: 0 auto;
    }

    .footer-main {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 60px;
        margin-bottom: 40px;
    }

    .footer-brand {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .footer-logo-img {
        width: 60px;
        height: auto;
        filter: drop-shadow(0 2px 8px rgba(131, 40, 47, 0.2));
        margin-bottom: 8px;
    }

    .footer-brand-title {
        color: #82242d;
        font-size: 24px;
        font-weight: 700;
        margin: 0;
        line-height: 1.2;
    }

    .footer-brand-tagline {
        color: #6c757d;
        font-size: 14px;
        font-weight: 400;
        margin: 0;
        line-height: 1.5;
    }

    .footer-links {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 40px;
    }

    .footer-column {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .footer-column-title {
        color: #82242d;
        font-size: 16px;
        font-weight: 600;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .footer-link-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .footer-link {
        color: #495057;
        font-size: 14px;
        text-decoration: none;
        transition: color 0.2s ease;
        display: inline-block;
    }

    .footer-link:hover {
        color: #83282f;
        text-decoration: underline;
    }

    .footer-link:focus {
        outline: 2px solid #83282f;
        outline-offset: 2px;
        border-radius: 2px;
    }

    .footer-link-text {
        color: #6c757d;
        font-size: 14px;
        line-height: 1.6;
        margin: 0;
    }

    .footer-divider {
        width: 100%;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(217, 134, 149, 0.3), transparent);
        margin: 40px 0 30px;
    }

    .footer-bottom {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }

    .footer-copyright {
        color: #6c757d;
        font-size: 13px;
        line-height: 1.6;
        margin: 0;
    }

    .footer-copyright p {
        margin: 0;
        color: #495057;
    }

    .footer-company {
        color: #adb5bd;
        font-size: 12px;
        margin-top: 4px;
    }

    .footer-meta {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .footer-version {
        color: #adb5bd;
        font-size: 12px;
        margin: 0;
    }

    @media (max-width: 768px) {
        .landing-content {
            padding: 40px 15px;
        }

        .features-section {
            grid-template-columns: 1fr;
            gap: 30px;
            margin-top: 60px;
        }

        .about-section {
            margin-top: 60px;
            padding: 40px 20px;
        }

        .footer-section {
            margin-top: 80px;
            padding: 40px 20px 20px;
        }

        .footer-main {
            grid-template-columns: 1fr;
            gap: 40px;
        }

        .footer-links {
            grid-template-columns: 1fr;
            gap: 30px;
        }

        .footer-bottom {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .footer-brand-title {
            font-size: 20px;
        }

        .footer-brand-tagline {
            font-size: 13px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Tambahkan class ke body untuk styling khusus welcome page
    (function() {
        document.body.classList.add('welcome-page');
    })();
    
    // Smooth scroll untuk anchor links dengan offset
    document.addEventListener('DOMContentLoaded', function() {
        const links = document.querySelectorAll('a[href^="#"]');
        
        links.forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                
                // Skip jika href adalah # saja atau empty
                if (href === '#' || !href) {
                    return;
                }
                
                // Skip jika link ke dashboard
                if (href.includes('dashboard')) {
                    return;
                }
                
                e.preventDefault();
                
                const targetId = href.substring(1);
                const targetElement = document.getElementById(targetId);
                
                if (targetElement) {
                    const landingPage = document.querySelector('.landing-page');
                    const targetPosition = targetElement.offsetTop - 20; // Offset 20px dari atas
                    
                    landingPage.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });
    });
    
    // Hapus class welcome-page saat navigasi keluar (untuk cleanup)
    document.addEventListener('turbo:before-visit', function() {
        document.body.classList.remove('welcome-page');
    });
</script>
@endpush

@section('content')
<div class="landing-page">
    <div class="landing-content">
        <!-- Hero Section -->
        <div class="hero-section">
            <div class="logo-container">
                <img src="{{ route('logo.serve', ['filename' => 'favicons.png']) }}" alt="Ceremood Logo">
            </div>
            <h1 class="hero-title">Ceremood</h1>
            <p class="hero-subtitle">Mood Tracker untuk Tim Hebat Cerebrum</p>
            <a href="{{ route('dashboard') }}" class="cta-button">
                Mulai Sekarang
            </a>
        </div>

        <!-- Features Section -->
        <section id="fitur" class="features-section">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="bi bi-emoji-smile"></i>
                </div>
                <h3 class="feature-title">Track Mood Harian</h3>
                <p class="feature-description">
                    Catat perasaanmu setiap hari dengan mudah. Pilih dari berbagai emoji yang mewakili mood kamu.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <h3 class="feature-title">Kalender Mood</h3>
                <p class="feature-description">
                    Lihat riwayat mood kamu dalam bentuk kalender yang mudah dipahami dan diakses kapan saja.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="bi bi-bell"></i>
                </div>
                <h3 class="feature-title">Notifikasi Real-time</h3>
                <p class="feature-description">
                    Terima notifikasi penting dari Admin/HRD secara real-time tanpa perlu refresh halaman.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="bi bi-graph-up"></i>
                </div>
                <h3 class="feature-title">Insight & Analytics</h3>
                <p class="feature-description">
                    Dapatkan insight tentang pola mood kamu untuk membantu menjaga kesehatan mental yang lebih baik.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h3 class="feature-title">Privasi Terjamin</h3>
                <p class="feature-description">
                    Data mood kamu aman dan hanya dapat diakses oleh kamu dan tim HRD untuk keperluan monitoring kesehatan tim.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="bi bi-phone"></i>
                </div>
                <h3 class="feature-title">Akses Mudah</h3>
                <p class="feature-description">
                    Install sebagai PWA dan akses Ceremood kapan saja, di mana saja tanpa perlu membuka browser setiap saat.
                </p>
            </div>
        </section>

        <!-- About Section -->
        <section id="tentang" class="about-section">
            <h2 class="about-title">Tentang Ceremood</h2>
            <p class="about-text">
                Ceremood adalah aplikasi mood tracker yang dirancang khusus untuk tim Cerebrum. 
                Dengan Ceremood, kamu bisa dengan mudah mencatat dan melacak mood harian, 
                melihat pola perasaan, dan tetap terhubung dengan tim melalui notifikasi real-time.
            </p>
            <p class="about-text" style="margin-top: 20px;">
                Karena mood sehat bikin kerja makin hebat! 
                Mari bersama-sama menjaga kesehatan mental dan produktivitas tim dengan Ceremood.
            </p>
            <div class="cerebrum-badge">
                <i class="bi bi-heart-fill me-2"></i>Dari Cerebrum untuk Cerebrum
            </div>
        </section>

        <!-- Footer -->
        <footer class="footer-section" role="contentinfo">
            <div class="footer-content">
                <div class="footer-main">
                    <div class="footer-brand">
                        <img src="{{ route('logo.serve', ['filename' => 'favicons.png']) }}" alt="Ceremood Logo" class="footer-logo-img" width="60" height="60">
                        <h3 class="footer-brand-title">Ceremood</h3>
                        <p class="footer-brand-tagline">Mood Tracker untuk Tim Cerebrum</p>
                    </div>
                    
                    <div class="footer-links">
                        <div class="footer-column">
                            <h4 class="footer-column-title">Produk</h4>
                            <ul class="footer-link-list">
                                <li><a href="{{ route('dashboard') }}" class="footer-link">Dashboard</a></li>
                                <li><a href="#fitur" class="footer-link">Fitur</a></li>
                                <li><a href="#tentang" class="footer-link">Tentang</a></li>
                            </ul>
                        </div>
                        
                        <div class="footer-column">
                            <h4 class="footer-column-title">Perusahaan</h4>
                            <ul class="footer-link-list">
                                <li><span class="footer-link-text">Cerebrum</span></li>
                                <li><span class="footer-link-text">Mood Tracking Solution</span></li>
                                <li><span class="footer-link-text">Employee Wellness Platform</span></li>
                            </ul>
                        </div>
                        
                        <div class="footer-column">
                            <h4 class="footer-column-title">Informasi</h4>
                            <ul class="footer-link-list">
                                <li><span class="footer-link-text">Aplikasi Internal</span></li>
                                <li><span class="footer-link-text">Untuk Karyawan Cerebrum</span></li>
                                <li><span class="footer-link-text">Privacy Protected</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="footer-divider"></div>
                
                <div class="footer-bottom">
                    <div class="footer-copyright">
                        <p>&copy; {{ date('Y') }} Ceremood. All rights reserved.</p>
                        <p class="footer-company">Mood Tracker Application by Cerebrum</p>
                    </div>
                    <div class="footer-meta">
                        <p class="footer-version">Version 1.0.0</p>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</div>
@endsection
