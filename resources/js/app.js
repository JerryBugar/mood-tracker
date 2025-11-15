import * as Turbo from '@hotwired/turbo';
import './bootstrap';
import './modal'; // Import modal.js
import '../css/app.css';
import 'bootstrap'; // Import Bootstrap JavaScript

let isNavInitialized = false;

// Optimasi Turbo: konfigurasi Turbo agar lebih efisien
Turbo.config.drive.progressBarDelay = 500; // Tunda progress bar untuk pengalaman yang lebih mulus

document.addEventListener('turbo:load', () => {
    // Logging untuk mengetahui halaman mana yang dimuat
    console.log('Turbo loaded on: ', window.location.pathname);

    // Splash screen logic from original file
    const splashLogo = document.getElementById('splash-logo-container');
    const mainContent = document.getElementById('main-content-wrapper');
    const finalLogo = document.getElementById('final-logo-position');

    if (splashLogo && mainContent && finalLogo) {
        if (!splashLogo.classList.contains('js-has-run')) {
            splashLogo.classList.add('js-has-run');

            // Tambahkan class untuk halaman dashboard
            document.body.classList.add('dashboard-page');
            
            // Mencegah scroll pada mobile kecil saat splash aktif
            if (window.innerWidth <= 375 && window.innerHeight <= 600) {
                document.body.style.position = 'fixed';
                document.body.style.overflow = 'hidden';
                document.body.style.width = '100vw';
                document.body.style.height = '100vh';
                document.documentElement.style.overflow = 'hidden';
                document.documentElement.style.height = '100vh';
            }

            requestAnimationFrame(() => {
                // Pastikan logo final tersembunyi dulu sebelum menghitung posisi
                finalLogo.style.visibility = 'hidden';
                finalLogo.style.opacity = '0';
                
                // Ambil posisi akhir logo sebelum menampilkannya
                mainContent.classList.remove('hidden');
                const finalRect = finalLogo.getBoundingClientRect();

                // Matikan sementara transisi agar dapat menghitung posisi dengan akurat
                mainContent.classList.add('hidden');

                const finalX = finalRect.left + finalRect.width / 2 - window.innerWidth / 2;
                const finalY = finalRect.top + finalRect.height / 2 - window.innerHeight / 2;

                const styleSheet = document.createElement('style');
                styleSheet.id = 'dynamic-splash-animation';
                const keyframes = `
                    @keyframes settleInPlace {
                        0% { transform: translate(-50%, -50%); opacity: 1; }
                        100% { transform: translate(calc(-50% + ${finalX}px), calc(-50% + ${finalY}px)); opacity: 1; }
                    }
                `;
                styleSheet.innerHTML = keyframes;
                document.head.appendChild(styleSheet);

                // Tampilkan splash logo
                splashLogo.style.opacity = '1';
                splashLogo.style.animation = 'settleInPlace 2.5s ease-in-out forwards';

                splashLogo.addEventListener('animationend', () => {
                    // Pastikan splash logo sudah di posisi akhir sebelum menyembunyikan
                    // Tunggu sedikit untuk memastikan animasi benar-benar selesai
                    setTimeout(() => {
                        // Sembunyikan logo final sementara
                        finalLogo.style.visibility = 'hidden';
                        finalLogo.style.opacity = '0';

                        // Tampilkan konten utama
                        mainContent.classList.remove('hidden');

                        // Setelah konten muncul, sembunyikan splash logo dan tampilkan logo final
                        setTimeout(() => {
                            splashLogo.style.opacity = '0';
                            splashLogo.style.transition = 'opacity 0.5s ease-out';
                            
                            // Setelah splash logo fade out, tampilkan logo final
                            setTimeout(() => {
                                finalLogo.style.visibility = 'visible';
                                finalLogo.style.opacity = '1';
                                finalLogo.style.transition = 'opacity 0.3s ease-in';
                            }, 100);
                        }, 50);
                    }, 50);

                    // Setelah splash logo disembunyikan, hapus dari DOM
                    splashLogo.addEventListener('transitionend', () => {
                        splashLogo.remove();
                        if (document.getElementById('dynamic-splash-animation')) {
                            document.getElementById('dynamic-splash-animation').remove();
                        }
                        
                        // Kembalikan scroll pada mobile kecil setelah splash selesai
                        if (window.innerWidth <= 375 && window.innerHeight <= 600) {
                            document.body.style.position = '';
                            document.body.style.overflow = '';
                            document.body.style.width = '';
                            document.body.style.height = '';
                            document.documentElement.style.overflow = '';
                            document.documentElement.style.height = '';
                        }
                    });
                });
            });
        }
    }

    // --- Definitive Sliding Nav Logic ---
    const bottomNav = document.querySelector('#main-bottom-nav');
    if (!bottomNav) return;

    // Manually manage the active state based on URL
    const navItems = bottomNav.querySelectorAll('.nav-item');
    const currentPath = window.location.pathname;
    let newActiveItem = null;

    navItems.forEach(item => {
        item.classList.remove('active');
        const itemPath = new URL(item.href).pathname;
        if (itemPath === currentPath) {
            newActiveItem = item;
        }
    });

    if (newActiveItem) {
        newActiveItem.classList.add('active');
    }

    // Function to update nav background position
    function updateNavBackground() {
        const activeBackground = bottomNav.querySelector('.nav-active-background');
        const activeItem = bottomNav.querySelector('.nav-item.active');

        if (activeBackground && activeItem) {
            // Use getBoundingClientRect for more accurate positioning
            const navRect = bottomNav.getBoundingClientRect();
            const itemRect = activeItem.getBoundingClientRect();
            
            const leftPosition = itemRect.left - navRect.left;
            const itemWidth = itemRect.width;
            
            if (!isNavInitialized) {
                // On first load, just snap to position without animation
                activeBackground.style.transition = 'none';
                activeBackground.style.left = `${leftPosition}px`;
                activeBackground.style.width = `${itemWidth}px`;
                activeBackground.style.opacity = 1;

                setTimeout(() => {
                    activeBackground.style.transition = 'all 0.3s cubic-bezier(0.68, -0.55, 0.27, 1.55)';
                    isNavInitialized = true;
                }, 50);

            } else {
                // On subsequent loads, the transition is already enabled. Just move it.
                activeBackground.style.left = `${leftPosition}px`;
                activeBackground.style.width = `${itemWidth}px`;
            }
        }
    }

    // Wait for the .nav-item's own CSS transition to finish before measuring its width.
    setTimeout(() => {
        updateNavBackground();
    }, 200); // .nav-item transition is 0.3s, so we wait 200ms.
    
    // Update nav background on window resize
    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            updateNavBackground();
        }, 100);
    });

    // Add click event listeners to update active state and background
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            // Remove active class from all items
            navItems.forEach(navItem => navItem.classList.remove('active'));

            // Add active class to clicked item
            this.classList.add('active');

            // Update background position after a short delay to allow CSS changes to take effect
            setTimeout(() => {
                const activeBackground = bottomNav.querySelector('.nav-active-background');
                const navRect = bottomNav.getBoundingClientRect();
                const itemRect = this.getBoundingClientRect();
                
                if (activeBackground) {
                    const leftPosition = itemRect.left - navRect.left;
                    const itemWidth = itemRect.width;
                    activeBackground.style.left = `${leftPosition}px`;
                    activeBackground.style.width = `${itemWidth}px`;
                }
            }, 50);
        });
    });
});

// Mencegah navigasi tidak perlu ke halaman yang sama
document.addEventListener('turbo:before-visit', (event) => {
    if (event.detail.url === window.location.href) {
        event.preventDefault();
    }
});

// Menangani kasus ketika Turbo memutuskan untuk melakukan full page reload
document.addEventListener('turbo:before-render', (event) => {
    // Hapus elemen-elemen yang tidak seharusnya duplikat sebelum render
    const duplicateElements = event.detail.newBody.querySelectorAll('[id^="calendar-day-view"]');
    duplicateElements.forEach(element => {
        element.remove();
    });

    // Jaga agar modal tetap ada di DOM yang baru
    const existingModal = document.getElementById('moodModal');
    const newModal = event.detail.newBody.querySelector('[id="moodModal"]');

    if (existingModal && newModal) {
        newModal.replaceWith(existingModal);
    }
});

// Debug untuk form login admin - nonaktifkan Turbo di halaman login
document.addEventListener('turbo:load', () => {
    if (window.location.pathname.includes('/admin/login')) {
        const loginForm = document.getElementById('adminLoginForm');
        if (loginForm) {
            // Nonaktifkan Turbo untuk form ini
            loginForm.setAttribute('data-turbo', 'false');
        }
    }
});