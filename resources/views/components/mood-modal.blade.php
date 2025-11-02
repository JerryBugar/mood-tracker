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
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            if (modalInstance && modalInstance._isShown) {
                modalInstance.hide();
            }
        }
    });

    // Amati perubahan pada body atau elemen utama untuk mendeteksi perubahan DOM
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            // Jika sebuah node baru ditambahkan dan mengandung modal
            if (mutation.type === 'childList') {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Jika node adalah elemen
                        // Cek apakah elemen yang ditambahkan adalah modal itu sendiri
                        if (node.id === 'moodModal') {
                            // Lakukan inisialisasi khusus jika diperlukan
                            console.log('Mood modal added to DOM');
                        }
                        
                        // Cek apakah elemen mengandung modal
                        const modalInNode = node.querySelector('#moodModal');
                        if (modalInNode) {
                            // Pastikan modal tidak memiliki instance ganda
                            const existingModal = bootstrap.Modal.getInstance(modalInNode);
                            if (existingModal) {
                                existingModal.dispose();
                            }
                            
                            console.log('Mood modal detected in added content');
                        }
                    }
                });
            }
        });
    });

    // Mulai mengamati perubahan DOM
    observer.observe(document.documentElement, {
        childList: true,
        subtree: true
    });

    // Hapus observer saat halaman berpindah untuk mencegah kebocoran memori
    document.addEventListener('turbo:before-render', function() {
        observer.disconnect();
    });
}
</script>