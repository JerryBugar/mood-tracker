// PWA Service Worker Registration dan Install Prompt
(function() {
    'use strict';

    // Jangan aktifkan PWA di halaman welcome atau admin
    const isWelcomePage = window.location.pathname === '/' || window.location.pathname === '/welcome';
    const isAdminPage = window.location.pathname.startsWith('/admin');
    
    if (isWelcomePage || isAdminPage) {
        console.log('[PWA] PWA disabled for this page');
        return; // Exit early, jangan aktifkan PWA
    }

    // Register Service Worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/sw.js')
                .then(function(registration) {
                    console.log('[PWA] Service Worker registered successfully:', registration.scope);
                    
                    // Check for updates
                    registration.addEventListener('updatefound', function() {
                        const newWorker = registration.installing;
                        newWorker.addEventListener('statechange', function() {
                            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                // New service worker available
                                showUpdateNotification();
                            }
                        });
                    });
                })
                .catch(function(error) {
                    console.error('[PWA] Service Worker registration failed:', error);
                });

            // Listen for controller change (new service worker activated)
            // Hanya reload jika benar-benar diperlukan untuk mencegah splash screen terload 2 kali
            navigator.serviceWorker.addEventListener('controllerchange', function() {
                console.log('[PWA] New service worker activated');
                // Hanya reload jika tidak sedang dalam proses redirect atau navigasi
                // Cek apakah halaman sedang dalam proses redirect
                const isRedirecting = sessionStorage.getItem('dashboard-redirect-executed');
                const isNavigating = document.visibilityState === 'hidden';
                
                // Jangan reload jika sedang redirect atau navigate
                if (isRedirecting || isNavigating) {
                    console.log('[PWA] Skip reload: redirect or navigation in progress');
                    return;
                }
                
                // Delay reload sedikit untuk memastikan tidak konflik dengan splash screen
                setTimeout(function() {
                    // Double check sebelum reload
                    if (!sessionStorage.getItem('dashboard-redirect-executed')) {
                        window.location.reload();
                    }
                }, 500);
            });
        });
    }

    // Install Prompt Handler
    let deferredPrompt;
    let installButton = null;

    // Check if app is running in standalone mode
    function isStandalone() {
        return (window.matchMedia('(display-mode: standalone)').matches) ||
               (window.navigator.standalone) ||
               document.referrer.includes('android-app://');
    }

    window.addEventListener('beforeinstallprompt', function(e) {
        // Jangan tampilkan install prompt di halaman admin atau welcome
        const isWelcomePage = window.location.pathname === '/' || window.location.pathname === '/welcome';
        const isAdminPage = window.location.pathname.startsWith('/admin');
        
        if (isAdminPage || isWelcomePage) {
            // Prevent the prompt from appearing di admin atau welcome pages
            e.preventDefault();
            // Jangan stash event untuk admin atau welcome pages
            return;
        }
        
        // Prevent the mini-infobar from appearing on mobile
        e.preventDefault();
        // Stash the event so it can be triggered later
        deferredPrompt = e;
        console.log('[PWA] beforeinstallprompt event received, showing install button');
        // Show install button (hanya di dashboard)
        showInstallButton();
    });
    
    // Fallback: Cek apakah sudah ada deferredPrompt yang tersimpan (untuk testing)
    // Jika di dashboard dan belum ada button, coba tampilkan setelah delay
    (function() {
        const currentPath = window.location.pathname;
        const isWelcome = currentPath === '/' || currentPath === '/welcome';
        const isAdmin = currentPath.startsWith('/admin');
        
        if (currentPath === '/dashboard' && !isWelcome && !isAdmin) {
            // Tunggu DOM ready dan splash screen selesai - timing yang sama dengan splash screen total (2.5s animasi + 0.2s buffer)
            setTimeout(function() {
                // Jika deferredPrompt sudah ada tapi button belum muncul, panggil showInstallButton
                if (deferredPrompt && !document.getElementById('pwa-install-button') && !isStandalone()) {
                    console.log('[PWA] Fallback: Showing install button (deferredPrompt exists)');
                    showInstallButton();
                } else if (!deferredPrompt) {
                    console.log('[PWA] No deferredPrompt available yet');
                }
            }, 2700); // Timing yang sama dengan splash screen selesai (2.5s + 0.2s)
        }
    })();

    // Show install button
    function showInstallButton() {
        // Jangan tampilkan di halaman admin atau welcome
        const isWelcomePage = window.location.pathname === '/' || window.location.pathname === '/welcome';
        const isAdminPage = window.location.pathname.startsWith('/admin');
        
        if (isAdminPage || isWelcomePage) {
            console.log('[PWA] Install button skipped: welcome or admin page');
            return;
        }
        
        // Hanya tampilkan di halaman dashboard
        if (window.location.pathname !== '/dashboard') {
            console.log('[PWA] Install button skipped: not dashboard page');
            return;
        }

        // Jangan tampilkan jika app sudah diinstall (standalone mode)
        if (isStandalone()) {
            console.log('[PWA] Install button skipped: app already installed');
            return;
        }

        // Check if button already exists
        if (document.getElementById('pwa-install-button')) {
            console.log('[PWA] Install button already exists');
            return;
        }
        
        console.log('[PWA] Creating install button...');

        // Tunggu splash screen selesai sebelum menampilkan button
        waitForSplashScreen(() => {
            // Create install button
            const button = document.createElement('button');
            button.id = 'pwa-install-button';
            button.className = 'btn position-fixed';
            
            // Deteksi mobile device
            const isMobile = window.innerWidth <= 768 || /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
            
            // Styling untuk desktop dan mobile
            const baseStyles = `
                z-index: 1050;
                background-color: #dc3545;
                border-color: #dc3545;
                color: white;
                border-radius: 10px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                transition: opacity 0.15s ease-in, background-color 0.2s ease, transform 0.2s ease;
                opacity: 0;
                border: none;
                cursor: pointer;
                font-size: 14px;
                font-weight: 600;
                white-space: nowrap;
            `;
            
            if (isMobile) {
                // Styling untuk mobile - posisi di kanan atas, lebih besar dan mudah diakses
                button.style.cssText = baseStyles + `
                    top: 15px !important;
                    right: 15px !important;
                    padding: 12px 16px !important;
                    font-size: 18px !important;
                    min-width: 44px !important;
                    min-height: 44px !important;
                    width: 44px !important;
                    height: 44px !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.25) !important;
                    position: fixed !important;
                    z-index: 9999 !important;
                    visibility: visible !important;
                `;
                button.innerHTML = '<i class="bi bi-download"></i>';
                button.setAttribute('aria-label', 'Install App');
            } else {
                // Styling untuk desktop
                button.style.cssText = baseStyles + `
                    top: 20px;
                    right: 20px;
                    padding: 8px 16px;
                `;
                button.innerHTML = '<i class="bi bi-download me-2"></i>Install App';
            }
            
            // Hover effect (hanya untuk desktop)
            if (!isMobile) {
                button.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = '#c82333';
                    this.style.borderColor = '#bd2130';
                });
                
                button.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = '#dc3545';
                    this.style.borderColor = '#dc3545';
                });
            }
            
            // Active/touch effect untuk mobile
            button.addEventListener('touchstart', function() {
                this.style.transform = 'scale(0.95)';
                this.style.backgroundColor = '#c82333';
            });
            
            button.addEventListener('touchend', function() {
                this.style.transform = 'scale(1)';
                this.style.backgroundColor = '#dc3545';
            });
            
            button.addEventListener('click', installApp);
            
            document.body.appendChild(button);
            
            // Fade in button dengan cepat (bersamaan dengan konten muncul)
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    button.style.opacity = '1';
                    console.log('[PWA] Install button displayed');
                });
            });
        });
    }
    
    // Debug: Log status PWA
    console.log('[PWA] Current path:', window.location.pathname);
    console.log('[PWA] Is standalone:', isStandalone());
    console.log('[PWA] Service Worker support:', 'serviceWorker' in navigator);
    
    // Untuk testing di responsive simulator: Force show button setelah delay
    // Hanya untuk dashboard dan jika belum ada button
    if (window.location.pathname === '/dashboard') {
        const isWelcome = window.location.pathname === '/' || window.location.pathname === '/welcome';
        const isAdmin = window.location.pathname.startsWith('/admin');
        
        if (!isWelcome && !isAdmin) {
            // Tunggu sesuai dengan timing splash screen selesai
            setTimeout(function() {
                const hasButton = document.getElementById('pwa-install-button');
                const isStandaloneMode = isStandalone();
                
                console.log('[PWA] Debug check:', {
                    hasButton: !!hasButton,
                    hasDeferredPrompt: !!deferredPrompt,
                    isStandalone: isStandaloneMode,
                    path: window.location.pathname
                });
                
                // Jika belum ada button dan belum standalone, coba tampilkan
                // (untuk testing di responsive simulator yang mungkin tidak trigger beforeinstallprompt)
                if (!hasButton && !isStandaloneMode) {
                    // Cek apakah ini mobile view
                    const isMobileView = window.innerWidth <= 768;
                    
                    if (isMobileView) {
                        console.log('[PWA] Mobile view detected, attempting to show install button');
                        // Jika deferredPrompt ada, gunakan itu
                        // Jika tidak, tetap tampilkan button (untuk testing)
                        if (deferredPrompt) {
                            showInstallButton();
                        } else {
                            // Untuk testing: tampilkan button meskipun belum ada deferredPrompt
                            // User bisa klik tapi akan error jika tidak ada deferredPrompt
                            console.log('[PWA] No deferredPrompt, but showing button for testing');
                            showInstallButton();
                        }
                    }
                }
            }, 2700); // Timing yang sama dengan splash screen selesai (2.5s animasi + 0.2s)
        }
    }

    // Function untuk menunggu splash screen selesai
    function waitForSplashScreen(callback) {
        const splashLogo = document.getElementById('splash-logo-container');
        const mainContent = document.getElementById('main-content-wrapper');
        
        // Jika tidak ada splash screen, langsung tampilkan button
        if (!splashLogo) {
            callback();
            return;
        }

        // Jika splash screen sudah selesai (tidak ada di DOM atau sudah dihapus)
        if (!document.getElementById('splash-logo-container')) {
            callback();
            return;
        }

        // Jika mainContent sudah visible, berarti splash sudah selesai
        if (mainContent && !mainContent.classList.contains('hidden')) {
            callback();
            return;
        }

        let hasTriggered = false;
        
        // Gunakan MutationObserver untuk mendeteksi kapan mainContent visible
        const observer = new MutationObserver((mutations) => {
            const currentMainContent = document.getElementById('main-content-wrapper');
            if (currentMainContent && !currentMainContent.classList.contains('hidden') && !hasTriggered) {
                hasTriggered = true;
                observer.disconnect();
                // Tampilkan button dengan delay minimal agar smooth
                setTimeout(() => {
                    callback();
                }, 50);
            }
        });
        
        // Observasi perubahan class di mainContent
        if (mainContent) {
            observer.observe(mainContent, {
                attributes: true,
                attributeFilter: ['class']
            });
        }

        // Listen untuk animationend sebagai backup
        splashLogo.addEventListener('animationend', () => {
            if (!hasTriggered) {
                hasTriggered = true;
                observer.disconnect();
                // Tunggu sampai mainContent benar-benar visible
                setTimeout(() => {
                    callback();
                }, 150);
            }
        }, { once: true });

        // Fallback: jika setelah 5 detik splash masih ada, tampilkan button anyway
        setTimeout(() => {
            if (!hasTriggered) {
                hasTriggered = true;
                observer.disconnect();
                callback();
            }
        }, 5000);
    }

    // Install app
    async function installApp() {
        if (!deferredPrompt) {
            return;
        }

        // Show the install prompt
        deferredPrompt.prompt();
        
        // Wait for the user to respond to the prompt
        const { outcome } = await deferredPrompt.userChoice;
        
        if (outcome === 'accepted') {
            console.log('[PWA] User accepted the install prompt');
        } else {
            console.log('[PWA] User dismissed the install prompt');
        }

        // Clear the deferredPrompt
        deferredPrompt = null;
        
        // Hide install button
        const button = document.getElementById('pwa-install-button');
        if (button) {
            button.remove();
        }
    }

    // Hide install button if app is already installed
    window.addEventListener('appinstalled', function() {
        console.log('[PWA] App installed');
        deferredPrompt = null;
        const button = document.getElementById('pwa-install-button');
        if (button) {
            button.remove();
        }
    });

    // Show update notification
    function showUpdateNotification() {
        // Create update notification
        const notification = document.createElement('div');
        notification.className = 'alert alert-info alert-dismissible fade show position-fixed';
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 1060; min-width: 300px;';
        notification.innerHTML = `
            <strong>Update Tersedia!</strong> Versi baru aplikasi tersedia.
            <button type="button" class="btn btn-sm btn-primary ms-2" onclick="window.location.reload()">
                Update Sekarang
            </button>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto dismiss after 10 seconds
        setTimeout(function() {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 10000);
    }

    // Log PWA status
    if (isStandalone()) {
        console.log('[PWA] App is running in standalone mode');
    }

    // Expose install function globally (optional)
    window.installPWA = installApp;
})();

