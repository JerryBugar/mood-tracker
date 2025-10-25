<div class="modal fade" id="moodModal" tabindex="-1" role="dialog" aria-labelledby="moodModalLabel" aria-hidden="true" data-turbo-temporary> {{-- data-turbo-temporary agar tidak di-cache Turbo --}}
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #f8f9fa; border-bottom: 1px solid #dee2e6;">
                <button type="button" class="btn-close position-absolute start-0 top-0 m-2" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="d-flex align-items-center ms-auto">
                    <img id="modalAvatar" src="" alt="Avatar" style="width: 30px; height: 30px; border-radius: 50%; margin-right: 10px; display: none;"> {{-- Awalnya disembunyikan --}}
                    <span id="modalDate" class="text-muted"></span>
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