@extends('layouts.internal')

@section('main-content')
    <div class="container-fluid">
        @if (Auth::check())
            <h1 style="color: #82272c; margin-top: 20px;">Haloo, {{ explode(' ', Auth::user()->name)[0] }}</h1>
        @else
            <h1 style="color: #82272c; margin-top: 20px;">Welcome to Home Page!</h1>
        @endif

        <style>
            .emoticon-background {
                display: inline-block;
                background-color: rgba(255, 255, 255, 0.2);
                border-radius: 50%;
                padding: 8px;
                margin: 0;
            }

            .mood-emoticons-container {
                padding: 20px;
            }

            .mood-emoticons-grid > div > div {
                margin: 0 5px;
            }

            @media (min-width: 768px) {
                .mood-emoticons-grid > div > div {
                    margin: 0 90px !important;
                }
            }

            @media (max-width: 767px) {
                .mood-emoticons-grid > div > div {
                    margin: 0 5px !important;
                }
                
                .emoticon-background {
                    padding: 5px;
                }
                
                .mood-emoticons-container {
                    padding: 15px 10px;
                }
            }
        </style>
        
        <div class="mood-input-container" style="margin-bottom: 8px;">
            <div class="mood-input-box text-center" style="flex-direction: column;">
                @if (Auth::check() && Auth::user()->avatar)
                    <img src="{{ Auth::user()->avatar }}" alt="User Avatar" class="mood-avatar mb-2" style="border: 3px solid #ffffff; border-radius: 50%;">
                @endif
                <div class="mood-text-content">
                    <h3 id="greeting-text"></h3>
                    <p class="mood-quote">Dibalik setiap kesulitan, tersimpan sebuah kesempatan.</p>
                    <small class="mood-author">- Albert Einstein</small>
                </div>
            </div>
        </div>

        <div class="mood-emoticons-container" style="background-color: #d98695; border-radius: 15px; margin-top: 0px; text-align: center;">
            <h3 class="d-none d-sm-block mb-0" style="color: white;">Bagaimana kabarmu hari ini?</h3>
            <h5 class="d-block d-sm-none mb-0" style="color: white;">Bagaimana kabarmu hari ini?</h6>
            
            <div class="mood-emoticons-grid mt-3">
                <div class="d-flex justify-content-center align-items-center" style="flex-wrap: nowrap; margin: 0 -15px;">
                    <div class="d-md-none text-center">
                        <div class="emoticon-background">
                            <img src="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/netral1.png') : asset('logo/netral.png') }}" alt="Netral" class="mood-emoticon emoticon-clickable" data-mood="netral" style="width: 45px; height: 45px; transition: transform 0.2s ease; display: block; cursor: pointer;">
                        </div>
                    </div>
                    <div class="d-none d-md-block text-center">
                        <div class="emoticon-background">
                            <img src="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/netral1.png') : asset('logo/netral.png') }}" alt="Netral" class="mood-emoticon emoticon-clickable" data-mood="netral" style="width: 70px; height: 70px; transition: transform 0.2s ease; cursor: pointer;">
                        </div>
                    </div>
                    
                    <div class="d-md-none text-center">
                        <div class="emoticon-background">
                            <img src="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/senyum1.png') : asset('logo/senyum.png') }}" alt="Senyum" class="mood-emoticon emoticon-clickable" data-mood="senyum" style="width: 45px; height: 45px; transition: transform 0.2s ease; display: block; cursor: pointer;">
                        </div>
                    </div>
                    <div class="d-none d-md-block text-center">
                        <div class="emoticon-background">
                            <img src="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/senyum1.png') : asset('logo/senyum.png') }}" alt="Senyum" class="mood-emoticon emoticon-clickable" data-mood="senyum" style="width: 70px; height: 70px; transition: transform 0.2s ease; cursor: pointer;">
                        </div>
                    </div>
                    
                    <div class="d-md-none text-center">
                        <div class="emoticon-background">
                            <img src="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/sedih1.png') : asset('logo/sedih.png') }}" alt="Sedih" class="mood-emoticon emoticon-clickable" data-mood="sedih" style="width: 45px; height: 45px; transition: transform 0.2s ease; display: block; cursor: pointer;">
                        </div>
                    </div>
                    <div class="d-none d-md-block text-center">
                        <div class="emoticon-background">
                            <img src="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/sedih1.png') : asset('logo/sedih.png') }}" alt="Sedih" class="mood-emoticon emoticon-clickable" data-mood="sedih" style="width: 70px; height: 70px; transition: transform 0.2s ease; cursor: pointer;">
                        </div>
                    </div>
                    
                    <div class="d-md-none text-center">
                        <div class="emoticon-background">
                            <img src="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/lelah1.png') : asset('logo/lelah.png') }}" alt="lelah" class="mood-emoticon emoticon-clickable" data-mood="lelah" style="width: 45px; height: 45px; transition: transform 0.2s ease; display: block; cursor: pointer;">
                        </div>
                    </div>
                    <div class="d-none d-md-block text-center">
                        <div class="emoticon-background">
                            <img src="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/lelah1.png') : asset('logo/lelah.png') }}" alt="lelah" class="mood-emoticon emoticon-clickable" data-mood="lelah" style="width: 70px; height: 70px; transition: transform 0.2s ease; cursor: pointer;">
                        </div>
                    </div>
                    
                    <div class="d-md-none text-center">
                        <div class="emoticon-background">
                            <img src="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/marah1.png') : asset('logo/marah.png') }}" alt="Marah" class="mood-emoticon emoticon-clickable" data-mood="marah" style="width: 45px; height: 45px; transition: transform 0.2s ease; display: block; cursor: pointer;">
                        </div>
                    </div>
                    <div class="d-none d-md-block text-center">
                        <div class="emoticon-background">
                            <img src="{{ Auth::check() && Auth::user()->jenis_kelamin === 'Cewek' ? asset('logo/marah1.png') : asset('logo/marah.png') }}" alt="Marah" class="mood-emoticon emoticon-clickable" data-mood="marah" style="width: 70px; height: 70px; transition: transform 0.2s ease; cursor: pointer;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal untuk input mood -->
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
        
        // Fungsi untuk mengecek apakah Bootstrap siap
        function isBootstrapReady() {
            return typeof bootstrap !== 'undefined' && bootstrap.Modal;
        }
        
        // Fungsi untuk membuka modal mood
        function openMoodModal(element) {
            const mood = element.getAttribute('data-mood');
            console.log('Mood yang dikirim:', mood);
            
            // Ambil token CSRF
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            console.log('CSRF Token:', csrfToken);
            
            // Kirim permintaan ke controller menggunakan AJAX
            fetch('{{ route("mood.modal") }}?mood=' + mood, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                console.log('Status response:', response.status);
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Data yang diterima:', data);
                
                // Set avatar dan tanggal di modal
                document.getElementById('modalAvatar').src = data.avatar || '';
                document.getElementById('modalDate').textContent = data.date || '';
                
                // Set emoticon dan teks mood
                document.getElementById('modalEmoticon').src = data.emoticon || '';
                document.getElementById('modalMoodText').textContent = data.title || '';
                document.getElementById('modalExplanation').textContent = data.explanation || '';
                document.getElementById('modalSuggestion').textContent = data.suggestion || '';
                
                // Tampilkan modal setelah bootstrap siap
                console.log('Memeriksa kesiapan Bootstrap...');
                if(isBootstrapReady()) {
                    console.log('Bootstrap siap, mencoba menampilkan modal');
                    const modalElement = document.getElementById('moodModal');
                    console.log('Elemen modal ditemukan:', modalElement);
                    if(modalElement) {
                        var myModal = new bootstrap.Modal(modalElement);
                        console.log('Objek modal dibuat:', myModal);
                        myModal.show();
                        console.log('Fungsi show() dipanggil');
                    } else {
                        console.error('Elemen modal tidak ditemukan!');
                    }
                } else {
                    console.error('Bootstrap tidak ditemukan!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat membuka modal: ' + error.message);
            });
        }
        
        // Tambahkan event listener untuk setiap emotikon saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOMContentLoaded dipanggil');
            const emoticons = document.querySelectorAll('.mood-emoticon');
            console.log('Jumlah emotikon ditemukan:', emoticons.length);
            
            emoticons.forEach(function(emoticon) {
                console.log('Menambahkan event listener ke emotikon:', emoticon.getAttribute('data-mood'));
                
                emoticon.addEventListener('click', function() {
                    console.log('Klik emotikon terdeteksi');
                    openMoodModal(this);
                });
                
                // Tambahkan efek hover
                emoticon.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.1)';
                });
                
                emoticon.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                });
            });
        });
        
        // Fungsi untuk menyimpan mood
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
                    if(isBootstrapReady()) {
                        bootstrap.Modal.getInstance(document.getElementById('moodModal')).hide();
                    }
                } else {
                    alert('Gagal menyimpan mood');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menyimpan mood');
            });
        });
    </script>

@endsection
