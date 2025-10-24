<div class="modal fade" id="moodModal" tabindex="-1" role="dialog" aria-labelledby="moodModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #f8f9fa; border-bottom: 1px solid #dee2e6;">
                <button type="button" class="btn-close position-absolute start-0 top-0 m-2" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="d-flex align-items-center ms-auto">
                    <img id="modalAvatar" src="" alt="Avatar" style="width: 30px; height: 30px; border-radius: 50%; margin-right: 10px;">
                    <span id="modalDate" class="text-muted"></span>
                </div>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <img id="modalEmoticon" src="" alt="Mood" style="width: 60px; height: 60px;">
                    <p id="modalMoodText" class="mt-2 mb-3" style="font-size: 1.2rem; color: #82272c; font-weight: bold;"></p>
                </div>
                <hr style="border-color: #dc3545; border-width: 2px;">
                <p id="modalExplanation" class="mb-3"></p>
                <textarea id="reasonInput" class="form-control mb-3" rows="3" placeholder="Coba ceritakan..."></textarea>
                <p id="modalSuggestion" class="mb-3"></p>
                <textarea id="suggestionInput" class="form-control mb-3" rows="3" placeholder="Kira-kira apa yang bisa bikin kamu gak biasa aja?"></textarea>
                <div class="text-end">
                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="submitMood">Simpan</button>
                </div>
            </div>
        </div>
    </div>
</div>