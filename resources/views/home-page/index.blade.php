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
        
        // Fungsi untuk mengecek apakah Bootstrap siap
        function isBootstrapReady() {
            return typeof bootstrap !== 'undefined' && bootstrap.Modal;
        }
        
        // Fungsi untuk membuka modal mood
        function openMoodModal(element) {
            const mood = element.getAttribute('data-mood');
            
            // Ambil token CSRF
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Kirim permintaan ke controller menggunakan AJAX
            fetch('{{ route("mood.modal") }}?mood=' + mood, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                // Perbarui CSRF token dari response header jika ada
                const newToken = response.headers.get('X-CSRF-TOKEN');
                if (newToken) {
                    document.querySelector('meta[name="csrf-token"]').setAttribute('content', newToken);
                }
                
                if (!response.ok) {
                    if (response.status === 401) {
                        alert('Anda harus login terlebih dahulu');
                        window.location.href = '/auth/google/redirect';
                    } else {
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                }
                return response.json();
            })
            .then(data => {
                // Cek jika ada error dari server
                if (data.error) {
                    alert('Terjadi kesalahan: ' + data.message);
                    return;
                }
                
                // Set avatar dan tanggal di modal
                document.getElementById('modalAvatar').src = data.avatar || '';
                document.getElementById('modalDate').textContent = data.date || '';
                
                // Set emoticon dan teks mood
                document.getElementById('modalEmoticon').src = data.emoticon || '';
                document.getElementById('modalMoodText').textContent = data.title || '';
                document.getElementById('modalExplanation').textContent = data.explanation || '';
                document.getElementById('modalSuggestion').textContent = data.suggestion || '';
                
                // Tampilkan modal setelah bootstrap siap
                if(isBootstrapReady()) {
                    const modalElement = document.getElementById('moodModal');
                    if(modalElement) {
                        // Hapus instance modal lama jika ada
                        const existingModal = bootstrap.Modal.getInstance(modalElement);
                        if(existingModal) {
                            existingModal.dispose();
                        }
                        
                        var myModal = new bootstrap.Modal(modalElement, {
                            backdrop: 'static',
                            keyboard: true
                        });
                        
                        // Tambahkan event listener untuk menutup modal dengan backdrop
                        modalElement.addEventListener('click', function(event) {
                            if (event.target === modalElement) {
                                closeMoodModal();
                            }
                        });
                        
                        myModal.show();
                    }
                }
            })
            .catch(error => {
                alert('Terjadi kesalahan saat membuka modal: ' + error.message);
            });
        }
        
        // Fungsi untuk menambahkan event listener ke emotikon
        function attachEmoticonEventListeners() {
            const emoticons = document.querySelectorAll('.mood-emoticon');
            
            emoticons.forEach(function(emoticon) {
                // Hapus event listener yang lama untuk mencegah duplikasi
                emoticon.removeEventListener('click', openMoodModal);
                
                const clickHandler = function() {
                    openMoodModal(this);
                };
                
                emoticon.addEventListener('click', clickHandler);
                
                // Tambahkan efek hover
                emoticon.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.1)';
                });
                
                emoticon.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                });
            });
        }

        // Karena aplikasi ini menggunakan Turbo, kita harus menggunakan event turbo:load
        // alih-alih DOMContentLoaded untuk memastikan script berjalan setelah Turbo memuat halaman
        document.addEventListener('turbo:load', function() {
            attachEmoticonEventListeners();
        });

        // Juga tetap gunakan DOMContentLoaded sebagai fallback
        document.addEventListener('DOMContentLoaded', function() {
            attachEmoticonEventListeners();
        });
        
        // Fungsi untuk menutup modal dengan benar
        function closeMoodModal() {
            if(isBootstrapReady()) {
                const modalElement = document.getElementById('moodModal');
                const modalInstance = bootstrap.Modal.getInstance(modalElement);
                
                if(modalInstance) {
                    modalInstance.hide();
                }
                
                // Hapus backdrop Bootstrap secara manual jika masih ada
                const backdrop = document.querySelector('.modal-backdrop');
                if(backdrop) {
                    backdrop.remove();
                }
                
                // Hapus class 'modal-open' dari body jika masih ada
                document.body.classList.remove('modal-open');
                
                // Reset form input
                document.getElementById('reasonInput').value = '';
                document.getElementById('suggestionInput').value = '';
            }
        }
        
        // Tambahkan event listener ke tombol close (X) dan tombol Batal
        document.addEventListener('click', function(event) {
            if(event.target.closest && (event.target.closest('.btn-close') || event.target.closest('.btn-secondary'))) {
                closeMoodModal();
            }
        });
        
        // Event listener untuk tombol submit mood
        document.getElementById('submitMood').addEventListener('click', function() {
            const moodValue = document.querySelector('.mood-emoticon.active') ? 
                             document.querySelector('.mood-emoticon.active').getAttribute('data-mood') : 
                             'netral'; // default value
            
            const reason = document.getElementById('reasonInput').value;
            const suggestion = document.getElementById('suggestionInput').value;
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch('{{ route("mood.save") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    mood: moodValue,
                    reason: reason,
                    suggestion: suggestion
                })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert(data.message);
                    closeMoodModal();
                } else {
                    alert('Gagal menyimpan mood');
                }
            })
            .catch(error => {
                alert('Terjadi kesalahan saat menyimpan mood');
                closeMoodModal();
            });
        });
    </script>

@endsection
