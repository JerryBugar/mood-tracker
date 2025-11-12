@extends('layouts.admin')

@section('main-content')
<style>
    .admin-dashboard {
        padding: 20px;
    }

    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .dashboard-title {
        color: #82272c;
        font-size: 1.8rem;
        margin: 0;
    }

    .logout-btn {
        background-color: #dc3545;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 1rem;
        cursor: pointer;
    }

    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        padding: 20px;
        text-align: center;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: bold;
        color: #661118ff;
        margin: 10px 0;
    }

    .stat-label {
        color: #6c757d;
        font-size: 1rem;
    }

    .chart-container {
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin-bottom: 30px;
    }

    .section-title {
        color: #82272c;
        margin-top: 0;
        margin-bottom: 20px;
        font-size: 1.5rem;
    }

    .nav-tabs {
        display: flex;
        border-bottom: 2px solid #dee2e6;
        margin-bottom: 20px;
    }

    .nav-tab {
        padding: 10px 20px;
        cursor: pointer;
        border-bottom: 3px solid transparent;
        color: #495057;
        flex: 1;
        text-align: center;
        text-decoration: none;
        transition: none; /* Nonaktifkan transition */
    }

    /* Nonaktifkan semua efek hover pada nav-tab */
    .nav-tab:hover {
        border-bottom: 3px solid transparent;
        color: #495057;
        font-weight: normal;
    }

    /* Hanya tab dengan class active yang terlihat aktif */
    .nav-tab.active {
        border-bottom: 3px solid #83282f;
        color: #83282f;
        font-weight: 500;
    }

    /* Pastikan hover tidak mengubah tab aktif */
    .nav-tab.active:hover {
        border-bottom: 3px solid #83282f;
        color: #83282f;
        font-weight: 500;
    }

    .employee-list {
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .employee-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        border-bottom: 1px solid #eee;
    }

    .employee-item:last-child {
        border-bottom: none;
    }

    .employee-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .employee-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }

    .employee-name {
        font-weight: 500;
        color: #495057;
    }

    .employee-mood {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .mood-indicator {
        width: 20px;
        height: 20px;
        border-radius: 50%;
    }

    .mood-happy { background-color: #28a745; }
    .mood-sad { background-color: #dc3545; }
    .mood-tired { background-color: #ffc107; }
    .mood-angry { background-color: #6f42c1; }
    .mood-neutral { background-color: #6c757d; }

    .notification-btn {
        background-color: #007bff;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 0.9rem;
        cursor: pointer;
    }

    .notification-btn:hover {
        background-color: #0056b3;
    }
</style>

<div class="admin-dashboard">
    <div class="dashboard-header">
        <h2 class="dashboard-title">Admin Dashboard - HRD</h2>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </div>

    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-value" id="total-employees">{{ $totalEmployees ?? 0 }}</div>
            <div class="stat-label">Total Karyawan</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="active-today">{{ $activeToday ?? 0 }}</div>
            <div class="stat-label">Aktif Hari Ini</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="mood-senang">{{ $senangCount ?? 0 }}</div>
            <div class="stat-label">Senang</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="mood-sedih">{{ $sedihCount ?? 0 }}</div>
            <div class="stat-label">Sedih</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="mood-netral">{{ $netralCount ?? 0 }}</div>
            <div class="stat-label">Biasa Saja</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="mood-lelah">{{ $lelahCount ?? 0 }}</div>
            <div class="stat-label">Lelah</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="mood-marah">{{ $marahCount ?? 0 }}</div>
            <div class="stat-label">Marah</div>
        </div>
    </div>

    <div class="chart-container">
        <h3 class="section-title">Tren Mood Karyawan</h3>
        <turbo-frame id="mood_chart_frame">
            <canvas id="moodChart" width="400" height="200"></canvas>
        </turbo-frame>
    </div>

    <div class="nav-tabs" id="nav-tabs-container">
        <a class="nav-tab {{ request()->routeIs('admin.dashboard.overview') || request()->routeIs('admin.dashboard') ? 'active' : '' }}" 
           href="{{ route('admin.dashboard.overview') }}" 
           data-turbo-frame="dashboard_content"
           data-tab="overview">Ringkasan</a>
        <a class="nav-tab {{ request()->routeIs('admin.dashboard.employees') ? 'active' : '' }}" 
           href="{{ route('admin.dashboard.employees') }}" 
           data-turbo-frame="dashboard_content"
           data-tab="employees">Daftar Karyawan</a>
        <a class="nav-tab {{ request()->routeIs('admin.dashboard.notifications') ? 'active' : '' }}" 
           href="{{ route('admin.dashboard.notifications') }}" 
           data-turbo-frame="dashboard_content"
           data-tab="notifications">Notifikasi</a>
    </div>

    <turbo-frame id="dashboard_content">
        <!-- Tab content will be loaded here via Turbo -->
        @include('admin.tabs.overview', [
            'totalEmployees' => $totalEmployees,
            'activeToday' => $activeToday,
            'senangCount' => $senangCount,
            'sedihCount' => $sedihCount,
            'netralCount' => $netralCount,
            'lelahCount' => $lelahCount,
            'marahCount' => $marahCount,
            'employees' => $employees
        ])
    </turbo-frame>

    <script>
        // Gunakan window object untuk menghindari redeclaration error
        if (typeof window.moodChartInstance === 'undefined') {
            window.moodChartInstance = null;
        }

        // Jangan hancurkan moodChart saat dashboard_content frame di-render
        // Karena moodChart berada di luar frame dan tidak terpengaruh oleh perubahan tab
        // Hanya hancurkan chart yang ada di dalam frame (divisionChart di overview tab)
        
        // Simpan state tab aktif
        let currentActiveTab = 'overview'; // Default tab
        
        // Fungsi untuk update active tab - hanya satu tab yang aktif
        function updateActiveTab(activeTabName = null) {
            const tabs = document.querySelectorAll('.nav-tab');
            
            // Jika activeTabName diberikan, update state
            if (activeTabName) {
                currentActiveTab = activeTabName;
            }
            
            // Hapus SEMUA active class terlebih dahulu - pastikan benar-benar dihapus
            tabs.forEach(tab => {
                tab.classList.remove('active');
                // Pastikan class benar-benar dihapus
                if (tab.classList.contains('active')) {
                    tab.className = tab.className.replace(/\bactive\b/g, '').trim();
                }
            });
            
            // Tambahkan active class HANYA pada tab yang sesuai dengan currentActiveTab
            tabs.forEach(tab => {
                const tabName = tab.getAttribute('data-tab');
                if (tabName && tabName === currentActiveTab) {
                    tab.classList.add('active');
                }
            });
        }
        
        // Flag untuk menandai bahwa ini adalah user click (bukan hover/prefetch)
        let isUserClick = false;
        
        // Update tab saat click - menggunakan Turbo click event
        document.addEventListener('turbo:click', function(event) {
            const link = event.target.closest('a[data-turbo-frame="dashboard_content"]');
            if (link) {
                const tabName = link.getAttribute('data-tab');
                if (tabName) {
                    // Tandai bahwa ini adalah user click
                    isUserClick = true;
                    // Update tab segera saat diklik
                    updateActiveTab(tabName);
                }
            }
        });
        
        // Jangan update tab saat hover/prefetch - hanya saat click
        // Event turbo:before-fetch-request dihapus karena bisa dipanggil saat hover
        
        // Update tab saat frame render hanya jika ini dari user click
        // Jangan update berulang kali untuk menghindari semua tab menjadi aktif
        let frameRenderTimeout = null;
        document.addEventListener('turbo:frame-render', function(event) {
            if (event.target && event.target.id === 'dashboard_content' && isUserClick) {
                // Clear timeout sebelumnya jika ada
                if (frameRenderTimeout) {
                    clearTimeout(frameRenderTimeout);
                }
                // Pastikan tab aktif tetap sesuai dengan state yang disimpan
                frameRenderTimeout = setTimeout(() => {
                    updateActiveTab(); // Gunakan currentActiveTab yang sudah disimpan
                    isUserClick = false; // Reset flag
                }, 50);
            } else {
                isUserClick = false; // Reset jika bukan dari click
            }
        });
        
        // Update tab saat frame load - hanya jika dari user click
        // Jangan update berulang kali
        let frameLoadTimeout = null;
        document.addEventListener('turbo:frame-load', function(event) {
            if (event.target && event.target.id === 'dashboard_content' && isUserClick) {
                // Clear timeout sebelumnya jika ada
                if (frameLoadTimeout) {
                    clearTimeout(frameLoadTimeout);
                }
                frameLoadTimeout = setTimeout(() => {
                    updateActiveTab(); // Gunakan currentActiveTab yang sudah disimpan
                    isUserClick = false; // Reset flag
                }, 100);
            } else {
                isUserClick = false; // Reset jika bukan dari click
            }
        });
        
        // Fungsi untuk inisialisasi tab berdasarkan URL
        let isInitialized = false;
        function initializeActiveTab() {
            // Hanya inisialisasi sekali untuk menghindari semua tab menjadi aktif
            if (isInitialized) {
                return;
            }
            
            const currentPath = window.location.pathname;
            
            if (currentPath.includes('employees')) {
                currentActiveTab = 'employees';
            } else if (currentPath.includes('notifications')) {
                currentActiveTab = 'notifications';
            } else if (currentPath.includes('overview') || currentPath.endsWith('/dashboard') || currentPath.endsWith('/admin/dashboard')) {
                currentActiveTab = 'overview';
            }
            
            updateActiveTab();
            isInitialized = true;
        }
        
        // Update active tab berdasarkan URL saat page load (hanya sekali)
        document.addEventListener('turbo:load', function() {
            // Reset flag saat page load baru
            isInitialized = false;
            initializeActiveTab();
        });
        
        // Inisialisasi saat DOM ready (hanya sekali)
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeActiveTab);
        } else {
            // DOM sudah siap
            initializeActiveTab();
        }
    </script>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Fungsi untuk memuat chart mood trend
    function loadChart() {
        const moodChartCanvas = document.getElementById('moodChart');
        
        if (!moodChartCanvas) {
            return;
        }

        // Hancurkan chart yang sudah ada jika ada
        if (window.moodChartInstance) {
            try {
                window.moodChartInstance.destroy();
            } catch (e) {
                console.warn('Error destroying mood chart instance:', e);
            }
            window.moodChartInstance = null;
        }

        // Cek juga dengan Chart.getChart untuk memastikan
        const existingChart = Chart.getChart(moodChartCanvas);
        if (existingChart) {
            try {
                existingChart.destroy();
            } catch (e) {
                console.warn('Error destroying existing mood chart:', e);
            }
        }

        fetch('/admin/dashboard/chart-data')
        .then(response => response.json())
        .then(data => {
            // Pastikan canvas masih ada sebelum membuat chart baru
            const canvas = document.getElementById('moodChart');
            if (!canvas) {
                return;
            }

            // Hancurkan lagi jika ada chart yang baru dibuat
            const existingChart = Chart.getChart(canvas);
            if (existingChart) {
                existingChart.destroy();
            }

            const moodCtx = canvas.getContext('2d');
            window.moodChartInstance = new Chart(moodCtx, {
                type: 'line',
                data: {
                    labels: data.moodTrend.labels,
                    datasets: data.moodTrend.datasets
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Tren Mood Minggu Ini'
                        }
                    },
                    scales: {
                        y: {
                            stacked: true
                        }
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error loading chart data:', error);
        });
    }

    // Initialize charts when page loads
    function initializeMoodChart() {
        // Pastikan canvas ada sebelum memuat chart
        const moodChartCanvas = document.getElementById('moodChart');
        if (moodChartCanvas) {
            loadChart();
        }
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeMoodChart);
    } else {
        initializeMoodChart();
    }
    
    // Event listener for Turbo - reload chart setelah page load
    document.addEventListener('turbo:load', function() {
        // Tunggu sedikit untuk memastikan DOM sudah siap
        setTimeout(initializeMoodChart, 100);
    });
    
    // Pastikan chart tidak hilang saat frame render
    // moodChart berada di dalam mood_chart_frame yang terpisah dari dashboard_content
    // Jadi tidak perlu dihancurkan saat dashboard_content di-render
    document.addEventListener('turbo:frame-render', function(event) {
        // Jika frame yang di-render adalah dashboard_content, pastikan moodChart tetap ada
        if (event.target && event.target.id === 'dashboard_content') {
            // Cek apakah moodChart masih ada, jika tidak, reload
            setTimeout(() => {
                const moodChartCanvas = document.getElementById('moodChart');
                if (moodChartCanvas) {
                    const existingChart = Chart.getChart(moodChartCanvas);
                    // Jika chart hilang, reload
                    if (!existingChart) {
                        loadChart();
                    }
                }
            }, 200);
        }
        
        // Jika frame yang di-render adalah mood_chart_frame, reload chart
        if (event.target && event.target.id === 'mood_chart_frame') {
            setTimeout(() => {
                loadChart();
            }, 100);
        }
    });
    
    // Pastikan chart tidak hilang saat frame load
    document.addEventListener('turbo:frame-load', function(event) {
        if (event.target && event.target.id === 'mood_chart_frame') {
            setTimeout(() => {
                loadChart();
            }, 100);
        }
    });
</script>

<!-- Modal Detail Karyawan (permanen di dashboard) -->
<div class="modal fade" id="employeeDetailModal" tabindex="-1" aria-labelledby="employeeDetailModalLabel" aria-hidden="true" data-turbo="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="employeeDetailModalLabel">Detail Karyawan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nama</label>
                    <p id="detail-user-name" class="mb-1"></p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Email</label>
                    <p id="detail-user-email" class="mb-1"></p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Divisi</label>
                    <p id="detail-user-division" class="mb-1"></p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Jenis Kelamin</label>
                    <p id="detail-user-gender" class="mb-1"></p>
                </div>

                <hr>

                <div class="mb-3">
                    <label class="form-label fw-bold">Catatan Mood</label>
                    <div id="mood-records-container">
                        <!-- Mood records akan ditampilkan di sini -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Perbaiki masalah aria-hidden pada modal
    // Gunakan window object untuk menyimpan flag agar tidak ter-reset
    if (typeof window.modalAriaHandlersSetup === 'undefined') {
        window.modalAriaHandlersSetup = false;
    }
    
    function setupModalAriaHandlers() {
        // Hanya setup sekali untuk menghindari duplikasi
        if (window.modalAriaHandlersSetup) {
            return;
        }
        
        const modal = document.getElementById('employeeDetailModal');
        if (!modal) {
            return;
        }
        
        // Saat modal akan disembunyikan, hapus focus dari elemen di dalamnya
        modal.addEventListener('hide.bs.modal', function() {
            const activeElement = document.activeElement;
            if (modal.contains(activeElement)) {
                activeElement.blur();
            }
        });
        
        // Saat modal benar-benar tersembunyi, pastikan aria-hidden benar
        modal.addEventListener('hidden.bs.modal', function() {
            modal.setAttribute('aria-hidden', 'true');
            // Hapus focus dari elemen apapun yang masih focused
            if (document.activeElement && modal.contains(document.activeElement)) {
                document.activeElement.blur();
            }
        });
        
        // Saat modal ditampilkan, pastikan aria-hidden dihapus
        modal.addEventListener('show.bs.modal', function() {
            modal.removeAttribute('aria-hidden');
        });
        
        // Saat modal benar-benar ditampilkan
        modal.addEventListener('shown.bs.modal', function() {
            modal.removeAttribute('aria-hidden');
        });
        
        window.modalAriaHandlersSetup = true;
    }
    
    // Setup saat DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupModalAriaHandlers);
    } else {
        setupModalAriaHandlers();
    }
    
    // Setup ulang saat Turbo load (jika modal belum di-setup)
    document.addEventListener('turbo:load', setupModalAriaHandlers);
</script>

@endsection