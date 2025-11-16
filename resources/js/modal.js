// File untuk mengelola modal mood dengan Turbo
let moodModalScriptInitialized = false;

if (!moodModalScriptInitialized) {
    moodModalScriptInitialized = true;
    
    // Fungsi untuk menutup modal dengan aman
    function closeMoodModal() {
        // Pastikan elemen ada sebelum mengakses properti style
        const modalElement = document.getElementById('moodModal');
        if (modalElement) { // Pastikan elemen ada
            // Hapus focus dari semua elemen yang bisa di-focus sebelum modal ditutup
            const focusableElements = modalElement.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            focusableElements.forEach(element => {
                if (element === document.activeElement && typeof element.blur === 'function') {
                    element.blur();
                }
            });

            // Juga hapus focus dari elemen aktif jika masih di dalam modal
            const activeElement = document.activeElement;
            if (modalElement.contains(activeElement) && 
                activeElement !== document.body && 
                activeElement !== document.documentElement &&
                typeof activeElement.blur === 'function') {
                activeElement.blur();
            }

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

    // Fungsi untuk memuat modal mood tanpa pindah halaman
    async function loadMoodModal(moodType) {
        try {
            // Fetch konten modal secara langsung
            const response = await fetch(`/mood/modal?mood=${encodeURIComponent(moodType)}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const htmlContent = await response.text();
            
            // Dapatkan elemen frame
            const frame = document.getElementById('mood_modal_content');
            if (frame) {
                // Set innerHTML dengan konten yang diterima
                frame.innerHTML = htmlContent;
                
                // Trigger event turbo:frame-render secara manual karena kita mengganti konten secara manual
                const frameRenderEvent = new CustomEvent('turbo:frame-render', {
                    bubbles: true,
                    detail: { target: frame }
                });
                frame.dispatchEvent(frameRenderEvent);
            }
        } catch (error) {
            // Tampilkan pesan error ke pengguna
            const frame = document.getElementById('mood_modal_content');
            if (frame) {
                frame.innerHTML = `<div class="alert alert-danger">Gagal memuat modal mood. Silakan coba lagi.</div>`;
            }
        }
    }
    
    // Tambahkan fungsi global agar bisa diakses dari file blade
    window.loadMoodModal = loadMoodModal;
    
    // Tambahkan event listener untuk validasi form sebelum dikirim
    document.addEventListener('submit', function(event) {
        if (event.target.id === 'mood-save-form') {
            const reasonInput = event.target.querySelector('#reasonInput');
            const suggestionInput = event.target.querySelector('#suggestionInput');

            if (!reasonInput.value.trim() || !suggestionInput.value.trim()) {
                event.preventDefault(); // Mencegah pengiriman form
                event.stopPropagation(); // Menghentikan propagasi
                
                // Tampilkan pesan error
                alert('Tolong diisi semua formnya');
                
                // Fokus ke field pertama yang kosong
                if (!reasonInput.value.trim()) {
                    reasonInput.focus();
                } else if (!suggestionInput.value.trim()) {
                    suggestionInput.focus();
                }
                
                return false;
            }
        }
    });
    
    // Tambahkan event listener untuk menangani saat frame dimuat untuk memastikan validasi dipasang
    document.addEventListener('turbo:frame-render', function(event) {
        if (event.target.id === 'mood_modal_content') {
            // Setelah frame dimuat, tambahkan handler submit ke form di dalam frame
            const form = event.target.querySelector('#mood-save-form');
            if (form) {
                // Hapus event listener yang mungkin sudah ada untuk menghindari duplikasi
                form.removeEventListener('submit', handleFormSubmit);
                // Tambahkan event listener baru
                form.addEventListener('submit', handleFormSubmit);
            }
        }
    });
    
    // Fungsi untuk menangani submit form
    function handleFormSubmit(event) {
        const reasonInput = event.target.querySelector('#reasonInput');
        const suggestionInput = event.target.querySelector('#suggestionInput');

        if (!reasonInput.value.trim() || !suggestionInput.value.trim()) {
            event.preventDefault(); // Mencegah pengiriman form
            event.stopPropagation(); // Menghentikan propagasi
            
            // Tampilkan pesan error
            alert('Tolong diisi semua formnya');
            
            // Fokus ke field pertama yang kosong
            if (!reasonInput.value.trim()) {
                reasonInput.focus();
            } else if (!suggestionInput.value.trim()) {
                suggestionInput.focus();
            }
            
            return false;
        }
    }

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