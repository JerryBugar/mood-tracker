// Notifications page JavaScript

// Get CSRF token from meta tag
function getCsrfToken() {
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    return metaTag ? metaTag.getAttribute('content') : '';
}

// Fungsi untuk menampilkan toast notification
function showToast(message, type = 'success') {
    // Pastikan Bootstrap tersedia
    if (typeof bootstrap === 'undefined') {
        alert(message); // Fallback ke alert jika Bootstrap tidak tersedia
        return;
    }

    const toastElement = document.getElementById('notificationToast');
    const toastMessage = document.getElementById('toast-message');
    const toastTitle = document.getElementById('toast-title');
    
    if (!toastElement || !toastMessage || !toastTitle) {
        alert(message); // Fallback ke alert
        return;
    }

    const toastIconWrapper = toastElement.querySelector('.toast-icon-wrapper');
    const toastIcon = toastElement.querySelector('.toast-icon');

    // Update message dan title
    toastMessage.textContent = message;
    
    // Update styling berdasarkan type
    if (type === 'success') {
        toastTitle.textContent = 'Berhasil';
        toastTitle.classList.remove('error');
        toastElement.classList.remove('error');
        toastElement.classList.add('success');
        
        if (toastIconWrapper) {
            toastIconWrapper.classList.remove('error');
        }
        
        if (toastIcon) {
            toastIcon.className = 'toast-icon bi bi-check-circle-fill';
        }
    } else {
        toastTitle.textContent = 'Error';
        toastTitle.classList.add('error');
        toastElement.classList.remove('success');
        toastElement.classList.add('error');
        
        if (toastIconWrapper) {
            toastIconWrapper.classList.add('error');
        }
        
        if (toastIcon) {
            toastIcon.className = 'toast-icon bi bi-exclamation-circle-fill';
        }
    }

    // Hide toast yang sedang ditampilkan sebelumnya jika ada
    const existingToast = bootstrap.Toast.getInstance(toastElement);
    if (existingToast) {
        existingToast.hide();
        // Tunggu animasi hide selesai
        setTimeout(() => {
            showToastNow(toastElement);
        }, 200);
    } else {
        showToastNow(toastElement);
    }
}

function showToastNow(toastElement) {
    // Reset class untuk animasi
    toastElement.classList.remove('hiding', 'show');
    
    // Hapus event listener lama jika ada
    const hideHandler = toastElement._hideHandler;
    if (hideHandler) {
        toastElement.removeEventListener('hide.bs.toast', hideHandler);
    }
    
    // Buat handler baru untuk animasi hide
    const newHideHandler = function() {
        toastElement.classList.add('hiding');
    };
    toastElement._hideHandler = newHideHandler;
    toastElement.addEventListener('hide.bs.toast', newHideHandler);
    
    // Pastikan toast container visible
    const toastContainer = toastElement.closest('.toast-container');
    if (toastContainer) {
        toastContainer.style.display = 'block';
    }
    
    // Show toast
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: 4000
    });
    
    toast.show();
}

