<nav id="main-bottom-nav" class="bottom-nav" style="background-color: #82272c;" data-turbo-permanent>
    <div class="nav-active-background"></div>
    <a href="{{ url('/home') }}" class="nav-item {{ Request::is('home') ? 'active' : '' }}" data-turbo-action="advance">
        <i class="bi bi-house-door-fill"></i>
        <span class="nav-text">Home</span>
    </a>
    <a href="{{ url('/calendar') }}" class="nav-item {{ Request::is('calendar') ? 'active' : '' }}" data-turbo-action="advance">
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M200-80q-33 0-56.5-23.5T120-160v-560q0-33 23.5-56.5T200-800h40v-80h80v80h320v-80h80v80h40q33 0 56.5 23.5T840-720v560q0 33-23.5 56.5T760-80H200Zm0-80h560v-400H200v400Zm0-480h560v-80H200v80Zm0 0v-80 80Zm280 240q-17 0-28.5-11.5T440-440q0-17 11.5-28.5T480-480q17 0 28.5 11.5T520-440q0 17-11.5 28.5T480-400Zm-160 0q-17 0-28.5-11.5T280-440q0-17 11.5-28.5T320-480q17 0 28.5 11.5T360-440q0 17-11.5 28.5T320-400Zm320 0q-17 0-28.5-11.5T600-440q0-17 11.5-28.5T640-480q17 0 28.5 11.5T680-440q0 17-11.5 28.5T640-400ZM480-240q-17 0-28.5-11.5T440-280q0-17 11.5-28.5T480-320q17 0 28.5 11.5T520-280q0 17-11.5 28.5T480-240Zm-160 0q-17 0-28.5-11.5T280-280q0-17 11.5-28.5T320-320q17 0 28.5 11.5T360-280q0 17-11.5 28.5T320-240Zm320 0q-17 0-28.5-11.5T600-280q0-17 11.5-28.5T640-320q17 0 28.5 11.5T680-280q0 17-11.5 28.5T640-240Z"/></svg>
        <span class="nav-text">Calendar</span>
    </a>
    <a href="{{ url('/notif') }}" class="nav-item {{ Request::is('notif') ? 'active' : '' }}" data-turbo-action="advance">
        <i class="bi bi-bell-fill"></i>
        <span class="nav-text">Notif</span>
    </a>
    <a href="{{ url('/profile') }}" class="nav-item {{ Request::is('profile') ? 'active' : '' }}" data-turbo-action="advance">
        <i class="bi bi-person-fill"></i>
        <span class="nav-text">Profile</span>
    </a>
</nav>

<script>
// Fungsi untuk memperbarui posisi background aktif berdasarkan item aktif saat ini
function updateActiveBackground() {
    // Hanya jalankan pada tampilan mobile
    if (window.innerWidth <= 767) {
        const activeItem = document.querySelector('.bottom-nav .nav-item.active');
        const background = document.querySelector('.nav-active-background');
        
        if (activeItem && background) {
            // Dapatkan posisi relatif dari item aktif terhadap navbar
            const navRect = document.querySelector('.bottom-nav').getBoundingClientRect();
            const itemRect = activeItem.getBoundingClientRect();
            
            // Hitung posisi dan lebar berdasarkan posisi sebenarnya dari item aktif
            const leftPosition = itemRect.left - navRect.left;
            const itemWidth = itemRect.width;
            
            // Tambahkan sedikit padding untuk memberikan ruang antara teks dan sisi background
            const padding = 8; // dalam pixel
            
            // Update posisi background
            background.style.left = `${leftPosition + padding}px`;
            background.style.width = `${itemWidth - (padding * 2)}px`;
        }
    }
}

// Fungsi untuk memperbarui kelas aktif berdasarkan URL saat ini
function updateActiveNavItem() {
    const currentPath = window.location.pathname;
    
    // Hapus semua kelas 'active'
    document.querySelectorAll('.bottom-nav .nav-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // Tentukan elemen aktif berdasarkan path
    let activeElement = null;
    
    if (currentPath.includes('/calendar')) {
        activeElement = document.querySelector('.bottom-nav .nav-item[href$="/calendar"]');
    } else if (currentPath.includes('/notif')) {
        activeElement = document.querySelector('.bottom-nav .nav-item[href$="/notif"]');
    } else if (currentPath.includes('/profile')) {
        activeElement = document.querySelector('.bottom-nav .nav-item[href$="/profile"]');
    } else {
        // Default ke home
        activeElement = document.querySelector('.bottom-nav .nav-item[href$="/home"]');
    }
    
    // Tambahkan kelas 'active' ke elemen yang sesuai
    if (activeElement) {
        activeElement.classList.add('active');
    }
    
    // Update background setelah perubahan kelas aktif
    updateActiveBackground();
}

// Event listener untuk Turbo load - ini penting untuk kompatibilitas Turbo
document.addEventListener('turbo:load', function() {
    updateActiveNavItem();
});

// Event listener untuk resize window - untuk menangani perubahan orientasi
window.addEventListener('resize', function() {
    // Update background saat ukuran jendela berubah
    updateActiveBackground();
});

// Inisialisasi saat halaman pertama kali dimuat
document.addEventListener('DOMContentLoaded', function() {
    // Tunggu sebentar agar elemen-elemen selesai dimuat
    setTimeout(function() {
        updateActiveBackground();
    }, 100);  // Delay kecil untuk memastikan DOM benar-benar siap
});
</script>
