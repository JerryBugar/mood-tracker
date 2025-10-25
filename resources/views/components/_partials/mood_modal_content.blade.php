<turbo-frame id="mood_modal_content">
    <div class="text-center mb-4">
        <img src="{{ $emoticon ?? asset('logo/netral.png') }}" alt="Mood: {{ $title ?? 'Netral' }}" style="width: 60px; height: 60px;">
        <p class="mt-2 mb-3" style="font-size: 1.2rem; color: #82272c; font-weight: bold;">{{ $title ?? 'Biasa saja' }}</p>
    </div>
    <hr style="border-color: #dc3545; border-width: 2px;">

    {{-- Form untuk menyimpan mood. Gunakan data-turbo="true" (default) atau data-turbo="false" jika masih mau pakai AJAX --}}
    <form id="mood-save-form" action="{{ route('mood.save') }}" method="POST">
        @csrf
        <input type="hidden" name="mood" value="{{ $mood ?? 'netral' }}"> {{-- Kirim mood yang dipilih --}}

        <p class="mb-1">{{ $explanation ?? 'Ceritakan perasaanmu...' }}</p>
        <textarea name="reason" id="reasonInput" class="form-control mb-3" rows="3" placeholder="Coba ceritakan..."></textarea>

        <p class="mb-1">{{ $suggestion ?? 'Apa yang bisa membantumu?' }}</p>
        <textarea name="suggestion_action" id="suggestionInput" class="form-control mb-3" rows="3" placeholder="Kira-kira apa yang bisa bikin kamu ..."></textarea>

        <div class="text-end">
            {{-- Tombol Batal tetap menggunakan data-bs-dismiss --}}
            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Batal</button>
            {{-- Tombol Simpan sekarang menjadi bagian dari form --}}
            <button type="submit" class="btn btn-primary" id="submitMood">Simpan</button>
        </div>
    </form>
</turbo-frame>