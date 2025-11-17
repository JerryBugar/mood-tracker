// Notifications page JavaScript - Optimized dengan Turbo Hotwired

// Get CSRF token dari meta tag
function getCsrfToken() {
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    return metaTag ? metaTag.getAttribute('content') : '';
}

// Fungsi untuk menampilkan toast notification
function showToast(message, type = 'success') {
    if (typeof bootstrap === 'undefined') {
        console.log('Toast tidak tersedia:', message);
        return;
    }

    const toastElement = document.getElementById('notificationToast');
    const toastMessage = document.getElementById('toast-message');
    const toastTitle = document.getElementById('toast-title');
    
    if (!toastElement || !toastMessage || !toastTitle) {
        console.log('Toast element tidak ditemukan:', message);
        return;
    }

    const toastIconWrapper = toastElement.querySelector('.toast-icon-wrapper');
    const toastIcon = toastElement.querySelector('.toast-icon');

    toastMessage.textContent = message;
    
    if (type === 'success') {
        toastTitle.textContent = 'Berhasil';
        toastTitle.classList.remove('error');
        toastElement.classList.remove('error');
        toastElement.classList.add('success');
        
        if (toastIconWrapper) toastIconWrapper.classList.remove('error');
        if (toastIcon) toastIcon.className = 'toast-icon bi bi-check-circle-fill';
    } else {
        toastTitle.textContent = 'Error';
        toastTitle.classList.add('error');
        toastElement.classList.remove('success');
        toastElement.classList.add('error');
        
        if (toastIconWrapper) toastIconWrapper.classList.add('error');
        if (toastIcon) toastIcon.className = 'toast-icon bi bi-exclamation-circle-fill';
    }

    const existingToast = bootstrap.Toast.getInstance(toastElement);
    if (existingToast) {
        existingToast.hide();
        setTimeout(() => showToastNow(toastElement), 200);
    } else {
        showToastNow(toastElement);
    }
}

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
    if (toastContainer) toastContainer.style.display = 'block';
    
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: 4000
    });
    
    toast.show();
}

// Event delegation untuk tombol mark as read (menggunakan Turbo form)
document.addEventListener('submit', async function(event) {
    const form = event.target;
    
    // Track Turbo Stream update saat form submit untuk notifications_frame
    if (form && form.hasAttribute('data-turbo-frame') && 
        form.getAttribute('data-turbo-frame') === 'notifications_frame') {
        // Track timestamp saat form submit untuk prevent double refresh
        lastTurboStreamUpdate = Date.now();
    }
    
    // Handle mark as read form
    if (form.action && form.action.includes('/notif/') && form.action.includes('/read')) {
        const button = form.querySelector('button[type="submit"]');
        if (button) {
            button.disabled = true;
            button.textContent = 'Memproses...';
        }
    }
    
    // Handle mark all as read form
    if (form.action && form.action.includes('/notif/read-all')) {
        const button = form.querySelector('button[type="submit"]');
        if (button) {
            button.disabled = true;
            button.innerHTML = '<i class="bi bi-hourglass-split"></i> Memproses...';
        }
    }
});

// Track saat Turbo Stream message diterima
document.addEventListener('turbo:before-stream-render', function(event) {
    // Cek apakah target adalah notifications_frame
    // event.detail.render adalah function, kita perlu cek event.target
    const streamElement = event.target;
    if (streamElement && streamElement.tagName === 'TURBO-STREAM') {
        const target = streamElement.getAttribute('target');
        if (target === 'notifications_frame') {
            lastTurboStreamUpdate = Date.now();
        }
    }
});

// Track previous unread count untuk detect perubahan
let previousUnreadCount = -1;

// Track waktu terakhir frame dimuat untuk mencegah reload berulang
let lastFrameLoadTime = 0;
const FRAME_RELOAD_INTERVAL = 60000; // 60 detik - hanya reload frame setiap 60 detik

// Handle Turbo Stream render untuk update langsung
document.addEventListener('turbo:frame-render', function(event) {
    // Cek apakah ini render dari notifications_frame
    if (event.target && event.target.id === 'notifications_frame') {
        // Update timestamp
        lastTurboStreamUpdate = Date.now();
        
        // Update unread count langsung tanpa delay
        const currentUnreadCount = document.querySelectorAll('.notification-item.unread').length;
        const currentTotalCount = document.querySelectorAll('.notification-item').length;
        updateUnreadCount();
        
        // Show toast untuk delete all (jika semua notifikasi dihapus)
        if (currentTotalCount === 0 && previousUnreadCount >= 0) {
            showToast('Semua notifikasi berhasil dihapus', 'success');
            previousUnreadCount = 0;
        }
        // Show toast hanya jika ada perubahan dari unread ke read
        // (previousUnreadCount > 0 dan currentUnreadCount < previousUnreadCount)
        else if (previousUnreadCount > 0 && currentUnreadCount < previousUnreadCount) {
            if (currentUnreadCount === 0) {
                showToast('Semua notifikasi ditandai sebagai sudah dibaca', 'success');
            } else {
                showToast('Notifikasi ditandai sebagai sudah dibaca', 'success');
            }
            // Update previous count
            previousUnreadCount = currentUnreadCount;
        } else {
            // Update previous count meskipun tidak ada toast
            previousUnreadCount = currentUnreadCount;
        }
    }
});

