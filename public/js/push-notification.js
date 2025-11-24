// Push Notification Handler untuk PWA
(function () {
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

        return;
    }

    // Gunakan window object untuk state agar persist antar Turbo navigation
    if (!window.pushNotificationState) {
        window.pushNotificationState = {
            currentSubscription: null,
            isSubscribed: false
        };
    }

    // Storage key untuk localStorage
    const STORAGE_KEY = 'push_notification_state';
    let updateUITimeout = null;

    // Get CSRF token dari meta tag
    function getCsrfToken() {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        return metaTag ? metaTag.getAttribute('content') : '';
    }

    // Helper function untuk membaca cookie
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    }

    // Refresh CSRF token dari server
    async function refreshCsrfToken() {
        try {
            const response = await fetch('/notif', {
                method: 'GET',
                headers: {
                    'Accept': 'text/html',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            if (response.ok) {
                const html = await response.text();
                // Extract CSRF token dari response HTML
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const csrfMeta = doc.querySelector('meta[name="csrf-token"]');

                if (csrfMeta) {
                    const newToken = csrfMeta.getAttribute('content');
                    // Update token di halaman saat ini
                    const currentMeta = document.querySelector('meta[name="csrf-token"]');
                    if (currentMeta) {
                        currentMeta.setAttribute('content', newToken);

                        return newToken;
                    }
                }
            }
        } catch (error) {

        }
        return getCsrfToken(); // Fallback ke token yang ada
    }

    // Simpan state ke localStorage (permanent)
    function saveStateToStorage() {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify({
                isSubscribed: window.pushNotificationState.isSubscribed,
                timestamp: Date.now()
            }));
        } catch (e) {

        }
    }

    // Load state dari localStorage
    function loadStateFromStorage() {
        try {
            const stored = localStorage.getItem(STORAGE_KEY);
            if (stored) {
                const state = JSON.parse(stored);
                return state.isSubscribed;
            }
        } catch (e) {

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
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                if (response.status === 419) {


                    // Increment reload count
                    const currentCount = parseInt(sessionStorage.getItem('push_reload_count') || '0');
                    sessionStorage.setItem('push_reload_count', (currentCount + 1).toString());
                    sessionStorage.setItem('push_last_reload', Date.now().toString());

                    window.location.reload();
                    return false;
                }
            }

            const data = await response.json();
            return data.subscribed || false;
        } catch (error) {

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
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    endpoint: subscription.endpoint,
                    keys: {
                        p256dh: arrayBufferToBase64(subscription.getKey('p256dh')),
                        auth: arrayBufferToBase64(subscription.getKey('auth'))
                    }
                })
            });

            // Cek apakah response berhasil
            if (!response.ok) {

                if (response.status === 419) {
                    // CSRF token mismatch - jangan reload, tapi throw error
                    throw new Error('CSRF token mismatch. Silakan refresh halaman dan coba lagi.');
                }
                const errorText = await response.text();

                throw new Error(`Server error: ${response.status}`);
            }

            const data = await response.json();


            if (data.success) {
                window.pushNotificationState.currentSubscription = subscription;
                window.pushNotificationState.isSubscribed = true;
                saveStateToStorage();
                updateUI(true);
                showSuccessMessage('Push notification berhasil diaktifkan!');

                return true;
            } else {
                throw new Error(data.message || 'Gagal subscribe');
            }
        } catch (error) {


            // Jika error karena CSRF token mismatch, jangan reload halaman
            if (error.message && error.message.includes('CSRF')) {

                // No reload here, just let the error propagate or handle it gracefully
            }

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
            let subscriptionToUnsubscribe = window.pushNotificationState.currentSubscription;

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

                        try {
                            const cleanupResponse = await fetch('/notif/push/cleanup', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': getCsrfToken(),
                                    'Accept': 'application/json'
                                },
                                credentials: 'same-origin'
                            });

                            if (cleanupResponse.status === 419) {

                                window.location.reload();
                                return;
                            }

                            const cleanupData = await cleanupResponse.json();
                            if (cleanupData.success) {

                            } else {

                            }
                        } catch (cleanupError) {

                        }
                    }

                    // Update state menjadi false
                    window.pushNotificationState.isSubscribed = false;
                    window.pushNotificationState.currentSubscription = null;
                    saveStateToStorage();
                    updateUI(true);

                    // Tampilkan success message
                    showSuccessMessage('Push notification berhasil dinonaktifkan');
                    return true;
                }

                window.pushNotificationState.currentSubscription = subscriptionToUnsubscribe;
            }

            // Hapus dari server terlebih dahulu
            const response = await fetch('/notif/push/unsubscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    endpoint: window.pushNotificationState.currentSubscription.endpoint
                })
            });

            const data = await response.json();

            if (data.success) {
                // Unsubscribe dari browser
                try {
                    await window.pushNotificationState.currentSubscription.unsubscribe();
                } catch (unsubError) {
                    // Jika unsubscribe dari browser gagal, tetap lanjutkan
                    // karena sudah dihapus dari server

                }

                // Pastikan state di-update dengan benar
                window.pushNotificationState.currentSubscription = null;
                window.pushNotificationState.isSubscribed = false;

                // Simpan state ke storage
                saveStateToStorage();

                // Verifikasi status dari server untuk memastikan unsubscribe berhasil
                const verifiedStatus = await checkSubscriptionStatus();
                if (verifiedStatus) {
                    // Masih terdeteksi sebagai subscribed, gunakan cleanup

                    try {
                        const cleanupResponse = await fetch('/notif/push/cleanup', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': getCsrfToken(),
                                'Accept': 'application/json'
                            },
                            credentials: 'same-origin'
                        });

                        if (cleanupResponse.status === 419) {

                            window.location.reload();
                            return;
                        }

                        const cleanupData = await cleanupResponse.json();
                        if (cleanupData.success) {

                        } else {

                        }
                    } catch (cleanupError) {

                    }
                }

                // Update UI dengan force untuk memastikan UI refresh
                updateUI(true);

                // Tampilkan success message
                showSuccessMessage('Push notification berhasil dinonaktifkan');
                return true;
            } else {
                throw new Error(data.message || 'Gagal unsubscribe');
            }
        } catch (error) {


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
                                },
                                credentials: 'same-origin'
                            });

                            if (cleanupResponse.status === 419) {

                                window.location.reload();
                                return;
                            }

                            const cleanupData = await cleanupResponse.json();
                            if (cleanupData.success) {

                            }
                        } catch (cleanupError) {

                        }
                    }

                    // Update state menjadi false
                    window.pushNotificationState.isSubscribed = false;
                    window.pushNotificationState.currentSubscription = null;
                    saveStateToStorage();
                    updateUI(true);
                }
            } catch (checkError) {

            }

            showErrorMessage('Gagal menonaktifkan push notification: ' + error.message);
            return false;
        }
    }

    // Show success message menggunakan toast container yang sama seperti profile
    function showSuccessMessage(message) {
        // Pastikan Bootstrap tersedia
        if (typeof bootstrap === 'undefined') {

            return;
        }

        const toastElement = document.getElementById('notificationToast');
        const toastMessage = document.getElementById('toast-message');
        const toastTitle = document.getElementById('toast-title');

        if (!toastElement || !toastMessage || !toastTitle) {

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

            return;
        }

        const toastElement = document.getElementById('notificationToast');
        const toastMessage = document.getElementById('toast-message');
        const toastTitle = document.getElementById('toast-title');

        if (!toastElement || !toastMessage || !toastTitle) {

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
            // Pastikan shouldBeChecked hanya true jika window.pushNotificationState.isSubscribed benar-benar true DAN permission granted
            // Ini memastikan toggle state selalu sinkron dengan window.pushNotificationState.isSubscribed state
            const shouldBeChecked = window.pushNotificationState.isSubscribed === true && permission === 'granted';



            // Selalu update toggle state jika force, atau jika state berbeda
            // Pastikan toggle selalu sinkron dengan window.pushNotificationState.isSubscribed state
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

            // Update status text berdasarkan window.pushNotificationState.isSubscribed state, bukan hanya permission
            // Status text harus sinkron dengan toggle state
            if (permission === 'denied') {
                statusText.textContent = 'Permission notifikasi ditolak';
                statusText.className = 'text-danger';
            } else if (window.pushNotificationState.isSubscribed === true) {
                // Hanya tampilkan "aktif" jika benar-benar subscribed
                statusText.textContent = 'Push notification aktif';
                statusText.className = 'text-success';
            } else {
                // Pastikan status text menunjukkan "tidak aktif" jika window.pushNotificationState.isSubscribed false
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
        // Tambahkan delay kecil untuk memastikan Turbo sudah selesai update DOM dan meta tags
        await new Promise(resolve => setTimeout(resolve, 500));

        const settingsElement = document.getElementById('push-notification-settings');
        if (!settingsElement) return;

        // Jika elemen permanent sudah di-initialize dan tidak ada forceCheck, skip
        if (settingsElement.hasAttribute('data-turbo-permanent') &&
            settingsElement.dataset.initialized === 'true' &&
            !forceCheck) {
            // Hanya update UI jika ada perubahan state yang mungkin terjadi di background
            updateUI(true);
            // Tandai sebagai initialized untuk elemen permanent
            if (settingsElement.hasAttribute('data-turbo-permanent')) {
                settingsElement.dataset.initialized = 'true';
            }
            return;
        }

        try {
            // Dapatkan status permission notifikasi
            const permission = getNotificationPermission();

            // Jika permission ditolak, langsung update UI dan tandai sebagai initialized
            if (permission === 'denied') {
                window.pushNotificationState.isSubscribed = false;
                window.pushNotificationState.currentSubscription = null;
                saveStateToStorage();
                updateUI(true);
                settingsElement.dataset.initialized = 'true';
                return;
            }

            // Dapatkan service worker registration
            const registration = await navigator.serviceWorker.ready;

            // Dapatkan subscription yang ada di browser
            const subscription = await registration.pushManager.getSubscription();



            // Jika forceCheck false (initial load), gunakan subscription browser sebagai sumber kebenaran
            if (!forceCheck) {
                // SELALU gunakan subscription browser sebagai sumber kebenaran
                // Stored state hanya sebagai fallback jika subscription browser tidak bisa diakses
                window.pushNotificationState.currentSubscription = subscription;
                window.pushNotificationState.isSubscribed = subscription !== null;



                // Simpan state terbaru
                saveStateToStorage();
                updateUI(true);
                settingsElement.dataset.initialized = 'true';
                return;
            }

            // Jika forceCheck true, lanjutkan dengan verifikasi server
            const serverStatus = await checkSubscriptionStatus();

            if (subscription) {
                // Ada subscription di browser
                window.pushNotificationState.currentSubscription = subscription;

                // Jika server tidak mencatat sebagai subscribed, berarti ada ketidaksesuaian
                if (!serverStatus) {

                    try {
                        await subscription.unsubscribe();
                        window.pushNotificationState.currentSubscription = null;
                        window.pushNotificationState.isSubscribed = false;

                    } catch (unsubError) {

                        // Jika gagal unsubscribe, tetap set state sebagai tidak subscribed
                        window.pushNotificationState.currentSubscription = null;
                        window.pushNotificationState.isSubscribed = false;
                    }
                } else {
                    // Subscription ada di browser dan di server
                    window.pushNotificationState.isSubscribed = true;
                }
            } else {
                // Tidak ada subscription di browser
                window.pushNotificationState.currentSubscription = null;
                window.pushNotificationState.isSubscribed = false;

                // Jika server masih mencatat sebagai subscribed tapi tidak ada di browser,
                // ini berarti ada ketidaksesuaian - bersihkan subscription di server
                if (serverStatus) {


                    try {
                        const cleanupResponse = await fetch('/notif/push/cleanup', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': getCsrfToken(),
                                'Accept': 'application/json'
                            },
                            credentials: 'same-origin'
                        });

                        if (cleanupResponse.status === 419) {


                            // Increment reload count
                            const currentCount = parseInt(sessionStorage.getItem('push_reload_count') || '0');
                            sessionStorage.setItem('push_reload_count', (currentCount + 1).toString());
                            sessionStorage.setItem('push_last_reload', Date.now().toString());

                            window.location.reload();
                            return;
                        }

                        const cleanupData = await cleanupResponse.json();
                        if (cleanupData.success) {
                            console.log('Subscription di server berhasil dibersihkan. Jumlah yang dihapus:', cleanupData.deleted_count || 0);
                        } else {
                            console.error('Gagal membersihkan subscription di server:', cleanupData.message);
                        }
                    } catch (cleanupError) {
                        console.error('Error cleaning up server subscription:', cleanupError);
                    }
                } else if (serverStatus && !forceCheck) {
                    // Jika initial load dan ada ketidaksesuaian, hanya log warning tanpa cleanup
                    console.warn('Server mencatat sebagai subscribed tapi tidak ada subscription di browser. Akan dibersihkan saat user toggle.');
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
            window.pushNotificationState.isSubscribed = false;
            window.pushNotificationState.currentSubscription = null;
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
        settingsElement._toggleHandler = async function (event) {
            const toggleBtn = event.target;
            if (toggleBtn.id !== 'push-notification-toggle') return;

            // Simpan state awal untuk validasi
            const previousCheckedState = toggleBtn.checked;
            const previousIsSubscribed = window.pushNotificationState.isSubscribed;

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
                        if (window.pushNotificationState.isSubscribed !== previousIsSubscribed) {
                            window.pushNotificationState.isSubscribed = previousIsSubscribed;
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
                        if (window.pushNotificationState.isSubscribed !== previousIsSubscribed) {
                            window.pushNotificationState.isSubscribed = previousIsSubscribed;
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
                if (window.pushNotificationState.isSubscribed !== previousIsSubscribed) {
                    window.pushNotificationState.isSubscribed = previousIsSubscribed;
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
        document.addEventListener('DOMContentLoaded', function () {
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
    document.addEventListener('turbo:load', function () {
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
            if (storedState !== null && storedState === window.pushNotificationState.isSubscribed && toggleBtn) {
                const permission = getNotificationPermission();
                const shouldBeChecked = window.pushNotificationState.isSubscribed && permission === 'granted';

                // Hanya update UI jika toggle state berbeda
                if (toggleBtn.checked !== shouldBeChecked) {
                    updateUI(true);
                }

                // Selalu setup toggle button saat turbo:load untuk memastikan listener ter-attach
                setupToggleButton();
                return;
            }
        }

        // Initialize dengan forceCheck=false untuk menghindari penghapusan subscription
        // User harus manual toggle jika ingin sinkronisasi dengan server
        initializePushNotification(false);

        // Selalu setup toggle button saat turbo:load untuk memastikan listener ter-attach
        setupToggleButton();
    });

    // Export functions untuk digunakan di global scope
    window.subscribeToPush = subscribeToPush;
    window.unsubscribeFromPush = unsubscribeFromPush;
    window.checkPushSubscriptionStatus = checkSubscriptionStatus;
    window.getNotificationPermission = getNotificationPermission;
})();
