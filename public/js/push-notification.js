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
    
    // Storage key untuk sessionStorage
    const STORAGE_KEY = 'push_notification_state';
    let updateUITimeout = null;

    // Get CSRF token
    function getCsrfToken() {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        return metaTag ? metaTag.getAttribute('content') : '';
    }
    
    // Simpan state ke sessionStorage
    function saveStateToStorage() {
        try {
            sessionStorage.setItem(STORAGE_KEY, JSON.stringify({
                isSubscribed: isSubscribed,
                timestamp: Date.now()
            }));
        } catch (e) {
            console.warn('Tidak bisa menyimpan state ke sessionStorage:', e);
        }
    }
    
    // Load state dari sessionStorage
    function loadStateFromStorage() {
        try {
            const stored = sessionStorage.getItem(STORAGE_KEY);
            if (stored) {
                const state = JSON.parse(stored);
                // State valid selama 5 menit
                if (Date.now() - state.timestamp < 300000) {
                    return state.isSubscribed;
                }
            }
        } catch (e) {
            console.warn('Tidak bisa load state dari sessionStorage:', e);
        }
        return null;
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

    // Cek status permission notifikasi
    function getNotificationPermission() {
        if (!('Notification' in window)) {
            return 'denied';
        }
        return Notification.permission;
    }

    // Show user-friendly permission message
    function showPermissionMessage() {
        const permission = getNotificationPermission();
        
        if (permission === 'denied') {
            // Jika permission sudah ditolak sebelumnya, berikan instruksi cara mengaktifkan
            if (confirm('Push notification dinonaktifkan. Untuk mengaktifkan, buka pengaturan browser Anda dan izinkan notifikasi untuk situs ini. Buka pengaturan sekarang?')) {
                // Buka halaman bantuan atau settings
                window.open('/help/notifications', '_blank');
            }
        } else if (permission === 'default') {
            // Jika belum ada keputusan, berikan penjelasan sebelum meminta permission
            if (confirm('Aktifkan push notification untuk mendapatkan pembaruan real-time? Anda dapat mengubah ini nanti di pengaturan.')) {
                return true; // Lanjutkan ke permission request
            }
        }
        return false;
    }

    // Subscribe ke push notification
    async function subscribeToPush() {
        try {
            // Cek permission status terlebih dahulu
            const permission = getNotificationPermission();
            
            if (permission === 'denied') {
                showPermissionMessage();
                throw new Error('Permission untuk push notification telah ditolak. Harap aktifkan manual di pengaturan browser.');
            }

            // Cek apakah service worker sudah terdaftar
            const registration = await navigator.serviceWorker.ready;

            // Request permission hanya jika belum ada keputusan
            let finalPermission = permission;
            if (permission === 'default') {
                // Tampilkan pesan konfirmasi sebelum meminta permission
                if (!showPermissionMessage()) {
                    throw new Error('Permintaan permission dibatalkan oleh pengguna');
                }
                
                finalPermission = await Notification.requestPermission();
            }

            if (finalPermission !== 'granted') {
                if (finalPermission === 'denied') {
                    showPermissionMessage();
                    throw new Error('Permission untuk push notification ditolak. Harap aktifkan manual di pengaturan browser.');
                } else {
                    throw new Error('Permission untuk push notification tidak diberikan');
                }
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
                saveStateToStorage();
                updateUI(true);
                showSuccessMessage('Push notification berhasil diaktifkan!');
                return true;
            } else {
                throw new Error(data.message || 'Gagal subscribe');
            }
        } catch (error) {
            console.error('Error subscribing to push:', error);
            
            // Jangan tampilkan alert untuk error permission yang sudah jelas
            if (!error.message.includes('Permission') && !error.message.includes('dibatalkan')) {
                alert('Gagal mengaktifkan push notification: ' + error.message);
            }
            
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
                    // Tidak ada subscription yang aktif
                    isSubscribed = false;
                    updateUI();
                    return true;
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
                saveStateToStorage();
                updateUI(true);
                showSuccessMessage('Push notification berhasil dinonaktifkan');
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

    // Show success message
    function showSuccessMessage(message) {
        // Buat toast notification sederhana
        const toast = document.createElement('div');
        toast.className = 'position-fixed alert alert-success alert-dismissible fade show';
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 1060; min-width: 300px;';
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(toast);
        
        // Auto remove setelah 3 detik
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 3000);
    }

    // Update UI berdasarkan status subscription dengan debounce dan state comparison
    function updateUI(force = false) {
        // Debounce untuk menghindari update berulang
        if (updateUITimeout) {
            clearTimeout(updateUITimeout);
        }
        
        updateUITimeout = setTimeout(() => {
            const toggleBtn = document.getElementById('push-notification-toggle');
            const statusText = document.getElementById('push-notification-status');
            
            if (!toggleBtn || !statusText) return;
            
            const permission = getNotificationPermission();
            const shouldBeChecked = isSubscribed && permission === 'granted';
            
            // Hanya update jika state berbeda atau force
            if (force || toggleBtn.checked !== shouldBeChecked) {
                toggleBtn.checked = shouldBeChecked;
                toggleBtn.disabled = permission === 'denied';
                
                // Tambahkan tooltip jika disabled
                if (permission === 'denied') {
                    toggleBtn.title = 'Permission notifikasi ditolak. Buka pengaturan browser untuk mengaktifkan.';
                } else {
                    toggleBtn.title = '';
                }
            }
            
            // Update status text
            if (permission === 'denied') {
                statusText.textContent = 'Permission notifikasi ditolak';
                statusText.className = 'text-danger';
            } else if (isSubscribed) {
                statusText.textContent = 'Push notification aktif';
                statusText.className = 'text-success';
            } else {
                statusText.textContent = 'Push notification tidak aktif';
                statusText.className = 'text-muted';
            }
        }, 50);
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
    async function initializePushNotification(forceCheck = false) {
        const settingsElement = document.getElementById('push-notification-settings');
        if (!settingsElement) return;
        
        // Untuk elemen permanent, check sessionStorage dulu
        if (settingsElement.hasAttribute('data-turbo-permanent') && 
            settingsElement.dataset.initialized === 'true' && 
            !forceCheck) {
            const storedState = loadStateFromStorage();
            if (storedState !== null) {
                // Gunakan stored state jika ada
                const previousState = isSubscribed;
                isSubscribed = storedState;
                
                // Hanya update UI jika state berbeda
                if (previousState !== isSubscribed) {
                    updateUI(true);
                }
                return;
            }
        }
        
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
            
            // Simpan state ke storage
            saveStateToStorage();
            updateUI(true);
            
            // Tandai sebagai initialized untuk elemen permanent
            if (settingsElement.hasAttribute('data-turbo-permanent')) {
                settingsElement.dataset.initialized = 'true';
            }
        } catch (error) {
            console.error('Error initializing push notification:', error);
            isSubscribed = false;
            saveStateToStorage();
            updateUI(true);
            
            if (settingsElement.hasAttribute('data-turbo-permanent')) {
                settingsElement.dataset.initialized = 'true';
            }
        }
    }

    // Setup event listener untuk toggle button dengan event delegation
    function setupToggleButton() {
        const settingsElement = document.getElementById('push-notification-settings');
        if (!settingsElement) return;
        
        // Gunakan event delegation untuk menghindari duplicate listeners
        if (settingsElement.dataset.listenerSetup === 'true') return;
        
        settingsElement.addEventListener('change', async function(event) {
            const toggleBtn = event.target;
            if (toggleBtn.id !== 'push-notification-toggle') return;
            
            toggleBtn.disabled = true;
            
            try {
                if (toggleBtn.checked) {
                    const success = await subscribeToPush();
                    if (!success) {
                        toggleBtn.checked = false;
                    }
                } else {
                    const success = await unsubscribeFromPush();
                    if (!success) {
                        toggleBtn.checked = true;
                    }
                }
            } finally {
                toggleBtn.disabled = false;
                updateUI();
            }
        }, true); // Use capture phase
        
        settingsElement.dataset.listenerSetup = 'true';
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
    // Skip jika elemen permanent sudah di-initialize dan state sama
    document.addEventListener('turbo:load', function() {
        const settingsElement = document.getElementById('push-notification-settings');
        if (!settingsElement) return;
        
        // Untuk elemen permanent, check state dulu
        if (settingsElement.hasAttribute('data-turbo-permanent') && 
            settingsElement.dataset.initialized === 'true') {
            const storedState = loadStateFromStorage();
            const toggleBtn = document.getElementById('push-notification-toggle');
            
            // Jika stored state ada dan sama dengan current state, skip
            if (storedState !== null && storedState === isSubscribed && toggleBtn) {
                const permission = getNotificationPermission();
                const shouldBeChecked = isSubscribed && permission === 'granted';
                
                // Hanya update UI jika toggle state berbeda
                if (toggleBtn.checked !== shouldBeChecked) {
                    updateUI(true);
                }
                return;
            }
        }
        
        // Initialize jika belum atau state berbeda
        initializePushNotification();
        setupToggleButton();
    });

    // Export functions untuk digunakan di global scope
    window.subscribeToPush = subscribeToPush;
    window.unsubscribeFromPush = unsubscribeFromPush;
    window.checkPushSubscriptionStatus = checkSubscriptionStatus;
    window.getNotificationPermission = getNotificationPermission;
})();