// Handle delete all dengan Turbo
document.addEventListener('click', async function(event) {
    const target = event.target.closest('[data-action="delete-all"]');
    if (!target) return;
    
    event.preventDefault();
    
    const modalElement = document.getElementById('deleteConfirmModal');
    if (!modalElement) {
        showToast('Modal tidak ditemukan', 'error');
        return;
    }
    
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    if (!confirmBtn) {
        showToast('Tombol konfirmasi tidak ditemukan', 'error');
        return;
    }
    
    // Clone button untuk reset event listener
    const newConfirmBtn = confirmBtn.cloneNode(true);
    confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
    
    newConfirmBtn.addEventListener('click', async function() {
        if (document.activeElement === newConfirmBtn) {
            newConfirmBtn.blur();
        }
        
        const button = document.querySelector('.btn-delete-all');
    if (button) {
        button.disabled = true;
            button.innerHTML = '<i class="bi bi-hourglass-split"></i> Menghapus...';
    }

        try {
            const response = await fetch('/notif/delete-all', {
                method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'text/vnd.turbo-stream.html, application/json',
                    'Turbo-Frame': 'notifications_frame'
        }
            });
            
            if (response.ok) {
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('text/vnd.turbo-stream.html')) {
                    const text = await response.text();
                    // Track Turbo Stream update
                    lastTurboStreamUpdate = Date.now();
                    // Reset previousUnreadCount karena semua akan dihapus
                    previousUnreadCount = 0;
                    if (window.Turbo) {
                        window.Turbo.renderStreamMessage(text);
                    }
                    // Toast akan muncul setelah frame render
                } else {
                    const data = await response.json();
                    if (data.success) {
                        // Reload frame dengan Turbo
                        const frame = document.getElementById('notifications_frame');
                        if (frame) {
                            lastTurboStreamUpdate = Date.now();
                            previousUnreadCount = 0;
                            frame.src = frame.src;
                        }
                    }
                }
            }
        } catch (error) {
            showToast('Terjadi kesalahan saat menghapus notifikasi', 'error');
            if (button) {
                button.disabled = false;
                button.innerHTML = '<i class="bi bi-trash-fill"></i> Hapus Semua Notif';
            }
        }
    });
    
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
});

// Update unread count setelah frame load
function updateUnreadCount() {
    const unreadItems = document.querySelectorAll('.notification-item.unread');
    const allItems = document.querySelectorAll('.notification-item');
    const markAllContainer = document.querySelector('.mark-all-container');
    
    if (!markAllContainer) return;
    
        const markAllButton = markAllContainer.querySelector('.btn-mark-all-read');
        const deleteAllButton = markAllContainer.querySelector('.btn-delete-all');
        
        if (markAllButton && (unreadItems.length === 0 || unreadItems.length <= 1)) {
        markAllButton.closest('.mark-all-container').style.display = 'none';
        }
        
        if (unreadItems.length === 0 && allItems.length > 0) {
            if (deleteAllButton) {
                markAllContainer.style.display = 'flex';
                deleteAllButton.style.display = 'inline-flex';
            }
        } else {
            if (deleteAllButton) {
                deleteAllButton.style.display = 'none';
        }
    }
}

// Setup modal handlers (hanya sekali)
if (!window.deleteModalHandlersSetup) {
    window.deleteModalHandlersSetup = true;
    
    document.addEventListener('turbo:load', function() {
    const modalElement = document.getElementById('deleteConfirmModal');
        if (!modalElement) return;

    modalElement.addEventListener('hide.bs.modal', function() {
        const activeElement = document.activeElement;
        if (modalElement.contains(activeElement) && 
            activeElement !== document.body && 
            typeof activeElement.blur === 'function') {
            activeElement.blur();
        }
    });

    modalElement.addEventListener('show.bs.modal', function() {
        modalElement.removeAttribute('aria-hidden');
    });

    modalElement.addEventListener('shown.bs.modal', function() {
        modalElement.removeAttribute('aria-hidden');
    });

    modalElement.addEventListener('hidden.bs.modal', function() {
        modalElement.setAttribute('aria-hidden', 'true');
        });
    });
}

// Track URL terakhir untuk detect navigation
let lastNotifUrl = null;

// Track timestamp terakhir Turbo Stream update untuk prevent double refresh
let lastTurboStreamUpdate = 0;
const TURBO_STREAM_UPDATE_WINDOW = 2000; // 2 detik

