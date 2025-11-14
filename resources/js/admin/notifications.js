// Flag untuk mencegah multiple submissions
// Gunakan window object untuk menghindari redeclaration error saat Turbo replaceWith
if (typeof window.isSubmitting === 'undefined') {
    window.isSubmitting = false;
}
if (typeof window.submitHandler === 'undefined') {
    window.submitHandler = null;
}

// Fungsi untuk menampilkan toast notification
function showToast(message, type = 'success') {
    // Pastikan Bootstrap tersedia
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap tidak tersedia');
        alert(message); // Fallback ke alert jika Bootstrap tidak tersedia
        return;
    }

    const toastElement = document.getElementById('notificationToast');
    const toastMessage = document.getElementById('toast-message');
    const toastTitle = document.getElementById('toast-title');
    const toastIconWrapper = toastElement?.querySelector('.toast-icon-wrapper');
    const toastIcon = toastElement?.querySelector('.toast-icon');

    if (!toastElement || !toastMessage || !toastTitle) {
        console.error('Toast element tidak ditemukan');
        alert(message); // Fallback ke alert
        return;
    }

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
        }, 300);
    } else {
        showToastNow(toastElement);
    }
}

function showToastNow(toastElement) {
    // Reset class untuk animasi
    toastElement.classList.remove('hiding');
    
    // Show toast
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: 4500
    });
    
    // Tambahkan event listener untuk animasi hide
    toastElement.addEventListener('hide.bs.toast', function() {
        toastElement.classList.add('hiding');
    });
    
    toast.show();
}

