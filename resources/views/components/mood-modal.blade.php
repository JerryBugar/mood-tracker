<div id="moodModal" 
     class="modal fade" 
     tabindex="-1" 
     role="dialog" 
     aria-labelledby="moodModalLabel" 
     aria-hidden="true" 
     data-turbo-permanent
     data-bs-backdrop="static"
     data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #83282f; border-bottom: 1px solid #dee2e6; color: #ffffff;">
                <button type="button" 
                        class="btn-close position-absolute start-0 top-0 m-2" 
                        style="background: none; border: none; cursor: pointer;" 
                        data-bs-dismiss="modal" 
                        aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#ffffffff">
                        <path d="M400-80 0-480l400-400 71 71-329 329 329 329-71 71Z"/>
                    </svg>
                </button>
                <div class="d-flex align-items-center justify-content-center flex-grow-1">
                    <img id="modalAvatar" 
                         src="" 
                         alt="Avatar" 
                         style="width: 30px; height: 30px; border-radius: 50%; margin-right: 10px; display: none;"
                         aria-hidden="true"> 
                    <span id="modalDate" class="text-white"></span>
                </div>
            </div>
            <div class="modal-body">
                <turbo-frame id="mood_modal_content">
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
// Optimize the turbo permanent implementation by using a more efficient approach
// The modal remains persistent across navigation with better lifecycle management
if (!window.moodModalInitialized) {
    window.moodModalInitialized = true;

    // Handle Turbo navigation to ensure modal state consistency
    document.addEventListener('turbo:before-render', function() {
        const modalElement = document.getElementById('moodModal');
        if (modalElement) {
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            if (modalInstance && modalInstance._isShown) {
                // Store modal state before Turbo render
                sessionStorage.setItem('moodModalWasOpen', 'true');
                modalInstance.hide();
            } else {
                sessionStorage.removeItem('moodModalWasOpen');
            }
            
            // PENTING: Reset Turbo Frame src saat navigasi
            const frameContent = document.getElementById('mood_modal_content');
            if (frameContent) {
                frameContent.removeAttribute('src');
            }
        }
    });

    // Tambahkan reset pada turbo:load untuk memastikan state bersih
    document.addEventListener('turbo:load', function() {
        const frameContent = document.getElementById('mood_modal_content');
        if (frameContent) {
            // Reset src jika ada, kecuali modal sedang terbuka
            const modalElement = document.getElementById('moodModal');
            const modalInstance = modalElement ? bootstrap.Modal.getInstance(modalElement) : null;
            
            // Hanya reset jika modal TIDAK sedang terbuka
            if (!modalInstance || !modalInstance._isShown) {
                frameContent.removeAttribute('src');
            }
        }
    });

    document.addEventListener('turbo:render', function() {
        // Restore modal after Turbo render if it was previously open
        if (sessionStorage.getItem('moodModalWasOpen') === 'true') {
            sessionStorage.removeItem('moodModalWasOpen');
            // Use setTimeout to ensure DOM is fully rendered before showing modal
            setTimeout(() => {
                const modalElement = document.getElementById('moodModal');
                if (modalElement && typeof bootstrap !== 'undefined') {
                    const modalInstance = new bootstrap.Modal(modalElement, {
                        backdrop: 'static',
                        keyboard: false
                    });
                    modalInstance.show();
                }
            }, 0);
        }
    });

    // Handle form validation directly on submit
    document.addEventListener('turbo:submit-start', function(event) {
        if (event.target.id === 'mood-save-form') {
            const form = event.target;
            const reasonInput = form.querySelector('#reasonInput');
            const suggestionInput = form.querySelector('#suggestionInput');

            if (!reasonInput?.value.trim() || !suggestionInput?.value.trim()) {
                event.preventDefault(); // Prevent the Turbo form submission
                
                // Show validation error
                alert('Tolong diisi semua formnya');
                
                // Focus on the first empty field
                if (!reasonInput.value.trim()) {
                    reasonInput.focus();
                } else if (!suggestionInput.value.trim()) {
                    suggestionInput.focus();
                }
                
                return false;
            }
        }
    });

    // Clean up any temporary data when modal is closed
    document.addEventListener('hidden.bs.modal', function(event) {
        if (event.target.id === 'moodModal') {
            // Reset form fields when modal is hidden
            const reasonInput = document.getElementById('reasonInput');
            const suggestionInput = document.getElementById('suggestionInput');
            
            if (reasonInput) reasonInput.value = '';
            if (suggestionInput) suggestionInput.value = '';
            
            // Reset the frame content to loading state
            const frameContent = document.getElementById('mood_modal_content');
            if (frameContent) {
                // PENTING: Reset src attribute dulu sebelum innerHTML
                frameContent.removeAttribute('src');
                
                frameContent.innerHTML = `
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                `;
            }
            
            // Juga hapus flag sessionStorage jika ada
            sessionStorage.removeItem('moodModalWasOpen');
        }
    });

    // Setup aria-hidden handlers untuk modal mood
    if (typeof window.moodModalAriaHandlersSetup === 'undefined') {
        window.moodModalAriaHandlersSetup = false;
    }

    function setupMoodModalAriaHandlers() {
        // Hanya setup sekali untuk menghindari duplikasi
        if (window.moodModalAriaHandlersSetup) {
            return;
        }

        const moodModal = document.getElementById('moodModal');
        if (!moodModal) {
            return;
        }

        // Hapus focus dari semua elemen yang bisa di-focus sebelum modal disembunyikan
        moodModal.addEventListener('hide.bs.modal', function() {
            // Hapus focus dari semua elemen yang bisa di-focus di dalam modal
            const focusableElements = moodModal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            focusableElements.forEach(element => {
                if (element === document.activeElement && typeof element.blur === 'function') {
                    element.blur();
                }
            });

            // Juga hapus focus dari elemen aktif jika masih di dalam modal
            const activeElement = document.activeElement;
            if (moodModal.contains(activeElement) && 
                activeElement !== document.body && 
                activeElement !== document.documentElement &&
                typeof activeElement.blur === 'function') {
                activeElement.blur();
            }
        });

        moodModal.addEventListener('hidden.bs.modal', function() {
            // Pastikan tidak ada elemen yang masih focused setelah modal tersembunyi
            const activeElement = document.activeElement;
            if (activeElement && 
                moodModal.contains(activeElement) && 
                activeElement !== document.body && 
                activeElement !== document.documentElement &&
                typeof activeElement.blur === 'function') {
                activeElement.blur();
            }
        });

        // Setup event listener untuk tombol close - hapus focus sebelum modal ditutup
        const closeBtn = moodModal.querySelector('[data-bs-dismiss="modal"]');
        if (closeBtn) {
            // Hapus focus saat mousedown (sebelum click) untuk mencegah aria-hidden error
            closeBtn.addEventListener('mousedown', function(e) {
                // Hapus focus sebelum Bootstrap menutup modal
                if (document.activeElement === closeBtn) {
                    closeBtn.blur();
                }
            }, { passive: true });

            // Juga hapus focus saat click sebagai backup
            closeBtn.addEventListener('click', function(e) {
                // Hapus focus sebelum modal ditutup untuk mencegah aria-hidden error
                if (document.activeElement === closeBtn) {
                    closeBtn.blur();
                }
            });
        }

        window.moodModalAriaHandlersSetup = true;
    }

    // Setup aria-hidden handlers saat DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupMoodModalAriaHandlers);
    } else {
        setupMoodModalAriaHandlers();
    }

    // Setup ulang saat Turbo load (jika modal belum di-setup)
    document.addEventListener('turbo:load', setupMoodModalAriaHandlers);
}
</script>