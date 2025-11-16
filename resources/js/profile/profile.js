// Profile page JavaScript

// Fungsi untuk menampilkan modal edit profil
function showEditProfile() {
    // Buka modal edit profil
    const modal = new bootstrap.Modal(document.getElementById('editProfileModal'));
    modal.show();
}

// Fungsi untuk menampilkan modal konfirmasi logout
function confirmLogout() {
    // Buka modal konfirmasi logout
    const modal = new bootstrap.Modal(document.getElementById('logoutConfirmModal'));
    modal.show();
}

// Event listener untuk tombol konfirmasi logout
function setupLogoutConfirm() {
    const confirmLogoutBtn = document.getElementById('confirmLogoutBtn');
    const cancelLogoutBtn = document.getElementById('cancelLogoutBtn');
    
    if (confirmLogoutBtn) {
        // Hapus event listener lama jika ada untuk mencegah duplicate
        const newBtn = confirmLogoutBtn.cloneNode(true);
        confirmLogoutBtn.parentNode.replaceChild(newBtn, confirmLogoutBtn);
        
        // Tambahkan event listener baru
        newBtn.addEventListener('click', function() {
            // Submit form logout
            document.getElementById('logout-form').submit();
        });
    }
    
    // Setup event listener untuk tombol "Tidak" - hapus focus sebelum modal ditutup
    if (cancelLogoutBtn) {
        const newCancelBtn = cancelLogoutBtn.cloneNode(true);
        cancelLogoutBtn.parentNode.replaceChild(newCancelBtn, cancelLogoutBtn);
        
        // Hapus focus saat mousedown (sebelum click) untuk mencegah aria-hidden error
        newCancelBtn.addEventListener('mousedown', function(e) {
            // Hapus focus sebelum Bootstrap menutup modal
            if (document.activeElement === newCancelBtn) {
                newCancelBtn.blur();
            }
        }, { passive: true });
        
        // Juga hapus focus saat click sebagai backup
        newCancelBtn.addEventListener('click', function(e) {
            // Hapus focus sebelum modal ditutup untuk mencegah aria-hidden error
            if (document.activeElement === newCancelBtn) {
                newCancelBtn.blur();
            }
        });
    }
    
    // Setup event listener untuk tombol close (X)
    const closeLogoutModalBtn = document.getElementById('closeLogoutModalBtn');
    if (closeLogoutModalBtn) {
        const newCloseBtn = closeLogoutModalBtn.cloneNode(true);
        closeLogoutModalBtn.parentNode.replaceChild(newCloseBtn, closeLogoutModalBtn);
        
        // Hapus focus saat mousedown (sebelum click)
        newCloseBtn.addEventListener('mousedown', function(e) {
            // Hapus focus sebelum Bootstrap menutup modal
            if (document.activeElement === newCloseBtn) {
                newCloseBtn.blur();
            }
        }, { passive: true });
        
        // Juga hapus focus saat click sebagai backup
        newCloseBtn.addEventListener('click', function(e) {
            if (document.activeElement === newCloseBtn) {
                newCloseBtn.blur();
            }
        });
    }
}

// Setup saat DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupLogoutConfirm);
} else {
    setupLogoutConfirm();
}

// Setup saat Turbo load
document.addEventListener('turbo:load', setupLogoutConfirm);

// Setup aria-hidden handlers untuk modal logout dan edit profil
if (typeof window.profileModalAriaHandlersSetup === 'undefined') {
    window.profileModalAriaHandlersSetup = false;
}

