<turbo-frame id="mood_modal_content">
    <div class="edit-mood-container">
        <!-- Internal Header for the Edit View -->
        <div class="text-center mb-4 position-relative">
            <h5 class="fw-bold" style="color: #82272c;">
                <i class="bi bi-pencil-square me-2"></i>Edit Catatan Mood
            </h5>
            <p class="text-muted small">{{ $record->formatted_date }}</p>
        </div>

        <form action="{{ route('mood.update', $record->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row g-0 border rounded-3 overflow-hidden shadow-sm">
                <!-- Left Side: Mood Selection -->
                <div class="col-md-5 bg-light border-end">
                    <div class="p-3 h-100 d-flex flex-column">
                        <h6 class="text-muted text-uppercase fw-bold mb-3" style="font-size: 0.75rem; letter-spacing: 1px;">Pilih Mood</h6>
                        
                        <div class="mood-selection-container flex-grow-1 d-flex flex-column justify-content-center gap-2">
                            @foreach($moodData as $key => $data)
                            <div class="form-check mood-option-card p-0 position-relative">
                                <input class="form-check-input position-absolute opacity-0" type="radio" name="mood" id="edit_mood_{{ $key }}" value="{{ $key }}" {{ $record->mood == $key ? 'checked' : '' }} required style="z-index: 1; width: 100%; height: 100%; cursor: pointer;">
                                <label class="form-check-label d-flex align-items-center p-2 rounded-3 border w-100 transition-all" for="edit_mood_{{ $key }}" style="cursor: pointer; transition: all 0.2s ease;">
                                    <img src="{{ $emoticonPaths[$key] }}" alt="{{ $data['title'] }}" width="32" height="32" class="me-2">
                                    <div>
                                        <span class="d-block fw-bold text-dark" style="font-size: 0.9rem;">{{ $data['title'] }}</span>
                                    </div>
                                    <div class="ms-auto check-indicator">
                                        <i class="bi bi-check-circle-fill text-success d-none"></i>
                                    </div>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                <!-- Right Side: Details -->
                <div class="col-md-7 bg-white">
                    <div class="p-3">
                        <div class="mb-3">
                            <label for="edit_reason" class="form-label fw-bold text-dark small">
                                <i class="bi bi-journal-text me-1 text-primary"></i>Ceritakan apa yang terjadi?
                            </label>
                            <textarea class="form-control bg-light p-2" id="edit_reason" name="reason" rows="3" placeholder="Tuliskan ceritamu di sini..." style="border-radius: 10px; resize: none; font-size: 0.95rem;" required>{{ $record->reason }}</textarea>
                            <div class="form-text text-end mt-0"><small>Minimal 1 karakter</small></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_suggestion_action" class="form-label fw-bold text-dark small">
                                <i class="bi bi-lightbulb me-1 text-warning"></i>Apa yang bisa bikin lebih baik?
                            </label>
                            <textarea class="form-control bg-light p-2" id="edit_suggestion_action" name="suggestion_action" rows="2" placeholder="Tuliskan ide atau rencanamu..." style="border-radius: 10px; resize: none; font-size: 0.95rem;" required>{{ $record->suggestion_action }}</textarea>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                            <button type="button" class="btn btn-light btn-sm px-3 rounded-pill" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary btn-sm px-4 rounded-pill fw-bold" style="background-color: #82272c; border-color: #82272c;">
                                <i class="bi bi-save me-1"></i>Simpan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <style>
        .mood-option-card input:checked + label {
            background-color: #fff0f1;
            border-color: #82272c !important;
            box-shadow: 0 2px 5px rgba(130, 39, 44, 0.1);
        }
        .mood-option-card input:checked + label .check-indicator .bi-check-circle-fill {
            display: block !important;
            color: #82272c !important;
        }
        .mood-option-card label:hover {
            background-color: #f8f9fa;
        }
    </style>
</turbo-frame>
