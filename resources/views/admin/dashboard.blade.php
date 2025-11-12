@extends('layouts.admin')

@push('styles')
@vite('resources/css/admin/dashboard.css')
@endpush

@section('main-content')

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
</div>

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
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <label class="form-label fw-bold mb-0">Catatan Mood</label>
                        <div class="d-flex gap-2">
                            <select id="filter-type" class="form-select form-select-sm" style="width: auto;">
                                <option value="">Semua</option>
                                <option value="day">Hari</option>
                                <option value="month">Bulan</option>
                                <option value="year">Tahun</option>
                            </select>
                            <input type="date" id="filter-day" class="form-control form-control-sm" style="width: auto; display: none;" max="{{ date('Y-m-d') }}">
                            <input type="month" id="filter-month" class="form-control form-control-sm" style="width: auto; display: none;" max="{{ date('Y-m') }}">
                            <input type="number" id="filter-year" class="form-control form-control-sm" style="width: auto; display: none;" min="2020" max="2025" value="2025" placeholder="Tahun">
                        </div>
                    </div>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@vite('resources/js/admin/dashboard.js')
@endpush

@endsection