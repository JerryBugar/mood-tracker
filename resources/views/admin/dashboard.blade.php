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
    }
    
    .nav-tab.active {
        border-bottom: 3px solid #83282f;
        color: #83282f;
        font-weight: 500;
    }
    
    .tab-content {
        display: none;
    }
    
    .tab-content.active {
        display: block;
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
        <canvas id="moodChart" width="400" height="200"></canvas>
    </div>
    
    <div class="nav-tabs">
        <div class="nav-tab active" data-tab="overview">Ringkasan</div>
        <div class="nav-tab" data-tab="employees">Daftar Karyawan</div>
        <div class="nav-tab" data-tab="notifications">Notifikasi</div>
    </div>
    
    <div id="overview" class="tab-content active">
        <div class="chart-container">
            <h3 class="section-title">Statistik Mood per Divisi</h3>
            <canvas id="divisionChart" width="400" height="200"></canvas>
        </div>
    </div>
    
    <div id="employees" class="tab-content">
        <div class="form-group mb-3">
            <div class="input-group">
                <input type="text" id="employee-search" class="form-control" placeholder="Cari karyawan berdasarkan nama atau divisi...">
                <button class="btn btn-outline-secondary" type="button" id="search-button">Cari</button>
            </div>
        </div>
        <div class="employee-list">
            @forelse($employees as $employee)
            <div class="employee-item">
                <div class="employee-info">
                    @if($employee->avatar)
                        <img src="{{ $employee->avatar }}" alt="Avatar" class="employee-avatar">
                    @else
                        <div class="employee-avatar bg-light d-flex align-items-center justify-content-center">
                            {{ strtoupper(substr($employee->name, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <div class="employee-name">{{ $employee->name }}</div>
                        <div class="text-muted">{{ $employee->division ?: 'Tidak ada divisi' }}</div>
                    </div>
                </div>
                <button class="notification-btn" onclick="viewEmployeeDetail({{ $employee->id }})">Lihat Detail</button>
            </div>
            @empty
            <div class="employee-item">
                <div class="employee-info">
                    <div>
                        <div class="employee-name">Tidak ada data karyawan</div>
                    </div>
                </div>
            </div>
            @endforelse
        </div>
    </div>
    
    <div id="notifications" class="tab-content">
        <div class="chart-container">
            <h3 class="section-title">Kirim Notifikasi</h3>
            <form id="notification-form">
                <div class="form-group mb-3">
                    <label for="notification-type" class="form-label">Jenis Notifikasi</label>
                    <select id="notification-type" class="form-control">
                        <option value="individual">Individu</option>
                        <option value="group">Grup/Divisi</option>
                        <option value="all">Semua Karyawan</option>
                    </select>
                </div>
                
                <div class="form-group mb-3">
                    <label for="message" class="form-label">Pesan</label>
                    <textarea id="message" class="form-control" rows="4" placeholder="Tulis pesan notifikasi..."></textarea>
                </div>
                
                <div class="form-group mb-3">
                    <label for="schedule-time" class="form-label">Jadwal Kirim (opsional)</label>
                    <input type="datetime-local" id="schedule-time" class="form-control">
                </div>
                
                <button type="submit" class="btn-login">Kirim Notifikasi</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Tab navigation
    document.querySelectorAll('.nav-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            // Remove active class from all tabs and content
            document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            // Add active class to clicked tab
            tab.classList.add('active');
            
            // Show corresponding content
            const tabId = tab.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
        });
    });
    
    // Initialize charts
    fetch('/admin/dashboard/chart-data')
    .then(response => response.json())
    .then(data => {
        // Mood trend chart
        const moodCtx = document.getElementById('moodChart').getContext('2d');
        const moodChart = new Chart(moodCtx, {
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
        
        // Division mood chart
        const divisionCtx = document.getElementById('divisionChart').getContext('2d');
        const divisionChart = new Chart(divisionCtx, {
            type: 'bar',
            data: data.divisionMood,
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Rata-rata Mood per Divisi'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    })
    .catch(error => {
        console.error('Error loading chart data:', error);
        
        // Fallback to original charts if API fails
        const moodCtx = document.getElementById('moodChart').getContext('2d');
        const moodChart = new Chart(moodCtx, {
            type: 'line',
            data: {
                labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                datasets: [{
                    label: 'Senang',
                    data: [12, 19, 3, 5, 2, 3, 15],
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.3)',
                    tension: 0.4,
                    pointRadius: 6,
                    pointBackgroundColor: 'rgb(20, 80, 35)',
                    pointBorderColor: 'rgb(20, 80, 35)',
                    fill: true
                }, {
                    label: 'Sedih',
                    data: [3, 5, 2, 8, 1, 4, 6],
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.3)',
                    tension: 0.4,
                    pointRadius: 6,
                    pointBackgroundColor: 'rgb(150, 35, 45)',
                    pointBorderColor: 'rgb(150, 35, 45)',
                    fill: true
                }, {
                    label: 'Biasa Saja',
                    data: [5, 4, 7, 2, 9, 3, 4],
                    borderColor: '#6c757d',
                    backgroundColor: 'rgba(108, 117, 125, 0.3)',
                    tension: 0.4,
                    pointRadius: 6,
                    pointBackgroundColor: 'rgb(70, 75, 80)',
                    pointBorderColor: 'rgb(70, 75, 80)',
                    fill: true
                }, {
                    label: 'Lelah',
                    data: [2, 3, 5, 4, 1, 2, 3],
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.3)',
                    tension: 0.4,
                    pointRadius: 6,
                    pointBackgroundColor: 'rgb(200, 150, 0)',
                    pointBorderColor: 'rgb(200, 150, 0)',
                    fill: true
                }, {
                    label: 'Marah',
                    data: [1, 2, 1, 3, 2, 1, 2],
                    borderColor: '#6f42c1',
                    backgroundColor: 'rgba(111, 66, 193, 0.3)',
                    tension: 0.4,
                    pointRadius: 6,
                    pointBackgroundColor: 'rgb(75, 45, 130)',
                    pointBorderColor: 'rgb(75, 45, 130)',
                    fill: true
                }]
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
        
        const divisionCtx = document.getElementById('divisionChart').getContext('2d');
        const divisionChart = new Chart(divisionCtx, {
            type: 'bar',
            data: {
                labels: ['IT', 'HR', 'Finance', 'Marketing', 'Operations'],
                datasets: [
                {
                    label: 'Senang',
                    data: [4, 3, 3, 2, 4],
                    backgroundColor: 'rgba(40, 167, 69, 0.7)'  // Hijau
                }, 
                {
                    label: 'Sedih',
                    data: [1, 2, 1, 2, 1],
                    backgroundColor: 'rgba(220, 53, 69, 0.7)'  // Merah
                }, 
                {
                    label: 'Biasa Saja',
                    data: [2, 2, 3, 2, 2],
                    backgroundColor: 'rgba(108, 117, 125, 0.7)'  // Abu-abu
                },
                {
                    label: 'Lelah',
                    data: [1, 1, 1, 2, 1],
                    backgroundColor: 'rgba(255, 193, 7, 0.7)'  // Kuning
                },
                {
                    label: 'Marah',
                    data: [0, 0, 1, 0, 0],
                    backgroundColor: 'rgba(111, 66, 193, 0.7)'  // Ungu
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Rata-rata Mood per Divisi'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
    
    // Form submission for notifications
    document.getElementById('notification-form').addEventListener('submit', function(e) {
        e.preventDefault();
        alert('Notifikasi berhasil dikirim!');
        this.reset();
    });
    
    // Employee search functionality
    document.getElementById('search-button').addEventListener('click', function() {
        performSearch();
    });
    
    document.getElementById('employee-search').addEventListener('keyup', function(event) {
        if (event.key === 'Enter') {
            performSearch();
        }
    });
    
    function performSearch() {
        const searchTerm = document.getElementById('employee-search').value.toLowerCase();
        const employeeItems = document.querySelectorAll('#employees .employee-item');
        
        employeeItems.forEach(function(item) {
            const employeeName = item.querySelector('.employee-name').textContent.toLowerCase();
            const employeeDivision = item.querySelector('.text-muted').textContent.toLowerCase();
            
            if (employeeName.includes(searchTerm) || employeeDivision.includes(searchTerm)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    }
    
    function viewEmployeeDetail(userId) {
        // Fetch user details and recent mood record
        fetch(`/admin/user/${userId}/detail`)
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                // Fill modal with user details
                document.getElementById('detail-user-name').textContent = data.user.name;
                document.getElementById('detail-user-email').textContent = data.user.email;
                document.getElementById('detail-user-division').textContent = data.user.division || 'Tidak ada divisi';
                document.getElementById('detail-user-gender').textContent = data.user.jenis_kelamin || 'Tidak diset';
                
                // Fill mood details if available
                if(data.moodRecord) {
                    document.getElementById('detail-mood-reason').textContent = data.moodRecord.reason || 'Tidak ada alasan';
                    document.getElementById('detail-mood-action').textContent = data.moodRecord.action_suggestion || 'Tidak ada saran tindakan';
                    
                    // Set mood emoticon and label
                    const moodEmoticon = document.getElementById('detail-mood-emoticon');
                    const moodLabel = document.getElementById('detail-mood-label');
                    
                    // Determine gender for emoticon selection
                    const isFemale = data.user.jenis_kelamin === 'Perempuan' || data.user.jenis_kelamin === 'Cewek';
                    
                    // Define emoticon paths based on mood and gender
                    const emoticonPaths = {
                        'netral': isFemale ? '{{ asset("logo/netral1.png") }}' : '{{ asset("logo/netral.png") }}',
                        'senyum': isFemale ? '{{ asset("logo/senyum1.png") }}' : '{{ asset("logo/senyum.png") }}',
                        'sedih': isFemale ? '{{ asset("logo/sedih1.png") }}' : '{{ asset("logo/sedih.png") }}',
                        'lelah': isFemale ? '{{ asset("logo/lelah1.png") }}' : '{{ asset("logo/lelah.png") }}',
                        'marah': isFemale ? '{{ asset("logo/marah1.png") }}' : '{{ asset("logo/marah.png") }}',
                    };
                    
                    // Set the appropriate emoticon
                    moodEmoticon.src = emoticonPaths[data.moodRecord.mood] || emoticonPaths['netral'];
                    moodEmoticon.alt = data.moodRecord.mood;
                    
                    // Set mood label based on mood type
                    const moodLabels = {
                        'senyum': 'Senang',
                        'sedih': 'Sedih',
                        'lelah': 'Lelah',
                        'marah': 'Marah',
                        'netral': 'Biasa Saja'
                    };
                    
                    moodLabel.textContent = moodLabels[data.moodRecord.mood] || data.moodRecord.mood;
                    
                    document.getElementById('detail-mood-date').textContent = new Date(data.moodRecord.created_at).toLocaleDateString('id-ID');
                } else {
                    document.getElementById('detail-mood-reason').textContent = 'Tidak ada catatan mood';
                    document.getElementById('detail-mood-action').textContent = 'Tidak ada catatan mood';
                    document.getElementById('detail-mood-emoticon').src = '{{ asset("logo/netral.png") }}';
                    document.getElementById('detail-mood-label').textContent = 'Tidak ada catatan';
                    document.getElementById('detail-mood-date').textContent = '-';
                }
                
                // Show the modal
                const detailModal = new bootstrap.Modal(document.getElementById('employeeDetailModal'));
                detailModal.show();
            } else {
                alert('Gagal mengambil data karyawan');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mengambil data karyawan');
        });
    }
    

</script>

<!-- Modal Detail Karyawan (dengan data-turbo-permanent untuk mencegah perubahan oleh Turbo) -->
<div class="modal fade" id="employeeDetailModal" tabindex="-1" aria-labelledby="employeeDetailModalLabel" aria-hidden="true" data-turbo="false">
    <div class="modal-dialog">
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
                    <label class="form-label fw-bold">Mood Terakhir</label>
                    <div class="d-flex align-items-center mb-2">
                        <img id="detail-mood-emoticon" class="me-2" style="width: 30px; height: 30px;" alt="Mood Emoticon">
                        <span id="detail-mood-label"></span>
                        <span class="text-muted ms-2" id="detail-mood-date"></span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Alasan</label>
                    <p id="detail-mood-reason" class="mb-1"></p>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Saran Tindakan</label>
                    <p id="detail-mood-action" class="mb-1"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection