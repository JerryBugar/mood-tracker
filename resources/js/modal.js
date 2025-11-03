// File untuk mengelola modal mood dengan Turbo
let moodModalScriptInitialized = false;

if (!moodModalScriptInitialized) {
    moodModalScriptInitialized = true;
    
    // Fungsi untuk menutup modal dengan aman
    function closeMoodModal() {
        // Pastikan elemen ada sebelum mengakses properti style
        const modalElement = document.getElementById('moodModal');
        if (modalElement) { // Pastikan elemen ada
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            if (modalInstance) {
                modalInstance.hide();
            }
        }

        // Hapus backdrop dan class 'modal-open' jika elemen backdrop ada
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
        document.body.classList.remove('modal-open');
        document.body.style.overflow = ''; // Kembalikan scroll body
        document.body.style.paddingRight = ''; // Kembalikan padding body

        // Reset form input di dalam frame setelah modal tertutup
        const reasonInput = document.getElementById('reasonInput');
        const suggestionInput = document.getElementById('suggestionInput');
        if (reasonInput) reasonInput.value = '';
        if (suggestionInput) suggestionInput.value = '';

        // Reset konten frame ke placeholder loading setelah modal ditutup
        const frameContent = document.getElementById('mood_modal_content');
        if (frameContent) {
            // Beri sedikit delay agar tidak terlihat aneh saat menutup
            setTimeout(() => {
                frameContent.innerHTML = `<div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>`;
            }, 300); // Sesuaikan delay
        }
    }

    // Tambahkan fungsi global agar bisa diakses dari file blade
    window.closeMoodModal = closeMoodModal;

    // Event listener untuk menangani saat frame modal ditampilkan
    document.addEventListener('turbo:frame-render', function(event) {
        const frameId = event.target.id;

        if (frameId === 'mood_modal_content') {
            // Periksa apakah Bootstrap siap
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const modalElement = document.getElementById('moodModal');
                if (modalElement) {
                    // Ambil dan tampilkan avatar serta tanggal
                    const avatarImg = document.getElementById('modalAvatar');
                    const dateSpan = document.getElementById('modalDate');

                    // Atur avatar (jika ada)
                    const userAvatarUrl = document.querySelector('meta[name="user-avatar"]')?.getAttribute('content') || '';
                    if (avatarImg) {
                        if (userAvatarUrl) {
                            avatarImg.src = userAvatarUrl;
                            avatarImg.style.display = 'inline-block';
                        } else {
                            avatarImg.style.display = 'none';
                        }
                    }

                    // Atur tanggal
                    if (dateSpan) {
                        const today = new Date();
                        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                        dateSpan.textContent = today.toLocaleDateString('id-ID', options);
                    }

                    // Hapus instance lama jika ada
                    const existingModal = bootstrap.Modal.getInstance(modalElement);
                    if (existingModal) {
                        existingModal.dispose();
                    }

                    // Buat instance baru dan tampilkan
                    const myModal = new bootstrap.Modal(modalElement, {
                        backdrop: 'static', // Mencegah tutup saat klik backdrop
                        keyboard: false // Mencegah tutup dengan tombol Esc
                    });
                    myModal.show();
                }
            }
        }
    });

    // Tangani klik pada tombol tutup modal
    document.addEventListener('click', function(event) {
        const target = event.target;
        // Cek apakah tombol close modal atau tombol di dalam modal
        if (target.matches('[data-bs-dismiss="modal"]') || target.closest('[data-bs-dismiss="modal"]')) {
            // Cek apakah ini tombol di dalam #moodModal
            if (target.closest('#moodModal')) {
                closeMoodModal();
            }
        }
    });

    // Event listener untuk saat Turbo memuat ulang halaman
    document.addEventListener('turbo:load', function() {
        // Jika modal sedang terbuka saat halaman dimuat ulang dengan Turbo,
        // tutup modal untuk mencegah keadaan tidak konsisten
        const modalElement = document.getElementById('moodModal');
        if (modalElement) {
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            if (modalInstance && modalInstance._isShown) {
                modalInstance.hide();
            }
        }
    });
}