function markAsRead(notificationId) {
    fetch(`/notif/${notificationId}/read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'text/vnd.turbo-stream.html, application/json'
        }
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('text/vnd.turbo-stream.html')) {
            // Turbo Stream response - Turbo akan otomatis memproses
            return response.text().then(text => {
                // Inject stream ke Turbo untuk diproses
                const parser = new DOMParser();
                const doc = parser.parseFromString(text, 'text/html');
                const streams = doc.querySelectorAll('turbo-stream');
                streams.forEach(stream => {
                    if (window.Turbo) {
                        window.Turbo.renderStreamMessage(stream.outerHTML);
                    }
                });
                showToast('Notifikasi ditandai sebagai sudah dibaca', 'success');
                return { success: true };
            });
        } else {
            // JSON response (fallback)
            return response.json();
        }
    })
    .then(data => {
        if (data && !data.success) {
            showToast('Gagal menandai notifikasi sebagai sudah dibaca', 'error');
        }
    })
    .catch(error => {
        showToast('Terjadi kesalahan', 'error');
    });
}

function markAllAsRead() {
    const button = document.querySelector('.btn-mark-all-read');
    if (button) {
        button.disabled = true;
        button.innerHTML = '<i class="bi bi-hourglass-split"></i> Memproses...';
    }

    fetch('/notif/read-all', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'text/vnd.turbo-stream.html, application/json'
        }
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('text/vnd.turbo-stream.html')) {
            // Turbo Stream response - Turbo akan otomatis memproses
            return response.text().then(text => {
                // Inject stream ke Turbo untuk diproses
                const parser = new DOMParser();
                const doc = parser.parseFromString(text, 'text/html');
                const streams = doc.querySelectorAll('turbo-stream');
                streams.forEach(stream => {
                    if (window.Turbo) {
                        window.Turbo.renderStreamMessage(stream.outerHTML);
                    }
                });
                showToast('Semua notifikasi ditandai sebagai sudah dibaca', 'success');
                return { success: true };
            });
        } else {
            // JSON response (fallback)
            return response.json();
        }
    })
    .then(data => {
        if (data && !data.success) {
            showToast('Gagal menandai semua notifikasi sebagai sudah dibaca', 'error');
            if (button) {
                button.disabled = false;
                button.innerHTML = '<i class="bi bi-check-all"></i> Tandai Semua Sudah Dibaca';
            }
        }
    })
    .catch(error => {
        showToast('Terjadi kesalahan', 'error');
        if (button) {
            button.disabled = false;
            button.innerHTML = '<i class="bi bi-check-all"></i> Tandai Semua Sudah Dibaca';
        }
    });
}

function checkUnreadCount() {
    const unreadItems = document.querySelectorAll('.notification-item.unread');
    const allItems = document.querySelectorAll('.notification-item');
    const markAllContainer = document.querySelector('.mark-all-container');
    
    if (markAllContainer) {
        const markAllButton = markAllContainer.querySelector('.btn-mark-all-read');
        const deleteAllButton = markAllContainer.querySelector('.btn-delete-all');
        
        // Sembunyikan tombol mark all jika tidak ada unread atau jumlah unread <= 1
        if (markAllButton && (unreadItems.length === 0 || unreadItems.length <= 1)) {
            markAllButton.style.display = 'none';
        }
        
        // Tampilkan tombol delete all jika semua sudah dibaca dan ada notifikasi
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
}

function deleteAllNotifications() {
    // Tampilkan modal konfirmasi
    const modalElement = document.getElementById('deleteConfirmModal');
    const modal = new bootstrap.Modal(modalElement);
    
    // Setup event listener untuk tombol konfirmasi
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    
    // Hapus event listener lama jika ada
    const newConfirmBtn = confirmBtn.cloneNode(true);
    confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
    
    // Setup event listener baru untuk tombol konfirmasi
    newConfirmBtn.addEventListener('click', function() {
        // Modal akan ditutup otomatis karena data-bs-dismiss="modal"
        performDeleteAll();
    });
    
    // Pastikan modal tidak menggunakan aria-hidden saat focus
    modalElement.addEventListener('shown.bs.modal', function() {
        modalElement.removeAttribute('aria-hidden');
        // Hapus inert saat modal terbuka
        const modalContent = modalElement.querySelector('.modal-content');
        if (modalContent) {
            modalContent.removeAttribute('inert');
        }
    });
    
    modalElement.addEventListener('hidden.bs.modal', function() {
        // Set inert saat modal tertutup untuk mencegah focus
        const modalContent = modalElement.querySelector('.modal-content');
        if (modalContent) {
            modalContent.setAttribute('inert', 'true');
        }
    });
    
    modal.show();
}

function performDeleteAll() {
    const button = document.querySelector('.btn-delete-all');
    if (button) {
        button.disabled = true;
        button.innerHTML = '<i class="bi bi-hourglass-split"></i> Menghapus...';
    }

    fetch('/notif/delete-all', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'text/vnd.turbo-stream.html, application/json'
        }
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('text/vnd.turbo-stream.html')) {
            // Turbo Stream response - Turbo akan otomatis memproses
            return response.text().then(text => {
                // Inject stream ke Turbo untuk diproses
                const parser = new DOMParser();
                const doc = parser.parseFromString(text, 'text/html');
                const streams = doc.querySelectorAll('turbo-stream');
                streams.forEach(stream => {
                    if (window.Turbo) {
                        window.Turbo.renderStreamMessage(stream.outerHTML);
                    }
                });
                showToast('Semua notifikasi berhasil dihapus', 'success');
                return { success: true };
            });
        } else {
            // JSON response (fallback)
            return response.json();
        }
    })
    .then(data => {
        if (data && !data.success) {
            showToast(data.message || 'Gagal menghapus notifikasi', 'error');
            if (button) {
                button.disabled = false;
                button.innerHTML = '<i class="bi bi-trash-fill"></i> Hapus Semua Notif';
            }
        }
    })
    .catch(error => {
        showToast('Terjadi kesalahan saat menghapus notifikasi', 'error');
        if (button) {
            button.disabled = false;
            button.innerHTML = '<i class="bi bi-trash-fill"></i> Hapus Semua Notif';
        }
    });
}

// Export functions untuk digunakan di global scope
window.showToast = showToast;
window.markAsRead = markAsRead;
window.markAllAsRead = markAllAsRead;
window.deleteAllNotifications = deleteAllNotifications;
window.checkUnreadCount = checkUnreadCount;

// Inisialisasi saat halaman dimuat
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        checkUnreadCount();
    });
} else {
    checkUnreadCount();
}

// Setup ulang saat Turbo load
document.addEventListener('turbo:load', function() {
    checkUnreadCount();
});

