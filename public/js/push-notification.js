// Push Notification Handler untuk PWA
(function() {
    'use strict';

    // Hanya aktifkan di halaman notif
    const isWelcomePage = window.location.pathname === '/' || window.location.pathname === '/welcome';
    const isAdminPage = window.location.pathname.startsWith('/admin');
    const isNotifPage = window.location.pathname === '/notif';
    
    // Hanya aktifkan di halaman notif, jangan mengganggu navbar lain
    if (isWelcomePage || isAdminPage || !isNotifPage) {
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
                
                // Re-initialize untuk memastikan state sinkron dengan server
                // Ini penting untuk Turbo agar state ter-update tanpa perlu refresh manual
                await initializePushNotification(true);
                
                return true;
            } else {
                throw new Error(data.message || 'Gagal subscribe');
            }
        } catch (error) {
            console.error('Error subscribing to push:', error);
            
            // Jangan tampilkan alert untuk error permission yang sudah jelas
            if (!error.message.includes('Permission') && !error.message.includes('dibatalkan')) {
                showErrorMessage('Gagal mengaktifkan push notification: ' + error.message);
            }
            
            return false;
        }
    }

    // Unsubscribe dari push notification
    async function unsubscribeFromPush() {
        try {
            // Pastikan state awal sudah benar
            let subscriptionToUnsubscribe = currentSubscription;
            
            if (!subscriptionToUnsubscribe) {
                // Coba dapatkan subscription yang ada dari browser
                const registration = await navigator.serviceWorker.ready;
                subscriptionToUnsubscribe = await registration.pushManager.getSubscription();
                
                if (!subscriptionToUnsubscribe) {
                    // Tidak ada subscription yang aktif di browser
                    // Tapi mungkin masih ada di server, jadi hapus dari server menggunakan cleanup
                    const serverStatus = await checkSubscriptionStatus();
                    if (serverStatus) {
                        // Masih ada di server, gunakan endpoint cleanup untuk menghapus semua
                        console.log('Tidak ada subscription di browser, membersihkan subscription di server...');
                        try {
                            const cleanupResponse = await fetch('/notif/push/cleanup', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': getCsrfToken(),
                                    'Accept': 'application/json'
                                }
                            });
                            
                            const cleanupData = await cleanupResponse.json();
                            if (cleanupData.success) {
                                console.log('Subscription di server berhasil dibersihkan. Jumlah yang dihapus:', cleanupData.deleted_count || 0);
                            } else {
                                console.error('Gagal membersihkan subscription di server:', cleanupData.message);
                            }
                        } catch (cleanupError) {
                            console.error('Error cleaning up server subscription:', cleanupError);
                        }
                    }
                    
                    // Update state menjadi false
                    isSubscribed = false;
                    currentSubscription = null;
                    saveStateToStorage();
                    updateUI(true);
                    
                    // Re-initialize untuk memastikan state sinkron dengan server
                    // Ini penting untuk Turbo agar state ter-update tanpa perlu refresh manual
                    await initializePushNotification(true);
                    
                    // Tampilkan success message
                    showSuccessMessage('Push notification berhasil dinonaktifkan');
                    return true;
                }
                
                currentSubscription = subscriptionToUnsubscribe;
            }

            // Hapus dari server terlebih dahulu
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
                try {
                    await currentSubscription.unsubscribe();
                } catch (unsubError) {
                    // Jika unsubscribe dari browser gagal, tetap lanjutkan
                    // karena sudah dihapus dari server
                    console.warn('Error unsubscribing from browser:', unsubError);
                }
                
                // Pastikan state di-update dengan benar
                currentSubscription = null;
                isSubscribed = false;
                
                // Simpan state ke storage
                saveStateToStorage();
                
                // Verifikasi status dari server untuk memastikan unsubscribe berhasil
                const verifiedStatus = await checkSubscriptionStatus();
                if (verifiedStatus) {
                    // Masih terdeteksi sebagai subscribed, gunakan cleanup
                    console.warn('Subscription masih terdeteksi setelah unsubscribe, menggunakan cleanup...');
                    try {
                        const cleanupResponse = await fetch('/notif/push/cleanup', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': getCsrfToken(),
                                'Accept': 'application/json'
                            }
                        });
                        
                        const cleanupData = await cleanupResponse.json();
                        if (cleanupData.success) {
                            console.log('Subscription di server berhasil dibersihkan. Jumlah yang dihapus:', cleanupData.deleted_count || 0);
                        } else {
                            console.error('Gagal membersihkan subscription di server:', cleanupData.message);
                        }
                    } catch (cleanupError) {
                        console.error('Error cleaning up server subscription:', cleanupError);
                    }
                }
                
                // Update UI dengan force untuk memastikan UI refresh
                updateUI(true);
                
                // Re-initialize untuk memastikan state sinkron dengan server
                // Ini penting untuk Turbo agar state ter-update tanpa perlu refresh manual
                await initializePushNotification(true);
                
                // Tampilkan success message
                showSuccessMessage('Push notification berhasil dinonaktifkan');
                return true;
            } else {
                throw new Error(data.message || 'Gagal unsubscribe');
            }
        } catch (error) {
            console.error('Error unsubscribing from push:', error);
            
            // Meskipun ada error, coba update state jika subscription tidak ada di browser
            try {
                const registration = await navigator.serviceWorker.ready;
                const subscription = await registration.pushManager.getSubscription();
                if (!subscription) {
                    // Tidak ada subscription di browser, coba cleanup di server juga
                    const serverStatus = await checkSubscriptionStatus();
                    if (serverStatus) {
                        try {
                            const cleanupResponse = await fetch('/notif/push/cleanup', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': getCsrfToken(),
                                    'Accept': 'application/json'
                                }
                            });
                            
                            const cleanupData = await cleanupResponse.json();
                            if (cleanupData.success) {
                                console.log('Subscription di server berhasil dibersihkan setelah error');
                            }
                        } catch (cleanupError) {
                            console.error('Error cleaning up server subscription:', cleanupError);
                        }
                    }
                    
                    // Update state menjadi false
                    isSubscribed = false;
                    currentSubscription = null;
                    saveStateToStorage();
                    updateUI(true);
                }
            } catch (checkError) {
                console.error('Error checking subscription:', checkError);
            }
            
            showErrorMessage('Gagal menonaktifkan push notification: ' + error.message);
            return false;
        }
    }

    // Show success message menggunakan toast container yang sama seperti profile
    function showSuccessMessage(message) {
        // Pastikan Bootstrap tersedia
        if (typeof bootstrap === 'undefined') {
            console.log('Bootstrap tidak tersedia:', message);
            return;
        }
        
        const toastElement = document.getElementById('notificationToast');
        const toastMessage = document.getElementById('toast-message');
        const toastTitle = document.getElementById('toast-title');
        
        if (!toastElement || !toastMessage || !toastTitle) {
            console.log('Toast element tidak ditemukan:', message);
            return;
        }
        
        // Langsung gunakan toast container tanpa bergantung pada window.showToast
        // Ini memastikan tidak ada alert yang muncul
        const toastIconWrapper = toastElement.querySelector('.toast-icon-wrapper');
        const toastIcon = toastElement.querySelector('.toast-icon');
        
        // Update message dan title
        toastMessage.textContent = message;
        toastTitle.textContent = 'Berhasil';
        
        // Set styling untuk success
        toastTitle.classList.remove('error');
        toastElement.classList.remove('error');
        toastElement.classList.add('success');
        
        if (toastIconWrapper) {
            toastIconWrapper.classList.remove('error');
        }
        
        if (toastIcon) {
            toastIcon.className = 'toast-icon bi bi-check-circle-fill';
        }
        
        // Hide toast yang sedang ditampilkan sebelumnya jika ada
        const existingToast = bootstrap.Toast.getInstance(toastElement);
        if (existingToast) {
            existingToast.hide();
            setTimeout(() => showToastNow(toastElement), 200);
        } else {
            showToastNow(toastElement);
        }
    }
    
    // Helper function untuk menampilkan toast
    function showToastNow(toastElement) {
        toastElement.classList.remove('hiding', 'show');
        
        const hideHandler = toastElement._hideHandler;
        if (hideHandler) {
            toastElement.removeEventListener('hide.bs.toast', hideHandler);
        }
        
        const newHideHandler = () => toastElement.classList.add('hiding');
        toastElement._hideHandler = newHideHandler;
        toastElement.addEventListener('hide.bs.toast', newHideHandler);
        
        const toastContainer = toastElement.closest('.toast-container');
        if (toastContainer) {
            toastContainer.style.display = 'block';
        }
        
        const toast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: 4000
        });
        
        toast.show();
    }

    // Show error message menggunakan toast container yang sama
    function showErrorMessage(message) {
        // Pastikan Bootstrap tersedia
        if (typeof bootstrap === 'undefined') {
            console.error('Bootstrap tidak tersedia:', message);
            return;
        }
        
        const toastElement = document.getElementById('notificationToast');
        const toastMessage = document.getElementById('toast-message');
        const toastTitle = document.getElementById('toast-title');
        
        if (!toastElement || !toastMessage || !toastTitle) {
            console.error('Toast element tidak ditemukan:', message);
            return;
        }
        
        // Langsung gunakan toast container tanpa bergantung pada window.showToast
        // Ini memastikan tidak ada alert yang muncul
        
        const toastIconWrapper = toastElement.querySelector('.toast-icon-wrapper');
        const toastIcon = toastElement.querySelector('.toast-icon');
        
        // Update message dan title
        toastMessage.textContent = message;
        toastTitle.textContent = 'Error';
        
        // Set styling untuk error
        toastTitle.classList.add('error');
        toastElement.classList.add('error');
        toastElement.classList.remove('success');
        
        if (toastIconWrapper) {
            toastIconWrapper.classList.add('error');
        }
        
        if (toastIcon) {
            toastIcon.className = 'toast-icon bi bi-x-circle-fill';
        }
        
        // Hide toast yang sedang ditampilkan sebelumnya jika ada
        const existingToast = bootstrap.Toast.getInstance(toastElement);
        if (existingToast) {
            existingToast.hide();
            setTimeout(() => showToastNow(toastElement), 200);
        } else {
            showToastNow(toastElement);
        }
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
            // Pastikan shouldBeChecked hanya true jika isSubscribed benar-benar true DAN permission granted
            // Ini memastikan toggle state selalu sinkron dengan isSubscribed state
            const shouldBeChecked = isSubscribed === true && permission === 'granted';
            
            // Selalu update toggle state jika force, atau jika state berbeda
            // Pastikan toggle selalu sinkron dengan isSubscribed state
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
            
            // Update status text berdasarkan isSubscribed state, bukan hanya permission
            // Status text harus sinkron dengan toggle state
            if (permission === 'denied') {
                statusText.textContent = 'Permission notifikasi ditolak';
                statusText.className = 'text-danger';
            } else if (isSubscribed === true) {
                // Hanya tampilkan "aktif" jika benar-benar subscribed
                statusText.textContent = 'Push notification aktif';
                statusText.className = 'text-success';
            } else {
                // Pastikan status text menunjukkan "tidak aktif" jika isSubscribed false
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
            
            // Cek subscription yang ada di browser
            const subscription = await registration.pushManager.getSubscription();
            
            // Cek status dari server HANYA jika forceCheck = true
            // Ini mencegah request berulang setiap kali turbo:load
            let serverStatus = false;
            if (forceCheck) {
                serverStatus = await checkSubscriptionStatus();
                lastStatusCheckTime = Date.now();
            } else {
                // Gunakan stored state jika ada, atau false sebagai default
                const storedState = loadStateFromStorage();
                serverStatus = storedState !== null ? storedState : false;
            }
            
            if (subscription) {
                // Ada subscription di browser
                currentSubscription = subscription;
                
                // Pastikan isSubscribed sesuai dengan server status
                // Ini penting untuk sinkronisasi ketika browser permission ON tapi subscription tidak ada di server
                isSubscribed = serverStatus;
                
                // Jika ada subscription di browser tapi tidak ada di server, hapus dari browser
                if (!serverStatus && subscription) {
                    console.warn('Subscription ada di browser tapi tidak ada di server, menghapus dari browser...');
                    try {
                        await subscription.unsubscribe();
                        currentSubscription = null;
                        isSubscribed = false;
                    } catch (unsubError) {
                        console.error('Error removing orphaned subscription:', unsubError);
                        // Tetap set state menjadi false
                        currentSubscription = null;
                        isSubscribed = false;
                    }
                }
            } else {
                // Tidak ada subscription di browser
                currentSubscription = null;
                
                // Jika server masih mencatat sebagai subscribed, set menjadi false
                // karena tidak ada subscription di browser
                isSubscribed = false;
                
                // Jika server masih mencatat sebagai subscribed tapi tidak ada di browser,
                // ini berarti ada ketidaksesuaian - bersihkan subscription di server
                if (serverStatus) {
                    console.warn('Server mencatat sebagai subscribed tapi tidak ada subscription di browser. Membersihkan subscription di server...');
                    
                    // Bersihkan subscription di server untuk sinkronisasi
                    try {
                        const cleanupResponse = await fetch('/notif/push/cleanup', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': getCsrfToken(),
                                'Accept': 'application/json'
                            }
                        });
                        
                        const cleanupData = await cleanupResponse.json();
                        if (cleanupData.success) {
                            console.log('Subscription di server berhasil dibersihkan. Jumlah yang dihapus:', cleanupData.deleted_count || 0);
                        } else {
                            console.error('Gagal membersihkan subscription di server:', cleanupData.message);
                        }
                    } catch (cleanupError) {
                        console.error('Error cleaning up server subscription:', cleanupError);
                    }
                }
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
            // Set state menjadi false jika ada error
            isSubscribed = false;
            currentSubscription = null;
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
        
        // Hapus listener lama jika ada (untuk mencegah duplicate listeners)
        // Ini penting karena Turbo mungkin me-replace elemen dan kita perlu re-attach listener
        if (settingsElement._toggleHandler) {
            settingsElement.removeEventListener('change', settingsElement._toggleHandler, true);
            settingsElement._toggleHandler = null;
        }
        
        // Buat handler baru
        settingsElement._toggleHandler = async function(event) {
            const toggleBtn = event.target;
            if (toggleBtn.id !== 'push-notification-toggle') return;
            
            // Simpan state awal untuk validasi
            const previousCheckedState = toggleBtn.checked;
            const previousIsSubscribed = isSubscribed;
            
            // Disable toggle selama proses
            toggleBtn.disabled = true;
            
            try {
                if (toggleBtn.checked) {
                    // User ingin mengaktifkan
                    const success = await subscribeToPush();
                    if (!success) {
                        // Jika subscribe gagal, kembalikan toggle ke posisi sebelumnya
                        toggleBtn.checked = false;
                        // Pastikan state konsisten
                        if (isSubscribed !== previousIsSubscribed) {
                            isSubscribed = previousIsSubscribed;
                            saveStateToStorage();
                        }
                    }
                } else {
                    // User ingin menonaktifkan
                    const success = await unsubscribeFromPush();
                    if (!success) {
                        // Jika unsubscribe gagal, kembalikan toggle ke posisi sebelumnya
                        toggleBtn.checked = true;
                        // Pastikan state konsisten
                        if (isSubscribed !== previousIsSubscribed) {
                            isSubscribed = previousIsSubscribed;
                            saveStateToStorage();
                        }
                    }
                }
            } catch (error) {
                // Handle error yang tidak tertangkap
                console.error('Error in toggle handler:', error);
                // Kembalikan toggle ke posisi sebelumnya
                toggleBtn.checked = previousCheckedState;
                // Pastikan state konsisten
                if (isSubscribed !== previousIsSubscribed) {
                    isSubscribed = previousIsSubscribed;
                    saveStateToStorage();
                }
            } finally {
                // Selalu enable toggle dan update UI
                toggleBtn.disabled = false;
                // Force update UI untuk memastikan sinkronisasi
                updateUI(true);
            }
        };
        
        // Attach listener baru
        settingsElement.addEventListener('change', settingsElement._toggleHandler, true);
        
        // Set flag untuk tracking (tapi akan di-reset di turbo:load)
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

    // Track waktu terakhir check status untuk mencegah request berulang
    let lastStatusCheckTime = 0;
    const STATUS_CHECK_INTERVAL = 30000; // 30 detik - hanya check server setiap 30 detik

    // Re-initialize saat Turbo load (untuk SPA behavior)
    // Skip jika elemen permanent sudah di-initialize dan state sama
    document.addEventListener('turbo:load', function() {
        // Hanya aktifkan di halaman notif, jangan mengganggu navbar lain
        if (window.location.pathname !== '/notif') {
            return;
        }
        
        const settingsElement = document.getElementById('push-notification-settings');
        if (!settingsElement) return;
        
        // Reset flag listenerSetup untuk memungkinkan re-setup
        // Ini penting karena Turbo mungkin me-replace elemen dan kita perlu re-attach listener
        delete settingsElement.dataset.listenerSetup;
        
        // Untuk elemen permanent, check state dulu
        if (settingsElement.hasAttribute('data-turbo-permanent') && 
            settingsElement.dataset.initialized === 'true') {
            const storedState = loadStateFromStorage();
            const toggleBtn = document.getElementById('push-notification-toggle');
            
            // Jika stored state ada dan sama dengan current state, hanya update UI tanpa check server
            if (storedState !== null && storedState === isSubscribed && toggleBtn) {
                const permission = getNotificationPermission();
                const shouldBeChecked = isSubscribed && permission === 'granted';
                
                // Hanya update UI jika toggle state berbeda
                if (toggleBtn.checked !== shouldBeChecked) {
                    updateUI(true);
                }
                
                // Verifikasi dengan server HANYA jika sudah lebih dari 30 detik sejak check terakhir
                const timeSinceLastCheck = Date.now() - lastStatusCheckTime;
                if (timeSinceLastCheck >= STATUS_CHECK_INTERVAL) {
                    // Lakukan verifikasi secara async tanpa blocking
                    checkSubscriptionStatus().then(serverStatus => {
                        lastStatusCheckTime = Date.now();
                        if (serverStatus !== isSubscribed) {
                            // Ada ketidaksesuaian, re-initialize untuk sinkronisasi
                            initializePushNotification(true);
                        }
                    }).catch(error => {
                        console.warn('Error verifying subscription status:', error);
                    });
                }
                
                // Selalu setup toggle button saat turbo:load untuk memastikan listener ter-attach
                setupToggleButton();
                return;
            }
        }
        
        // Initialize jika belum atau state berbeda
        // HANYA gunakan forceCheck jika sudah lebih dari 30 detik sejak check terakhir
        const timeSinceLastCheck = Date.now() - lastStatusCheckTime;
        const shouldForceCheck = timeSinceLastCheck >= STATUS_CHECK_INTERVAL;
        
        initializePushNotification(shouldForceCheck);
        if (shouldForceCheck) {
            lastStatusCheckTime = Date.now();
        }
        
        // Selalu setup toggle button saat turbo:load untuk memastikan listener ter-attach
        setupToggleButton();
    });

    // Export functions untuk digunakan di global scope
    window.subscribeToPush = subscribeToPush;
    window.unsubscribeFromPush = unsubscribeFromPush;
    window.checkPushSubscriptionStatus = checkSubscriptionStatus;
    window.getNotificationPermission = getNotificationPermission;
})();