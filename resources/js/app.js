import * as Turbo from '@hotwired/turbo';
import './bootstrap';
import './modal'; // Import modal.js
import '../css/app.css';
import 'bootstrap'; // Import Bootstrap JavaScript

let isNavInitialized = false;

// Optimasi Turbo: konfigurasi Turbo agar lebih efisien
Turbo.config.drive.progressBarDelay = 500; // Tunda progress bar untuk pengalaman yang lebih mulus

document.addEventListener('turbo:load', () => {

    // Splash screen logic from original file - Optimized for mobile
    const splashLogo = document.getElementById('splash-logo-container');
    const mainContent = document.getElementById('main-content-wrapper');
    const finalLogo = document.getElementById('final-logo-position');

    if (splashLogo && mainContent && finalLogo) {
        // Guard menggunakan sessionStorage untuk mencegah splash screen terload 2 kali
        // Ini penting karena Turbo bisa memicu event beberapa kali atau service worker bisa reload halaman
        const splashKey = 'splash-screen-executed';
        const currentUrl = window.location.href;
        const executedUrl = sessionStorage.getItem(splashKey);

        // Jika splash sudah dieksekusi untuk URL yang sama, skip
        if (executedUrl === currentUrl && splashLogo.classList.contains('js-has-run')) {
            // Pastikan splash logo sudah dihapus jika sudah selesai
            if (splashLogo && splashLogo.parentNode) {
                splashLogo.remove();
            }
            // Pastikan main content visible
            if (mainContent) {
                mainContent.classList.remove('hidden');
            }
            return;
        }

        if (!splashLogo.classList.contains('js-has-run')) {
            // Tandai splash sudah dieksekusi untuk URL ini
            sessionStorage.setItem(splashKey, currentUrl);
            splashLogo.classList.add('js-has-run');

            // Tambahkan class untuk halaman dashboard
            document.body.classList.add('dashboard-page');

            // Simpan posisi scroll saat ini sebelum mencegah scroll
            const scrollY = window.scrollY;
            const scrollX = window.scrollX;

            // Mencegah scroll untuk semua ukuran layar saat splash aktif
            document.body.style.position = 'fixed';
            document.body.style.overflow = 'hidden';
            document.body.style.width = '100vw';
            document.body.style.height = '100vh';
            document.body.style.top = `-${scrollY}px`;
            document.body.style.left = `-${scrollX}px`;
            document.documentElement.style.overflow = 'hidden';
            document.documentElement.style.height = '100vh';

            // Fungsi untuk mencegah scroll behavior
            const preventScroll = (e) => {
                e.preventDefault();
                e.stopPropagation();
                return false;
            };

            // Tambahkan event listener untuk mencegah scroll
            const scrollEvents = ['wheel', 'touchmove', 'scroll', 'keydown'];
            const scrollPreventers = [];

            scrollEvents.forEach(eventType => {
                const preventer = (e) => {
                    // Izinkan scroll hanya jika bukan arrow keys atau space/page up/down
                    if (eventType === 'keydown') {
                        const keys = ['ArrowUp', 'ArrowDown', 'PageUp', 'PageDown', 'Home', 'End', ' '];
                        if (keys.includes(e.key)) {
                            e.preventDefault();
                            e.stopPropagation();
                            return false;
                        }
                    } else {
                        preventScroll(e);
                    }
                };
                document.addEventListener(eventType, preventer, { passive: false });
                scrollPreventers.push({ eventType, preventer });
            });

            // Fungsi untuk cleanup scroll prevention
            const removeScrollPrevention = () => {
                // Hapus semua event listener
                scrollPreventers.forEach(({ eventType, preventer }) => {
                    document.removeEventListener(eventType, preventer);
                });

                // Kembalikan style body dan html
                document.body.style.position = '';
                document.body.style.overflow = '';
                document.body.style.width = '';
                document.body.style.height = '';
                document.body.style.top = '';
                document.body.style.left = '';
                document.documentElement.style.overflow = '';
                document.documentElement.style.height = '';

                // Kembalikan posisi scroll
                window.scrollTo(scrollX, scrollY);
            };

            // Optimasi splash screen untuk mobile: sederhanakan animasi dan pastikan transisi aman
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
                        0% {
                            transform: translate(-50%, -50%) scale(0.9);
                            opacity: 0;
                        }
                        15% {
                            opacity: 1;
                        }
                        100% {
                            transform: translate(calc(-50% + ${finalX}px), calc(-50% + ${finalY}px)) scale(1.0);
                            opacity: 1;
                        }
                    }
                `;
                styleSheet.innerHTML = keyframes;
                document.head.appendChild(styleSheet);

                // Optimasi performa dengan will-change
                splashLogo.style.willChange = 'transform, opacity';

                // Tampilkan splash logo dengan fade-in halus
                // Gunakan cubic-bezier yang lebih dinamis untuk efek yang lebih menarik
                splashLogo.style.animation = 'settleInPlace 1.8s cubic-bezier(0.34, 1.56, 0.64, 1) forwards';

                // Tambahkan fallback timeout sebagai cadangan jika animationend tidak terpicu
                const animationDuration = 2000; // 1.8s + 0.2s buffer
                const animationTimeout = setTimeout(() => {
                    handleAnimationComplete();
                }, animationDuration);

                function handleAnimationComplete() {
                    // Hapus timeout jika animationend terjadi lebih cepat
                    if (animationTimeout) {
                        clearTimeout(animationTimeout);
                    }

                    // Tampilkan konten utama dengan fade-in halus
                    mainContent.classList.remove('hidden');

                    // Sembunyikan logo final sementara (sudah hidden dari awal)
                    finalLogo.style.visibility = 'hidden';
                    finalLogo.style.opacity = '0';

                    // Timing yang lebih seamless: langsung mulai fade-out splash logo
                    // Gunakan setTimeout untuk timing yang lebih konsisten di mobile
                    setTimeout(() => {
                        // Fade-out splash logo dengan transisi yang halus
                        splashLogo.style.opacity = '0';
                        splashLogo.style.transition = 'opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1)';

                        // Tampilkan logo final dengan timing yang overlap untuk transisi seamless
                        setTimeout(() => {
                            finalLogo.style.visibility = 'visible';
                            finalLogo.style.opacity = '0'; // Mulai dari opacity 0 untuk transisi yang mulus

                            // Beri sedikit delay sebelum menerapkan transisi untuk memastikan render
                            requestAnimationFrame(() => {
                                finalLogo.style.transition = 'opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
                                finalLogo.style.opacity = '1';
                            });
                        }, 150); // Overlap sedikit dengan fade-out untuk transisi yang lebih halus
                    }, 50); // Delay kecil sebelum mulai fade-out splash

                    // Setelah splash logo disembunyikan, hapus dari DOM
                    setTimeout(() => {
                        if (splashLogo && splashLogo.parentNode) {
                            splashLogo.remove();
                        }
                        if (document.getElementById('dynamic-splash-animation')) {
                            document.getElementById('dynamic-splash-animation').remove();
                        }
                    }, 600); // Beri waktu untuk fade-out selesai sebelum dihapus

                    // Hapus scroll prevention setelah animasi selesai
                    removeScrollPrevention();

                    // Cleanup sessionStorage setelah splash selesai (untuk next visit)
                    // Tapi jangan hapus jika masih di URL yang sama (untuk mencegah double load)
                    const splashKey = 'splash-screen-executed';
                    const currentUrl = window.location.href;
                    const executedUrl = sessionStorage.getItem(splashKey);

                    // Hanya hapus jika URL berbeda (berarti sudah navigate ke halaman lain)
                    // Atau hapus setelah delay untuk memastikan tidak ada double load
                    setTimeout(() => {
                        if (executedUrl !== currentUrl) {
                            sessionStorage.removeItem(splashKey);
                        }
                    }, 1000);
                }

                // Event listener untuk animasi selesai
                splashLogo.addEventListener('animationend', () => {
                    handleAnimationComplete();
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

    // Function to update nav background position dengan requestAnimationFrame untuk timing yang akurat
    function updateNavBackground() {
        const activeBackground = bottomNav.querySelector('.nav-active-background');
        const activeItem = bottomNav.querySelector('.nav-item.active');

        if (activeBackground && activeItem) {
            // Gunakan requestAnimationFrame untuk timing yang lebih akurat
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
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

                        // Setelah posisi awal ditetapkan, aktifkan transisi
                        requestAnimationFrame(() => {
                            activeBackground.style.transition = 'left 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), width 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.35s cubic-bezier(0.4, 0, 0.2, 1)';
                            isNavInitialized = true;
                        });
                    } else {
                        // On subsequent loads, the transition is already enabled. Just move it.
                        activeBackground.style.left = `${leftPosition}px`;
                        activeBackground.style.width = `${itemWidth}px`;
                    }
                });
            });
        }
    }

    // Tunggu nav-item transition selesai sebelum update background
    // Gunakan requestAnimationFrame untuk timing yang lebih akurat
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            // Tunggu sedikit untuk memastikan CSS transition nav-item sudah mulai
            setTimeout(() => {
                updateNavBackground();
            }, 50);
        });
    });

    // Update nav background on window resize dengan debounce
    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            updateNavBackground();
        }, 150);
    });

    // Add click event listeners to update active state and background dengan efek visual
    navItems.forEach(item => {
        item.addEventListener('click', function (e) {
            // Hapus active class dari semua items dengan efek fade/scale
            navItems.forEach(navItem => {
                if (navItem.classList.contains('active')) {
                    // Tambahkan efek fade-out ringan saat menghapus active
                    navItem.style.opacity = '0.7';
                    requestAnimationFrame(() => {
                        navItem.classList.remove('active');
                        navItem.style.opacity = '';
                    });
                } else {
                    navItem.classList.remove('active');
                }
            });

            // Tambahkan active class ke item yang diklik dengan efek fade/scale
            this.style.opacity = '0.8';
            this.style.transform = 'scale(0.95)';

            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    this.classList.add('active');
                    this.style.opacity = '';
                    this.style.transform = '';

                    // Update background position setelah CSS transition dimulai
                    requestAnimationFrame(() => {
                        const activeBackground = bottomNav.querySelector('.nav-active-background');
                        const navRect = bottomNav.getBoundingClientRect();
                        const itemRect = this.getBoundingClientRect();

                        if (activeBackground) {
                            const leftPosition = itemRect.left - navRect.left;
                            const itemWidth = itemRect.width;
                            activeBackground.style.left = `${leftPosition}px`;
                            activeBackground.style.width = `${itemWidth}px`;
                        }
                    });
                });
            });
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

// Function to handle opening edit modal - Global function
window.openEditMoodModal = function (url) {
    // 1. Reset frame content to loading state
    const frameContent = document.getElementById('mood_modal_content');
    if (frameContent) {
        // PENTING: Reset src attribute terlebih dahulu untuk menghindari caching
        frameContent.removeAttribute('src');

        frameContent.innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;

        // 2. Set src SETELAH removeAttribute untuk memastikan fetch baru
        // Gunakan setTimeout untuk memastikan removeAttribute sudah diproses
        setTimeout(() => {
            frameContent.setAttribute('src', url);
        }, 0);
    }

    // 3. Show modal safely
    const modalElement = document.getElementById('moodModal');
    if (modalElement) {
        // Gunakan getOrCreateInstance untuk menghindari duplikasi instance yang bisa merusak state
        const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
        modal.show();

        // Pastikan event listener untuk cleanup ditambahkan
        // Hapus class modal-open dari body secara manual saat modal tertutup
        // Ini untuk mengatasi bug di mana scroll terkunci setelah update via Turbo
        modalElement.addEventListener('hidden.bs.modal', function () {
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        }, { once: true });
    }
};