// Refresh notifications frame dengan fetch langsung
async function refreshNotificationsFrame() {
    // Skip refresh jika Turbo Stream baru saja update
    const timeSinceLastUpdate = Date.now() - lastTurboStreamUpdate;
    if (timeSinceLastUpdate < TURBO_STREAM_UPDATE_WINDOW) {
        return; // Skip refresh, Turbo Stream sudah update
    }
    const frame = document.getElementById('notifications_frame');
    if (!frame) return;
    
    try {
        // Fetch fresh data dari server dengan cache busting
        const url = new URL('/notif', window.location.origin);
        url.searchParams.set('_t', Date.now());
        
        const response = await fetch(url.toString(), {
            method: 'GET',
            headers: {
                'Accept': 'text/html',
                'Turbo-Frame': 'notifications_frame',
                'X-Requested-With': 'XMLHttpRequest'
            },
            cache: 'no-store'
        });
        
        if (response.ok) {
            const html = await response.text();
            // Extract content dari turbo-frame
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Ambil content dari turbo-frame
            const turboFrame = doc.querySelector('turbo-frame#notifications_frame');
            const notificationsContainer = doc.querySelector('.notifications-container');
            
            // Gunakan content dari turbo-frame jika ada, atau langsung dari container
            const content = turboFrame ? turboFrame.innerHTML : 
                          (notificationsContainer ? notificationsContainer.outerHTML : null);
            
            if (content && frame) {
                // Replace content frame dengan data fresh
                frame.innerHTML = content;
                // Update previousUnreadCount setelah refresh manual
                previousUnreadCount = document.querySelectorAll('.notification-item.unread').length;
                updateUnreadCount();
            }
        }
    } catch (error) {
        console.warn('Error refreshing notifications frame:', error);
    }
}

// Turbo events
document.addEventListener('turbo:frame-load', function(event) {
    if (event.target && event.target.id === 'notifications_frame') {
        // Update waktu terakhir frame dimuat
        lastFrameLoadTime = Date.now();
        
        // Inisialisasi previousUnreadCount saat frame load
        previousUnreadCount = document.querySelectorAll('.notification-item.unread').length;
        updateUnreadCount();
        // Reset Turbo Stream update tracking setelah frame load
        // Ini memastikan refresh manual bisa dilakukan setelah frame selesai load
        setTimeout(() => {
            lastTurboStreamUpdate = 0;
        }, TURBO_STREAM_UPDATE_WINDOW);
    }
});

// Refresh frame saat kembali ke halaman notif
document.addEventListener('turbo:load', function() {
    const currentUrl = window.location.href;
    const isNotifPage = window.location.pathname === '/notif';
    
    // Cek apakah frame sudah ada
    const frame = document.getElementById('notifications_frame');
    
    // Cek apakah ini navigasi baru ke halaman notif (bukan kembali dari navbar lain)
    if (isNotifPage && currentUrl !== lastNotifUrl) {
        lastNotifUrl = currentUrl;
        
        // Inisialisasi previousUnreadCount saat navigasi baru
        previousUnreadCount = -1;
        
        // Hanya reload frame jika sudah lebih dari 60 detik sejak load terakhir
        // Ini mencegah request berulang setiap kali navigasi
        if (frame) {
            const timeSinceLastLoad = Date.now() - lastFrameLoadTime;
            const hasContent = frame.querySelector('.notifications-container:not(.empty-state)');
            
            // Jika frame sudah punya konten dan belum 60 detik, jangan reload
            if (hasContent && timeSinceLastLoad < FRAME_RELOAD_INTERVAL) {
                // Frame masih fresh, hanya update UI
                updateUnreadCount();
                return;
            }
            
            // Reload frame hanya jika diperlukan (belum ada konten atau sudah lebih dari 60 detik)
            // Turbo Frame dengan src akan otomatis memuat konten fresh
            lastFrameLoadTime = Date.now();
        }
    } else if (!isNotifPage) {
        // Reset tracking jika bukan di halaman notif
        lastNotifUrl = null;
        lastTurboStreamUpdate = 0;
        previousUnreadCount = -1;
    }
    
    // Update unread count jika frame ada
    if (frame) {
        const hasFrameContent = frame.querySelector('.notifications-container');
        if (hasFrameContent) {
            updateUnreadCount();
        }
    }
});

// Listen untuk message dari service worker untuk refresh frame saat notifikasi baru diterima
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.addEventListener('message', function(event) {
        // Cek apakah message adalah notifikasi baru
        if (event.data && event.data.type === 'new-notification') {
            // Cek apakah user sedang di halaman notif
            const isNotifPage = window.location.pathname === '/notif';
            
            if (isNotifPage) {
                // Cek apakah frame sudah ada
                const frame = document.getElementById('notifications_frame');
                if (frame) {
                    // Trigger refresh frame (partial update, bukan full reload)
                    // Gunakan setTimeout kecil untuk memastikan tidak conflict dengan update lain
                    setTimeout(() => {
                        refreshNotificationsFrame();
                    }, 100);
                }
            }
        }
    });
}

// Export untuk global scope
window.showToast = showToast;
window.updateUnreadCount = updateUnreadCount;
