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
            navigator.serviceWorker.addEventListener('controllerchange', function() {
                console.log('[PWA] New service worker activated');
                window.location.reload();
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
        // Show install button (hanya di dashboard)
        showInstallButton();
    });

    // Show install button
    function showInstallButton() {
        // Jangan tampilkan di halaman admin atau welcome
        const isWelcomePage = window.location.pathname === '/' || window.location.pathname === '/welcome';
        const isAdminPage = window.location.pathname.startsWith('/admin');
        
        if (isAdminPage || isWelcomePage) {
            return;
        }
        
        // Hanya tampilkan di halaman dashboard
        if (window.location.pathname !== '/dashboard') {
            return;
        }

        // Jangan tampilkan jika app sudah diinstall (standalone mode)
        if (isStandalone()) {
            return;
        }

        // Check if button already exists
        if (document.getElementById('pwa-install-button')) {
            return;
        }

        // Tunggu splash screen selesai sebelum menampilkan button
        waitForSplashScreen(() => {
            // Create install button
            const button = document.createElement('button');
            button.id = 'pwa-install-button';
            button.className = 'btn position-fixed';
            button.style.cssText = `
                top: 20px;
                right: 20px;
                z-index: 1050;
                background-color: #dc3545;
                border-color: #dc3545;
                color: white;
                border-radius: 10px;
                padding: 8px 16px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                transition: opacity 0.15s ease-in, background-color 0.2s ease;
                opacity: 0;
                border: none;
                cursor: pointer;
            `;
            
            // Hover effect
            button.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#c82333';
                this.style.borderColor = '#bd2130';
            });
            
            button.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '#dc3545';
                this.style.borderColor = '#dc3545';
            });
            button.innerHTML = '<i class="bi bi-download me-2"></i>Install App';
            button.addEventListener('click', installApp);
            
            document.body.appendChild(button);
            
            // Fade in button dengan cepat (bersamaan dengan konten muncul)
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    button.style.opacity = '1';
                });
            });
        });
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

        // Listen untuk animationend - munculkan button saat mainContent muncul (50ms setelah animationend)
        splashLogo.addEventListener('animationend', () => {
            // Tampilkan button bersamaan dengan mainContent muncul (50ms delay seperti di app.js)
            setTimeout(() => {
                callback();
            }, 50);
        }, { once: true });

        // Fallback: jika setelah 5 detik splash masih ada, tampilkan button anyway
        setTimeout(() => {
            if (document.getElementById('splash-logo-container')) {
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