function setupProfileModalAriaHandlers() {
    // Hanya setup sekali untuk menghindari duplikasi
    if (window.profileModalAriaHandlersSetup) {
        return;
    }
    
    // Setup untuk modal logout
    const logoutModal = document.getElementById('logoutConfirmModal');
    if (logoutModal) {
        // Hapus focus dari semua elemen yang bisa di-focus sebelum modal disembunyikan
        logoutModal.addEventListener('hide.bs.modal', function() {
            // Hapus focus dari semua elemen yang bisa di-focus di dalam modal
            const focusableElements = logoutModal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            focusableElements.forEach(element => {
                if (element === document.activeElement && typeof element.blur === 'function') {
                    element.blur();
                }
            });
            
            // Juga hapus focus dari elemen aktif jika masih di dalam modal
            const activeElement = document.activeElement;
            if (logoutModal.contains(activeElement) && 
                activeElement !== document.body && 
                activeElement !== document.documentElement &&
                typeof activeElement.blur === 'function') {
                activeElement.blur();
            }
        });
        
        logoutModal.addEventListener('hidden.bs.modal', function() {
            // Pastikan tidak ada elemen yang masih focused setelah modal tersembunyi
            const activeElement = document.activeElement;
            if (activeElement && 
                logoutModal.contains(activeElement) && 
                activeElement !== document.body && 
                activeElement !== document.documentElement &&
                typeof activeElement.blur === 'function') {
                activeElement.blur();
            }
        });
    }
    
    // Setup untuk modal edit profil
    const editModal = document.getElementById('editProfileModal');
    if (editModal) {
        // Hapus focus dari semua elemen yang bisa di-focus sebelum modal disembunyikan
        editModal.addEventListener('hide.bs.modal', function() {
            // Hapus focus dari semua elemen yang bisa di-focus di dalam modal
            const focusableElements = editModal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            focusableElements.forEach(element => {
                if (element === document.activeElement && typeof element.blur === 'function') {
                    element.blur();
                }
            });
            
            // Juga hapus focus dari elemen aktif jika masih di dalam modal
            const activeElement = document.activeElement;
            if (editModal.contains(activeElement) && 
                activeElement !== document.body && 
                activeElement !== document.documentElement &&
                typeof activeElement.blur === 'function') {
                activeElement.blur();
            }
        });
        
        editModal.addEventListener('hidden.bs.modal', function() {
            // Pastikan tidak ada elemen yang masih focused setelah modal tersembunyi
            const activeElement = document.activeElement;
            if (activeElement && 
                editModal.contains(activeElement) && 
                activeElement !== document.body && 
                activeElement !== document.documentElement &&
                typeof activeElement.blur === 'function') {
                activeElement.blur();
            }
        });
    }
    
    window.profileModalAriaHandlersSetup = true;
}

// Setup aria-hidden handlers saat DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupProfileModalAriaHandlers);
} else {
    setupProfileModalAriaHandlers();
}

// Setup ulang saat Turbo load (jika modal belum di-setup)
document.addEventListener('turbo:load', setupProfileModalAriaHandlers);

// Fungsi untuk menangani submit form edit profil
document.addEventListener('submit', function(e) {
    if (e.target.id === 'edit-profile-form') {
        e.preventDefault(); // Mencegah submit standar
        
        // Cegah pengiriman ganda dengan menonaktifkan tombol submit
        const submitButton = e.target.querySelector('button[type="submit"]');
        if (submitButton.disabled) {
            // Jika tombol sudah dinonaktifkan, hentikan eksekusi
            return;
        }
        
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...';
        
        const formData = new FormData(e.target);
        
        fetch(e.target.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-HTTP-Method-Override': 'PUT',
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
                    
                    // Tutup modal
                    const modalElement = document.getElementById('editProfileModal');
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                    }
                    
                    // Tampilkan notifikasi Toast berhasil
                    showToast('Profil berhasil diperbarui!', 'success');
                    
                    return { success: true };
                });
            } else {
                // JSON response (fallback)
                return response.json();
            }
        })
        .then(data => {
            if (data && !data.success) {
                alert('Terjadi kesalahan saat memperbarui profil: ' + (data.message || 'Silakan coba lagi'));
            }
        })
        .catch(error => {
            alert('Terjadi kesalahan saat memperbarui profil. Silakan coba lagi.');
        })
        .finally(() => {
            // Selalu aktifkan kembali tombol submit setelah selesai
            submitButton.disabled = false;
            submitButton.innerHTML = 'Simpan';
        });
    }
});

// Fungsi untuk menampilkan toast notification
function showToast(message, type = 'success') {
    // Pastikan Bootstrap tersedia
    if (typeof bootstrap === 'undefined') {
        alert(message); // Fallback ke alert jika Bootstrap tidak tersedia
        return;
    }

    const toastElement = document.getElementById('successToast');
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

// Export functions untuk digunakan di global scope jika diperlukan
window.showEditProfile = showEditProfile;
window.confirmLogout = confirmLogout;
window.showToast = showToast;