function initializeNotificationForm() {
    const notificationForm = document.getElementById('notification-form');
    const notificationType = document.getElementById('notification-type');
    
    // Cek apakah form dan elemen yang diperlukan ada
    if (!notificationForm || !notificationType) {
        // Jangan log error jika form belum ada (mungkin tab belum aktif)
        return;
    }
    
    const employeeSelectContainer = document.getElementById('employee-select-container');
    const divisionSelectContainer = document.getElementById('division-select-container');
    const employeeSelect = document.getElementById('employee-select');
    const employeeSearchInput = document.getElementById('employee-search-input');
    const employeeDropdownList = document.getElementById('employee-dropdown-list');
    const divisionSelect = document.getElementById('division-select');

    // Ambil route dan CSRF token dari data attributes
    const notificationRoute = notificationForm.dataset.route || '/admin/notification/send';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                      notificationForm.dataset.csrf || '';

    // Hapus event listener lama jika ada
    if (window.submitHandler) {
        notificationForm.removeEventListener('submit', window.submitHandler);
    }

    // Initialize searchable dropdown untuk karyawan
    function initializeEmployeeSearchable() {
        if (!employeeSearchInput || !employeeDropdownList) return;

        const allItems = employeeDropdownList.querySelectorAll('.dropdown-item:not(.no-results)');
        let selectedItem = null;

        // Fungsi untuk filter items
        function filterItems(searchTerm) {
            const term = searchTerm.toLowerCase().trim();
            let visibleCount = 0;

            allItems.forEach(item => {
                const name = item.dataset.name.toLowerCase();
                const email = item.dataset.email.toLowerCase();
                const matches = name.includes(term) || email.includes(term);

                if (matches) {
                    item.style.display = 'flex';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            // Tampilkan "no results" jika tidak ada yang cocok
            let noResults = employeeDropdownList.querySelector('.no-results');
            if (visibleCount === 0 && term !== '') {
                if (!noResults) {
                    noResults = document.createElement('div');
                    noResults.className = 'dropdown-item no-results';
                    noResults.textContent = 'Tidak ada karyawan yang ditemukan';
                    employeeDropdownList.appendChild(noResults);
                }
                noResults.style.display = 'flex';
            } else if (noResults) {
                noResults.style.display = 'none';
            }

            // Tampilkan dropdown jika ada hasil
            if (term !== '' && visibleCount > 0) {
                employeeDropdownList.classList.add('show');
            } else if (term === '') {
                employeeDropdownList.classList.add('show');
            } else {
                // Jika tidak ada hasil, tetap tampilkan dropdown dengan pesan no results
                employeeDropdownList.classList.add('show');
            }
        }

        // Event listener untuk input search
        employeeSearchInput.addEventListener('input', function(e) {
            filterItems(this.value);
        });

        // Event listener untuk focus
        employeeSearchInput.addEventListener('focus', function() {
            if (this.value.trim() === '') {
                allItems.forEach(item => item.style.display = 'flex');
            }
            employeeDropdownList.classList.add('show');
        });

        // Event listener untuk click item
        allItems.forEach(item => {
            item.addEventListener('click', function() {
                const value = this.dataset.value;
                const name = this.dataset.name;
                const email = this.dataset.email;

                // Update hidden input
                if (employeeSelect) {
                    employeeSelect.value = value;
                }

                // Update search input dengan nama yang dipilih
                employeeSearchInput.value = `${name} (${email})`;

                // Update selected state
                if (selectedItem) {
                    selectedItem.classList.remove('selected');
                }
                this.classList.add('selected');
                selectedItem = this;

                // Sembunyikan dropdown
                employeeDropdownList.classList.remove('show');
            });
        });

        // Sembunyikan dropdown saat klik di luar
        document.addEventListener('click', function(e) {
            if (!employeeSelectContainer.contains(e.target)) {
                employeeDropdownList.classList.remove('show');
            }
        });
    }

    // Toggle visibility dropdown berdasarkan jenis notifikasi
    function toggleDropdowns() {
        const type = notificationType.value;
        
        if (type === 'individual') {
            if (employeeSelectContainer) employeeSelectContainer.style.display = 'block';
            if (divisionSelectContainer) divisionSelectContainer.style.display = 'none';
            if (employeeSelect) employeeSelect.required = true;
            if (divisionSelect) divisionSelect.required = false;
            // Initialize searchable dropdown
            setTimeout(() => initializeEmployeeSearchable(), 100);
        } else if (type === 'group') {
            if (employeeSelectContainer) employeeSelectContainer.style.display = 'none';
            if (divisionSelectContainer) divisionSelectContainer.style.display = 'block';
            if (employeeSelect) employeeSelect.required = false;
            if (divisionSelect) divisionSelect.required = true;
        } else {
            if (employeeSelectContainer) employeeSelectContainer.style.display = 'none';
            if (divisionSelectContainer) divisionSelectContainer.style.display = 'none';
            if (employeeSelect) employeeSelect.required = false;
            if (divisionSelect) divisionSelect.required = false;
        }
    }

    // Event listener untuk perubahan jenis notifikasi
    notificationType.addEventListener('change', toggleDropdowns);

    // Inisialisasi saat pertama kali load
    toggleDropdowns();

    // Buat submit handler baru
    window.submitHandler = function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Cegah multiple submissions
        if (window.isSubmitting) {
            return false;
        }

        const type = notificationType.value;
        const message = document.getElementById('message').value;
        const scheduleTime = document.getElementById('schedule-time').value;
        const userId = employeeSelect ? employeeSelect.value : null;
        const division = divisionSelect ? divisionSelect.value : null;

        // Validasi
        if (!message.trim()) {
            showToast('Pesan tidak boleh kosong!', 'error');
            return false;
        }

        if (type === 'individual' && (!userId || userId === '')) {
            showToast('Silakan pilih karyawan!', 'error');
            return false;
        }

        if (type === 'group' && !division) {
            showToast('Silakan pilih divisi!', 'error');
            return false;
        }

        // Set flag untuk mencegah multiple submissions
        window.isSubmitting = true;

        // Disable button saat submit
        const submitBtn = notificationForm.querySelector('button[type="submit"]');
        let originalText = 'Kirim Notifikasi';
        if (submitBtn) {
            originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Mengirim...';
        }

        // Kirim request ke server
        fetch(notificationRoute, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                type: type,
                message: message,
                user_id: userId || null,
                division: division || null,
                scheduled_at: scheduleTime || null
            })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.message || 'Terjadi kesalahan');
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                // Reset form
                notificationForm.reset();
                // Reset search input juga
                if (employeeSearchInput) {
                    employeeSearchInput.value = '';
                }
                // Reset selected item
                if (employeeDropdownList) {
                    const selected = employeeDropdownList.querySelector('.selected');
                    if (selected) {
                        selected.classList.remove('selected');
                    }
                }
                toggleDropdowns();
            } else {
                showToast('Gagal mengirim notifikasi: ' + (data.message || 'Terjadi kesalahan'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Terjadi kesalahan saat mengirim notifikasi: ' + error.message, 'error');
        })
        .finally(() => {
            // Reset flag dan enable button kembali
            window.isSubmitting = false;
            const btn = notificationForm.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = false;
                btn.textContent = originalText || 'Kirim Notifikasi';
            }
        });

        return false;
    };

    // Attach submit event listener
    notificationForm.addEventListener('submit', window.submitHandler);
}

// Inisialisasi saat Turbo Frame load
document.addEventListener('turbo:frame-load', function(event) {
    if (event.target.id === 'dashboard_content') {
        // Delay minimal karena animasi sudah lebih cepat
        setTimeout(() => {
            // Cek apakah form ada sebelum inisialisasi
            const notificationForm = document.getElementById('notification-form');
            if (notificationForm) {
                initializeNotificationForm();
            }
        }, 50);
    }
});

// Fallback untuk DOMContentLoaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            // Cek apakah form ada sebelum inisialisasi
            const notificationForm = document.getElementById('notification-form');
            if (notificationForm) {
                initializeNotificationForm();
            }
        }, 50);
    });
} else {
    setTimeout(() => {
        // Cek apakah form ada sebelum inisialisasi
        const notificationForm = document.getElementById('notification-form');
        if (notificationForm) {
            initializeNotificationForm();
        }
    }, 50);
}

