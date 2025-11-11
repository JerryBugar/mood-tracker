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

    <div class="nav-tabs">
        <a class="nav-tab active" href="{{ route('admin.dashboard.overview') }}" data-turbo-frame="dashboard_content">Ringkasan</a>
        <a class="nav-tab" href="{{ route('admin.dashboard.employees') }}" data-turbo-frame="dashboard_content">Daftar Karyawan</a>
        <a class="nav-tab" href="{{ route('admin.dashboard.notifications') }}" data-turbo-frame="dashboard_content">Notifikasi</a>
    </div>

    <turbo-frame id="dashboard_content">
        <!-- Tab content will be loaded here via Turbo -->
        <div class="chart-container">
            <h3 class="section-title">Statistik Mood per Divisi</h3>
            <turbo-frame id="division_chart_frame">
                <canvas id="divisionChart" width="400" height="200"></canvas>
            </turbo-frame>
        </div>
    </turbo-frame>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Initialize charts when page loads or Turbo frame renders
    document.addEventListener('turbo:load', function() {
        loadChart();
    });
    
    function loadChart() {
        fetch('/admin/dashboard/chart-data')
        .then(response => response.json())
        .then(data => {
            // Mood trend chart
            const moodCtx = document.getElementById('moodChart').getContext('2d');
            if (moodCtx.chart) {
                moodCtx.chart.destroy();
            }
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
            if (divisionCtx.chart) {
                divisionCtx.chart.destroy();
            }
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
        });
    }

    // Initial chart load
    document.addEventListener('DOMContentLoaded', function() {
        loadChart();
    });
</script>

@endsection