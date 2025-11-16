// Push Notification Handler untuk PWA
(function() {
    'use strict';

    // Jangan aktifkan di halaman welcome atau admin
    const isWelcomePage = window.location.pathname === '/' || window.location.pathname === '/welcome';
    const isAdminPage = window.location.pathname.startsWith('/admin');
    
    if (isWelcomePage || isAdminPage) {
        return; // Exit early
    }

    // Cek apakah browser support push notification
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        console.log('Browser tidak mendukung push notification');
        return;
    }

    let currentSubscription = null;
    let isSubscribed = false;

    // Get CSRF token
    function getCsrfToken() {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        return metaTag ? metaTag.getAttribute('content') : '';
    }

    // Cek status subscription dari server
    async function checkSubscriptionStatus() {
        try {
            const response = await fetch('/notif/push/status', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            return data.subscribed || false;
        } catch (error) {
            console.error('Error checking subscription status:', error);
            return false;
        }
    }

    // Subscribe ke push notification
    async function subscribeToPush() {
        try {
            // Cek apakah service worker sudah terdaftar
            const registration = await navigator.serviceWorker.ready;

            // Request permission
            const permission = await Notification.requestPermission();
            
            if (permission !== 'granted') {
                throw new Error('Permission untuk push notification ditolak');
            }

            // Subscribe ke push service
            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(getVapidPublicKey())
            });

            // Kirim subscription ke server
            const response = await fetch('/notif/push/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    endpoint: subscription.endpoint,
                    keys: {
                        p256dh: arrayBufferToBase64(subscription.getKey('p256dh')),
                        auth: arrayBufferToBase64(subscription.getKey('auth'))
                    }
                })
            });

            const data = await response.json();
            
            if (data.success) {
                currentSubscription = subscription;
                isSubscribed = true;
                updateUI();
                return true;
            } else {
                throw new Error(data.message || 'Gagal subscribe');
            }
        } catch (error) {
            console.error('Error subscribing to push:', error);
            alert('Gagal mengaktifkan push notification: ' + error.message);
            return false;
        }
    }

    // Unsubscribe dari push notification
    async function unsubscribeFromPush() {
        try {
            if (!currentSubscription) {
                // Coba dapatkan subscription yang ada
                const registration = await navigator.serviceWorker.ready;
                const subscription = await registration.pushManager.getSubscription();
                
                if (!subscription) {
                    throw new Error('Tidak ada subscription yang aktif');
                }
                
                currentSubscription = subscription;
            }

            // Hapus dari server
            const response = await fetch('/notif/push/unsubscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    endpoint: currentSubscription.endpoint
                })
            });

            const data = await response.json();
            
            if (data.success) {
                // Unsubscribe dari browser
                await currentSubscription.unsubscribe();
                currentSubscription = null;
                isSubscribed = false;
                updateUI();
                return true;
            } else {
                throw new Error(data.message || 'Gagal unsubscribe');
            }
        } catch (error) {
            console.error('Error unsubscribing from push:', error);
            alert('Gagal menonaktifkan push notification: ' + error.message);
            return false;
        }
    }

    // Update UI berdasarkan status subscription
    function updateUI() {
        const toggleBtn = document.getElementById('push-notification-toggle');
        const statusText = document.getElementById('push-notification-status');
        
        if (toggleBtn) {
            toggleBtn.checked = isSubscribed;
            toggleBtn.disabled = false;
        }
        
        if (statusText) {
            statusText.textContent = isSubscribed 
                ? 'Push notification aktif' 
                : 'Push notification tidak aktif';
        }
    }

    // Get VAPID public key dari meta tag atau config
    function getVapidPublicKey() {
        // Coba ambil dari meta tag dulu
        const metaTag = document.querySelector('meta[name="vapid-public-key"]');
        if (metaTag) {
            return metaTag.getAttribute('content');
        }
        
        // Fallback: return empty string, akan error nanti
        console.warn('VAPID public key tidak ditemukan. Pastikan meta tag dengan name="vapid-public-key" ada di layout.');
        return '';
    }

    // Convert VAPID key dari base64 URL ke Uint8Array
    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/\-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    // Convert ArrayBuffer ke base64
    function arrayBufferToBase64(buffer) {
        const bytes = new Uint8Array(buffer);
        let binary = '';
        for (let i = 0; i < bytes.byteLength; i++) {
            binary += String.fromCharCode(bytes[i]);
        }
        return window.btoa(binary);
    }

    // Initialize push notification
    async function initializePushNotification() {
        try {
            // Tunggu service worker ready
            const registration = await navigator.serviceWorker.ready;
            
            // Cek subscription yang ada
            const subscription = await registration.pushManager.getSubscription();
            
            if (subscription) {
                currentSubscription = subscription;
                // Cek status dari server
                isSubscribed = await checkSubscriptionStatus();
            } else {
                isSubscribed = false;
            }
            
            updateUI();
        } catch (error) {
            console.error('Error initializing push notification:', error);
            isSubscribed = false;
            updateUI();
        }
    }

    // Setup event listener untuk toggle button
    function setupToggleButton() {
        const toggleBtn = document.getElementById('push-notification-toggle');
        
        if (toggleBtn) {
            toggleBtn.addEventListener('change', async function() {
                toggleBtn.disabled = true;
                
                if (this.checked) {
                    const success = await subscribeToPush();
                    if (!success) {
                        this.checked = false;
                    }
                } else {
                    const success = await unsubscribeFromPush();
                    if (!success) {
                        this.checked = true;
                    }
                }
                
                toggleBtn.disabled = false;
            });
        }
    }

    // Initialize saat DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initializePushNotification();
            setupToggleButton();
        });
    } else {
        initializePushNotification();
        setupToggleButton();
    }

    // Re-initialize saat Turbo load (untuk SPA behavior)
    document.addEventListener('turbo:load', function() {
        initializePushNotification();
        setupToggleButton();
    });

    // Export functions untuk digunakan di global scope
    window.subscribeToPush = subscribeToPush;
    window.unsubscribeFromPush = unsubscribeFromPush;
    window.checkPushSubscriptionStatus = checkSubscriptionStatus;
})();

