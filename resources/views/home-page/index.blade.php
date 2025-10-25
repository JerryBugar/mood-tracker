@extends('layouts.internal')

@section('main-content')
    <div class="container-fluid">
        @if (Auth::check())
            <h1 style="color: #82272c; margin-top: 20px;">Haloo, {{ explode(' ', Auth::user()->name)[0] }}</h1>
        @else
            <h1 style="color: #82272c; margin-top: 20px;">Welcome to Home Page!</h1>
        @endif

        @include('components.mood-input-container')
        @include('components.mood-emoticons')
    </div>

    @include('components.mood-modal')

    <script>
        // Fungsi untuk menentukan salam berdasarkan waktu
        function updateGreeting() {
            const now = new Date();
            const hours = now.getHours();
            
            let greeting = '';
            
            if (hours >= 4 && hours < 10) {
                greeting = 'Selamat Pagi';
            } else if (hours >= 10 && hours < 14) {
                greeting = 'Selamat Siang';
            } else if (hours >= 14 && hours < 18) {
                greeting = 'Selamat Sore';
            } else {
                greeting = 'Selamat Malam';
            }
            
            document.getElementById('greeting-text').textContent = greeting;
        }
        
        // Langsung eksekusi fungsi saat script dimuat untuk mendapatkan salam langsung
        updateGreeting();
        
        // Update salam setiap 15 detik agar tetap akurat
        setInterval(updateGreeting, 15000);
        
        // Fungsi untuk memeriksa apakah cache masih valid (kurang dari 1 jam)
        function isQuoteCacheValid() {
            const cacheTimestamp = localStorage.getItem('moodQuoteTimestamp');
            if (!cacheTimestamp) return false;
            
            const now = new Date().getTime();
            const cacheTime = parseInt(cacheTimestamp);
            const oneHour = 60 * 60 * 1000; // 1 jam dalam milidetik
            
            return (now - cacheTime) < oneHour;
        }
        
        // Fungsi untuk mengambil kutipan motivasi acak
        function loadRandomQuote() {
            // Cek apakah elemen quote ada di halaman ini
            const moodQuoteElement = document.getElementById('moodQuote');
            const moodAuthorElement = document.getElementById('moodAuthor');
            
            // Jika elemen tidak ditemukan, keluar dari fungsi
            if (!moodQuoteElement || !moodAuthorElement) {
                return;
            }
            
            // Cek apakah cache valid
            const isCacheValid = isQuoteCacheValid();
            const cachedQuote = localStorage.getItem('moodQuote');
            const cachedAuthor = localStorage.getItem('moodAuthor');
            
            // Jika cache valid dan ada datanya, tampilkan dulu
            if (isCacheValid && cachedQuote && cachedAuthor) {
                moodQuoteElement.textContent = cachedQuote;
                moodAuthorElement.textContent = cachedAuthor;
            }
            
            // Ambil data dari server untuk update cache (kecuali jika cache masih valid)
            if (!isCacheValid) {
                fetch('{{ route("mood.quote") }}', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Received quote data:', data);
                    
                    // Simpan data ke localStorage dengan timestamp
                    localStorage.setItem('moodQuote', data.quote);
                    localStorage.setItem('moodAuthor', '- ' + data.author);
                    localStorage.setItem('moodQuoteTimestamp', new Date().getTime());
                    
                    // Tampilkan data yang baru (hanya jika elemen masih ada)
                    const currentMoodQuoteElement = document.getElementById('moodQuote');
                    const currentMoodAuthorElement = document.getElementById('moodAuthor');
                    
                    if (currentMoodQuoteElement && currentMoodAuthorElement) {
                        currentMoodQuoteElement.textContent = data.quote;
                        currentMoodAuthorElement.textContent = '- ' + data.author;
                    }
                })
                .catch(error => {
                    console.error('Error fetching quote:', error);
                    // Jika gagal ambil dari server, tetap gunakan data dari localStorage
                    // atau gunakan fallback jika tidak ada di localStorage
                    if (!(cachedQuote && cachedAuthor)) {
                        // Hanya tampilkan fallback jika elemen masih ada
                        const currentMoodQuoteElement = document.getElementById('moodQuote');
                        const currentMoodAuthorElement = document.getElementById('moodAuthor');
                        
                        if (currentMoodQuoteElement && currentMoodAuthorElement) {
                            currentMoodQuoteElement.textContent = 'Dibalik setiap kesulitan, tersimpan sebuah kesempatan.';
                            currentMoodAuthorElement.textContent = '- Albert Einstein';
                        }
                    }
                });
            } else {
                // Jika cache valid, kita tidak perlu ambil dari server, tapi tetap bisa mengupdate nanti
                // Cek apakah data sudah tampil, jika belum tampil maka tampilkan
                if (cachedQuote && cachedAuthor) {
                    moodQuoteElement.textContent = cachedQuote;
                    moodAuthorElement.textContent = cachedAuthor;
                }
            }
        }
        
        // Muat kutipan ketika halaman pertama kali dimuat
        document.addEventListener('DOMContentLoaded', function() {
            loadRandomQuote();
        });
        
        // Muat ulang kutipan saat Turbo memuat ulang halaman
        // Ini akan mengambil kutipan dari sesi (server-side), bukan mengacak ulang
        document.addEventListener('turbo:load', function() {
            loadRandomQuote();
        });
        
        // Bersihkan cache yang sudah kadaluarsa saat halaman dimuat
        window.addEventListener('load', function() {
            if (!isQuoteCacheValid()) {
                localStorage.removeItem('moodQuote');
                localStorage.removeItem('moodAuthor');
                localStorage.removeItem('moodQuoteTimestamp');
            }
        });
        
        // Hapus atau komentari fungsi openMoodModal yang lama
        /*
        function openMoodModal(element) {
            // ... kode fetch lama ...
        }
        */

        // Fungsi untuk memeriksa apakah Bootstrap siap (tetap sama)
        function isBootstrapReady() {
            return typeof bootstrap !== 'undefined' && bootstrap.Modal;
        }

        // Fungsi untuk menutup modal (sedikit modifikasi untuk membersihkan frame)
        function closeMoodModal() {
            if (isBootstrapReady()) {
                const modalElement = document.getElementById('moodModal');
                const modalInstance = bootstrap.Modal.getInstance(modalElement);

                if (modalInstance) {
                    modalInstance.hide();
                }

                // Hapus backdrop dan class 'modal-open' (opsional, hide() biasanya cukup)
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
                document.body.classList.remove('modal-open');
                document.body.style.overflow = ''; // Kembalikan scroll body
                document.body.style.paddingRight = ''; // Kembalikan padding body


                // Reset form input di dalam frame setelah modal tertutup
                const reasonInput = document.getElementById('reasonInput');
                const suggestionInput = document.getElementById('suggestionInput');
                if (reasonInput) reasonInput.value = '';
                if (suggestionInput) suggestionInput.value = '';

                // Opsional: Reset konten frame ke placeholder loading setelah modal ditutup
                const frameContent = document.getElementById('mood_modal_content');
                if (frameContent) {
                     // Beri sedikit delay agar tidak terlihat aneh saat menutup
                    setTimeout(() => {
                        frameContent.innerHTML = `<div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>`;
                    }, 300); // Sesuaikan delay
                }
            }
        }

        // Listener untuk menampilkan modal SETELAH Turbo Frame selesai dirender
        document.addEventListener('turbo:frame-render', function(event) {
            const frameId = event.target.id;

            if (frameId === 'mood_modal_content') {
                if (isBootstrapReady()) {
                    const modalElement = document.getElementById('moodModal');
                    if (modalElement) {
                        // Ambil data avatar dan date (jika perlu dari server,
                        // idealnya ini bisa bagian dari frame response atau atribut data)
                        // Untuk sekarang, kita set manual saja dari user yg login (jika ada)
                        const avatarImg = document.getElementById('modalAvatar');
                        const dateSpan = document.getElementById('modalDate');

                        // Anda perlu cara mendapatkan avatar & date di JS.
                        // Misal, simpan di data attribute body atau elemen lain saat page load.
                        // Contoh Sederhana (perlu disesuaikan):
                         const userAvatarUrl = "{{ Auth::check() ? Auth::user()->avatar : '' }}"; // Ini hanya bekerja jika JS inline di Blade
                         if (avatarImg && userAvatarUrl) {
                             avatarImg.src = userAvatarUrl;
                             avatarImg.style.display = 'inline-block';
                         } else if(avatarImg) {
                             avatarImg.style.display = 'none';
                         }

                         if (dateSpan) {
                             const today = new Date();
                             // Format tanggal manual (atau gunakan library jika perlu format kompleks)
                             const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                             dateSpan.textContent = today.toLocaleDateString('id-ID', options);
                         }


                        // Hapus instance lama jika ada (penting!)
                        const existingModal = bootstrap.Modal.getInstance(modalElement);
                        if (existingModal) {
                            existingModal.dispose();
                        }

                        // Buat instance baru dan tampilkan
                        const myModal = new bootstrap.Modal(modalElement, {
                             backdrop: 'static', // Mencegah tutup saat klik backdrop
                             keyboard: false // Mencegah tutup dengan tombol Esc
                        });
                        myModal.show();
                    }
                }
            }
        });


        // Hapus atau komentari attachEmoticonEventListeners() karena link Turbo bekerja otomatis
        /*
        function attachEmoticonEventListeners() {
            // ... kode lama ...
        }
        document.addEventListener('turbo:load', attachEmoticonEventListeners);
        document.addEventListener('DOMContentLoaded', attachEmoticonEventListeners);
        */
        
        // Tambahkan event listener ke tombol close (X) dan tombol Batal (tetap sama)
        document.addEventListener('click', function(event) {
            const target = event.target;
            // Cek apakah tombol close modal atau tombol Batal di dalam modal
            if (target.matches('[data-bs-dismiss="modal"]') || target.closest('[data-bs-dismiss="modal"]')) {
                 // Cek apakah ini tombol di dalam #moodModal
                if (target.closest('#moodModal')) {
                    closeMoodModal();
                }
            }
        });

        // Event listener untuk form submit mood (menggunakan AJAX manual karena data-turbo="false")
        document.addEventListener('turbo:load', () => {
            const moodForm = document.getElementById('mood-save-form');
            if (moodForm) {
                // Hapus listener lama jika ada
                const currentHandler = moodForm.handler;
                if(currentHandler) {
                    moodForm.removeEventListener('submit', currentHandler);
                }

                // Tambah listener baru
                const newHandler = function(event) {
                    event.preventDefault(); // Mencegah submit form biasa

                    const formData = new FormData(moodForm);
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    fetch(moodForm.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json', // Beri tahu server kita mengharapkan JSON
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                            // Jangan set Content-Type, biarkan browser menentukannya untuk FormData
                        },
                        body: formData // Kirim sebagai FormData
                    })
                    .then(response => {
                         if (!response.ok) {
                             throw new Error(`HTTP error! status: ${response.status}`);
                         }
                         // Cek content type sebelum parse JSON
                         const contentType = response.headers.get("content-type");
                         if (contentType && contentType.indexOf("application/json") !== -1) {
                             return response.json();
                         } else {
                             return response.text().then(text => {throw new Error("Expected JSON, got: "+text)});
                         }
                    })
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            closeMoodModal();
                        } else {
                            alert('Gagal menyimpan mood: ' + (data.message || 'Error tidak diketahui'));
                        }
                    })
                    .catch(error => {
                        console.error('Error saving mood:', error);
                        alert('Terjadi kesalahan saat menyimpan mood: ' + error.message);
                        // Pertimbangkan untuk tidak menutup modal jika ada error
                        // closeMoodModal();
                    });
                };

                moodForm.addEventListener('submit', newHandler);
                moodForm.handler = newHandler; // Simpan referensi handler
            }
        });
    </script>

@endsection
