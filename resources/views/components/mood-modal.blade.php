<div class="modal fade" id="moodModal" tabindex="-1" role="dialog" aria-labelledby="moodModalLabel" aria-hidden="true" data-turbo-temporary> {{-- data-turbo-temporary agar tidak di-cache Turbo --}}
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