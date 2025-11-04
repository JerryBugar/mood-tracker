<div class="modal fade" id="moodModal" tabindex="-1" role="dialog" aria-labelledby="moodModalLabel" aria-hidden="true" data-turbo-permanent> {{-- data-turbo-permanent agar modal tetap ada saat navigasi --}}
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #83282f; border-bottom: 1px solid #dee2e6; color: #ffffff;">
                <button type="button" class="btn-close position-absolute start-0 top-0 m-2" style="background: none; border: none; cursor: pointer;" data-bs-dismiss="modal" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#ffffffff">
                        <path d="M400-80 0-480l400-400 71 71-329 329 329 329-71 71Z"/>
                    </svg>
                </button>
                <div class="d-flex align-items-center justify-content-center flex-grow-1">
                    <img id="modalAvatar" src="" alt="Avatar" style="width: 30px; height: 30px; border-radius: 50%; margin-right: 10px; display: none;"> {{-- Awalnya disembunyikan --}}
                    <span id="modalDate" class="text-white"></span>
                </div>
            </div>
            <div class="modal-body">
                {{-- Turbo frame ini akan diisi oleh respons dari server --}}
                <turbo-frame id="mood_modal_content">
                    {{-- Kosongkan di awal atau beri placeholder loading jika mau --}}
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </turbo-frame>
            </div>
        </div>
    </div>
</div>

<script>
// Tambahkan flag untuk memastikan skrip hanya dijalankan sekali
if (!window.moodModalScriptInitialized) {
    window.moodModalScriptInitialized = true;
    
    // Gunakan MutationObserver untuk mendeteksi perubahan DOM akibat Turbo
    document.addEventListener('turbo:load', function() {
        // Jika modal sedang terbuka saat halaman dimuat ulang dengan Turbo,
        // tutup modal untuk mencegah keadaan tidak konsisten
        const modalElement = document.getElementById('moodModal');
        if (modalElement) {
            // Pastikan hanya satu instance modal Bootstrap yang aktif
            const existingModalInstance = bootstrap.Modal.getInstance(modalElement);
            if (existingModalInstance) {
                // Jangan dispose instance karena modal bersifat permanen
            } else {
                // Jika tidak ada instance, kita tidak perlu membuat baru karena modal permanen
            }
        }
    });

    // Tambahkan event listener untuk form submit di dalam frame setelah konten frame dimuat
    document.addEventListener('turbo:frame-load', function(event) {
        if (event.target.id === 'mood_modal_content') {
            const form = event.target.querySelector('#mood-save-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const reasonInput = form.querySelector('#reasonInput');
                    const suggestionInput = form.querySelector('#suggestionInput');
                    let isValid = true;

                    if (!reasonInput.value.trim() || !suggestionInput.value.trim()) {
                        isValid = false;
                    }

                    if (!isValid) {
                        e.preventDefault(); // Batalkan pengiriman jika tidak valid
                        alert('Tolong diisi semua formnya'); // Tampilkan pesan error
                        
                        // Opsional: Fokus ke field pertama yang kosong
                        if (!reasonInput.value.trim()) {
                            reasonInput.focus();
                        } else if (!suggestionInput.value.trim()) {
                            suggestionInput.focus();
                        }
                        return false;
                    }
                });
            }
        }
    });
}
</script>