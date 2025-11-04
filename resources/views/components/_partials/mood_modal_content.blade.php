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

        <p class="mb-1">{{ $explanation ?? 'Ceritakan perasaanmu...' }} <span class="text-danger">*</span></p>
        <textarea name="reason" id="reasonInput" class="form-control mb-3" rows="3" placeholder="Coba ceritakan..." required></textarea>

        <p class="mb-1">{{ $suggestion ?? 'Apa yang bisa membantumu?' }} <span class="text-danger">*</span></p>
        <textarea name="suggestion_action" id="suggestionInput" class="form-control mb-3" rows="3" placeholder="Kira-kira apa yang bisa bikin kamu ..." required></textarea>

        <div class="text-end">
            {{-- Tombol Simpan sekarang menjadi bagian dari form --}}
            <button type="submit" class="btn" style="background-color: #83282f; color: white; border: none;" id="submitMood">Simpan <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#ffffff"><path d="M480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q65 0 123 19t107 53l-58 59q-38-24-81-37.5T480-800q-133 0-226.5 93.5T160-480q0 133 93.5 226.5T480-160q133 0 226.5-93.5T800-480q0-18-2-36t-6-35l65-65q11 32 17 66t6 70q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm-56-216L254-466l56-56 114 114 400-401 56 56-456 457Z"/></svg></button>
        </div>
    </form>
</turbo-frame>