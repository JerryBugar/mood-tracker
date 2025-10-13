@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Baris ini akan menengahkan konten secara vertikal dan horizontal --}}
    <div class="row justify-content-center align-items-center min-vh-100">
        {{-- Kolom ini akan mengatur lebar form. Di layar medium (md) ke atas, lebarnya 50%. Di mobile, 100%. --}}
        <div class="col-md-8 col-lg-6 col-xl-5">
            
            {{-- Menggunakan komponen card dari Bootstrap untuk tampilan yang bersih --}}
            <div class="card shadow-lg border-0">
                <div class="card-body p-4 p-md-5">
                    
                    <h1 class="h3 card-title text-center fw-bold mb-3" style="color: #82242d;">Lengkapi Data Anda</h1>
                    <p class="card-text text-center text-muted mb-4">Silakan isi data diri Anda untuk verifikasi.</p>

                    <form method="POST" action="{{ route('verification.verify') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Lengkap</label>
                            <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="division" class="form-label">Divisi</label>
                            <input id="division" type="text" name="division" class="form-control @error('division') is-invalid @enderror" value="{{ old('division') }}" required>
                            @error('division')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Role / Jabatan</label>
                            <input id="role" type="text" name="role" class="form-control @error('role') is-invalid @enderror" value="{{ old('role') }}" required>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                            <select id="jenis_kelamin" name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror" required>
                                <option value="" disabled {{ old('jenis_kelamin') ? '' : 'selected' }}>Pilih Jenis Kelamin</option>
                                <option value="Cowok" {{ old('jenis_kelamin') == 'Cowok' ? 'selected' : '' }}>Cowok</option>
                                <option value="Cewek" {{ old('jenis_kelamin') == 'Cewek' ? 'selected' : '' }}>Cewek</option>
                            </select>
                            @error('jenis_kelamin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="company_code_inputs" class="form-label">Kode Unik Perusahaan</label>
                            <div id="company_code_inputs" class="d-flex justify-content-between gap-2">
                                @for ($i = 0; $i < 8; $i++)
                                    <input type="text" name="code_part[]" class="form-control text-center code-input" inputmode="text" pattern="[a-zA-Z0-9]" maxlength="1" style="width: 3rem; height: 3rem; font-size: 1.2rem; border: 1px solid #000;" required>
                                @endfor
                            </div>
                            <input type="hidden" name="company_code" id="company_code">
                            @error('company_code')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn tracking-btn btn-lg">
                                Verifikasi & Masuk
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form[action="{{ route('verification.verify') }}"]');
    const inputs = document.querySelectorAll('.code-input');
    const hiddenInput = document.getElementById('company_code');
    const jenisKelaminSelect = document.getElementById('jenis_kelamin');
    const emoticonPreview = document.getElementById('emoticon-preview');

    // Mapping emoticon untuk cowok (tanpa angka) dan cewek (dengan angka 1)
    const emoticonMap = {
        'Cowok': {
            'netral': '{{ asset('logo/netral.png') }}',
            'senyum': '{{ asset('logo/senyum.png') }}',
            'sedih': '{{ asset('logo/sedih.png') }}',
            'nervous': '{{ asset('logo/nervous.png') }}',
            'marah': '{{ asset('logo/marah.png') }}'
        },
        'Cewek': {
            'netral': '{{ asset('logo/netral1.png') }}',
            'senyum': '{{ asset('logo/senyum1.png') }}',
            'sedih': '{{ asset('logo/sedih1.png') }}',
            'nervous': '{{ asset('logo/nervous1.png') }}',
            'marah': '{{ asset('logo/marah1.png') }}'
        }
    };

    // Fungsi untuk mengganti emoticon berdasarkan jenis kelamin
    function updateEmoticon() {
        const selectedGender = jenisKelaminSelect.value;
        if (selectedGender === 'Cowok' || selectedGender === 'Cewek') {
            // Pilih emoticon netral sebagai contoh
            const emoticonKey = 'netral';
            const emoticonPath = emoticonMap[selectedGender][emoticonKey];
            emoticonPreview.src = emoticonPath;
        }
    }

    // Tambahkan event listener untuk perubahan jenis kelamin
    if (jenisKelaminSelect && emoticonPreview) {
        jenisKelaminSelect.addEventListener('change', updateEmoticon);
    }

    if (form && inputs.length > 0 && hiddenInput) {
        inputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                // Sanitize input to be alphanumeric
                e.target.value = e.target.value.replace(/[^a-zA-Z0-9]/g, '');

                // Automatically move to the next input
                if (e.target.value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            });

            input.addEventListener('keydown', (e) => {
                // Move to the previous input on backspace if the current input is empty
                if (e.key === 'Backspace' && e.target.value.length === 0 && index > 0) {
                    inputs[index - 1].focus();
                }
            });

            // Handle paste
            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const pasteData = e.clipboardData.getData('text').replace(/[^a-zA-Z0-9]/g, '').slice(0, inputs.length - index);
                
                pasteData.split('').forEach((char, i) => {
                    if (index + i < inputs.length) {
                        inputs[index + i].value = char;
                    }
                });

                const nextFocusIndex = Math.min(index + pasteData.length, inputs.length - 1);
                inputs[nextFocusIndex].focus();
            });
        });

        form.addEventListener('submit', (e) => {
            let code = '';
            inputs.forEach(input => {
                code += input.value;
            });
            hiddenInput.value = code;
        });
    }

    // Update emoticon saat halaman dimuat jika sudah ada nilai yang dipilih
    updateEmoticon();
});
</script>
@endsection
