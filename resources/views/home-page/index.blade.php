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
        @include('components.record-container')
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
            
            const greetingElement = document.getElementById('greeting-text');
            if (greetingElement) {
                greetingElement.textContent = greeting;
            }
        }
        
        // Langsung eksekusi fungsi saat DOM telah dimuat untuk mendapatkan salam langsung
        document.addEventListener('DOMContentLoaded', function() {
            updateGreeting();
        });
        
        // Update salam setiap 15 detik agar tetap akurat
        setInterval(updateGreeting, 15000);
        
        // Juga update saat Turbo memuat ulang halaman
        document.addEventListener('turbo:load', function() {
            updateGreeting();
        });
        
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
    </script>

@push('scripts')
<script>
// Fungsi global untuk menampilkan respons admin
if (typeof window.showAdminResponse === 'undefined') {
    window.showAdminResponse = function(recordId, response, responseDate) {
        const modalElement = document.getElementById('adminResponseModal' + recordId);
        if (modalElement) {
            // Update modal content
            const contentElement = document.getElementById('adminResponseContent' + recordId);
            const dateElement = document.getElementById('adminResponseDate' + recordId);
            
            if (contentElement) {
                contentElement.textContent = response;
            }
            if (dateElement) {
                dateElement.textContent = 'Direspons pada: ' + responseDate;
            }
            
            // Show modal
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        }
    };
}

// Setup aria-hidden handlers untuk modal respons admin
if (typeof window.adminResponseModalAriaHandlersSetup === 'undefined') {
    window.adminResponseModalAriaHandlersSetup = false;
}

function setupAdminResponseModalAriaHandlers() {
    if (window.adminResponseModalAriaHandlersSetup) {
        return;
    }

    // Setup untuk semua modal respons admin
    document.addEventListener('show.bs.modal', function(event) {
        const modal = event.target;
        if (modal.id && modal.id.startsWith('adminResponseModal')) {
            modal.removeAttribute('aria-hidden');
        }
    });

    document.addEventListener('hide.bs.modal', function(event) {
        const modal = event.target;
        if (modal.id && modal.id.startsWith('adminResponseModal')) {
            const activeElement = document.activeElement;
            if (modal.contains(activeElement) && 
                activeElement !== document.body && 
                activeElement !== document.documentElement &&
                typeof activeElement.blur === 'function') {
                activeElement.blur();
            }
        }
    });

    document.addEventListener('hidden.bs.modal', function(event) {
        const modal = event.target;
        if (modal.id && modal.id.startsWith('adminResponseModal')) {
            modal.setAttribute('aria-hidden', 'true');
            const activeElement = document.activeElement;
            if (activeElement && 
                modal.contains(activeElement) && 
                activeElement !== document.body && 
                activeElement !== document.documentElement &&
                typeof activeElement.blur === 'function') {
                activeElement.blur();
            }
        }
    });

    window.adminResponseModalAriaHandlersSetup = true;
}

// Setup saat DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupAdminResponseModalAriaHandlers);
} else {
    setupAdminResponseModalAriaHandlers();
}

// Setup ulang saat Turbo load
document.addEventListener('turbo:load', setupAdminResponseModalAriaHandlers);
</script>
@endpush

@endsection
