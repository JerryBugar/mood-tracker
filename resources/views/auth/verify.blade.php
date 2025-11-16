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

                    <form method="POST" action="{{ route('verification.verify') }}" data-turbo="false">
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
                            <select id="division" name="division" class="form-select @error('division') is-invalid @enderror" required>
                                <option value="" disabled {{ old('division') ? '' : 'selected' }}>Pilih Divisi</option>
                                <option value="Marketing" {{ old('division') == 'Marketing' ? 'selected' : '' }}>Marketing</option>
                                <option value="Product" {{ old('division') == 'Product' ? 'selected' : '' }}>Product</option>
                                <option value="IT" {{ old('division') == 'IT' ? 'selected' : '' }}>IT</option>
                                <option value="CDS" {{ old('division') == 'CDS' ? 'selected' : '' }}>CDS</option>
                                <option value="HRD" {{ old('division') == 'HRD' ? 'selected' : '' }}>HRD</option>
                            </select>
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
                                <option value="Laki-laki" {{ old('jenis_kelamin') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="Perempuan" {{ old('jenis_kelamin') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                            @error('jenis_kelamin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="company_code_inputs" class="form-label">Kode Unik Perusahaan</label>
                            <div id="company_code_inputs" class="d-flex flex-wrap justify-content-between gap-2">
                                @for ($i = 0; $i < 8; $i++)
                                    <input type="text" name="code_part[]" class="form-control text-center code-input" inputmode="text" pattern="[a-zA-Z0-9]" maxlength="1" style="flex: 1; min-width: calc((100% - 7 * 0.5rem) / 8); height: 3rem; font-size: 1.2rem; border: 2px solid #000;" required>
                                @endfor
                            </div>
                            <input type="hidden" name="company_code" id="company_code">
                            @error('company_code')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div id="rate-limit-error" class="invalid-feedback d-block" style="display: none;"></div>
                            @if(isset($rateLimitBlocked) && $rateLimitBlocked)
                                <div class="alert alert-danger mt-2" id="rate-limit-alert">
                                    <strong>Akun Terblokir!</strong><br>
                                    Terlalu banyak percobaan. Silakan coba lagi dalam <span id="countdown-timer">{{ ceil($rateLimitSeconds / 60) }}</span> menit.
                                </div>
                            @endif
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn tracking-btn btn-lg" id="submit-btn">
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
    const submitBtn = document.getElementById('submit-btn');
    const rateLimitError = document.getElementById('rate-limit-error');
    const rateLimitAlert = document.getElementById('rate-limit-alert');
    let countdownTimer = document.getElementById('countdown-timer');
    const jenisKelaminSelect = document.getElementById('jenis_kelamin');
    const emoticonPreview = document.getElementById('emoticon-preview');

    // Rate limit status dari server
    let rateLimitBlocked = {{ isset($rateLimitBlocked) && $rateLimitBlocked ? 'true' : 'false' }};
    let rateLimitSeconds = {{ isset($rateLimitSeconds) ? $rateLimitSeconds : 0 }};
    const rateLimitRemaining = {{ isset($rateLimitRemaining) ? $rateLimitRemaining : 3 }};

    // Fungsi untuk disable form saat rate limit
    function disableForm() {
        if (rateLimitBlocked || rateLimitSeconds > 0) {
            form.querySelectorAll('input, select, button').forEach(el => {
                el.disabled = true;
                el.style.opacity = '0.6';
                el.style.cursor = 'not-allowed';
            });
            if (submitBtn) {
                submitBtn.textContent = 'Form Terblokir - Silakan Tunggu';
            }
        }
    }

    // Fungsi untuk update countdown timer
    function updateCountdown() {
        if (rateLimitSeconds > 0 && countdownTimer) {
            const minutes = Math.ceil(rateLimitSeconds / 60);
            countdownTimer.textContent = minutes;
            rateLimitSeconds--;
            
            if (rateLimitSeconds <= 0) {
                // Reload halaman setelah countdown selesai
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }
        }
    }

    // Check error message dari server untuk rate limit
    const errorMessage = document.querySelector('.invalid-feedback.d-block');
    if (errorMessage && errorMessage.textContent.includes('Terlalu banyak percobaan')) {
        rateLimitBlocked = true;
        // Extract minutes from error message
        const match = errorMessage.textContent.match(/(\d+)\s*menit/);
        if (match) {
            rateLimitSeconds = parseInt(match[1]) * 60;
        }
    }

    // Initialize
    disableForm();
    if (rateLimitBlocked && rateLimitSeconds > 0) {
        // Show alert jika belum ada
        if (!rateLimitAlert && rateLimitSeconds > 0) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger mt-2';
            alertDiv.id = 'rate-limit-alert';
            alertDiv.innerHTML = '<strong>Akun Terblokir!</strong><br>Terlalu banyak percobaan. Silakan coba lagi dalam <span id="countdown-timer">' + Math.ceil(rateLimitSeconds / 60) + '</span> menit.';
            const companyCodeDiv = document.querySelector('#company_code').parentElement;
            companyCodeDiv.appendChild(alertDiv);
            countdownTimer = document.getElementById('countdown-timer');
        }
        setInterval(updateCountdown, 1000);
    }

    // Mapping emoticon untuk laki-laki (tanpa angka) dan perempuan (dengan angka 1)
    const emoticonMap = {
        'Laki-laki': {
            'netral': '{{ asset('logo/netral.png') }}',
            'senyum': '{{ asset('logo/senyum.png') }}',
            'sedih': '{{ asset('logo/sedih.png') }}',
            'lelah': '{{ asset('logo/lelah.png') }}',
            'marah': '{{ asset('logo/marah.png') }}'
        },
        'Perempuan': {
            'netral': '{{ asset('logo/netral1.png') }}',
            'senyum': '{{ asset('logo/senyum1.png') }}',
            'sedih': '{{ asset('logo/sedih1.png') }}',
            'lelah': '{{ asset('logo/lelah1.png') }}',
            'marah': '{{ asset('logo/marah1.png') }}'
        }
    };

    // Fungsi untuk mengganti emoticon berdasarkan jenis kelamin
    function updateEmoticon() {
        const selectedGender = jenisKelaminSelect.value;
        if (selectedGender === 'Laki-laki' || selectedGender === 'Perempuan') {
            // Pastikan emoticonPreview ada sebelum mengakses propertinya
            if (emoticonPreview) {
                // Pilih emoticon netral sebagai contoh
                const emoticonKey = 'netral';
                const emoticonPath = emoticonMap[selectedGender][emoticonKey];
                emoticonPreview.src = emoticonPath;
            }
        }
    }

    // Tambahkan event listener untuk perubahan jenis kelamin
    if (jenisKelaminSelect) {
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
            // Prevent submission jika rate limit blocked
            if (rateLimitBlocked || rateLimitSeconds > 0) {
                e.preventDefault();
                e.stopPropagation();
                if (rateLimitError) {
                    rateLimitError.textContent = 'Form terblokir. Silakan tunggu beberapa saat.';
                    rateLimitError.style.display = 'block';
                }
                return false;
            }

            console.log('Verification form submitted');
            let code = '';
            inputs.forEach(input => {
                code += input.value;
            });
            hiddenInput.value = code;
            console.log('Final company code:', code);
        });
    }

    // Update emoticon saat halaman dimuat jika sudah ada nilai yang dipilih dan elemen tersedia
    if (emoticonPreview) {
        updateEmoticon();
    }
});
</script>
@endsection
