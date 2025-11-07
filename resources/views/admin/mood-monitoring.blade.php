@extends('layouts.admin')

@section('main-content')
<style>
    .mood-monitoring {
        padding: 20px;
    }
    
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }
    
    .page-title {
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
    
    .filters {
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .filter-row {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        align-items: end;
    }
    
    .form-group {
        flex: 1;
        min-width: 200px;
    }
    
    .form-label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
        color: #495057;
    }
    
    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        font-size: 1rem;
    }
    
    .btn-filter {
        background-color: #661118ff;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 1rem;
        cursor: pointer;
        height: fit-content;
    }
    
    .mood-table-container {
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-bottom: 30px;
    }
    
    .mood-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .mood-table th,
    .mood-table td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }
    
    .mood-table th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #495057;
    }
    
    .employee-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .employee-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }
    
    .mood-indicator {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
    }
    
    .mood-happy { background-color: #28a745; }
    .mood-sad { background-color: #dc3545; }
    .mood-tired { background-color: #ffc107; }
    .mood-angry { background-color: #6f42c1; }
    .mood-neutral { background-color: #6c757d; }
    
    .action-buttons {
        display: flex;
        gap: 10px;
    }
    
    .btn-notification {
        background-color: #007bff;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 0.9rem;
        cursor: pointer;
    }
    
    .btn-schedule {
        background-color: #28a745;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 0.9rem;
        cursor: pointer;
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
    
    .pagination {
        display: flex;
        justify-content: center;
        margin-top: 20px;
    }
    
    .pagination a, .pagination span {
        padding: 8px 16px;
        margin: 0 4px;
        text-decoration: none;
        border: 1px solid #ddd;
        color: #007bff;
    }
    
    .pagination .active {
        background-color: #007bff;
        color: white;
    }
</style>

<div class="mood-monitoring">
    <div class="page-header">
        <h2 class="page-title">Monitoring Mood Karyawan</h2>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </div>
    
    <div class="filters">
        <div class="filter-row">
            <div class="form-group">
                <label for="division" class="form-label">Divisi</label>
                <select id="division" class="form-control">
                    <option value="">Semua Divisi</option>
                    <option value="IT">IT Department</option>
                    <option value="HR">HR Department</option>
                    <option value="Finance">Finance Department</option>
                    <option value="Marketing">Marketing Department</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="date-range" class="form-label">Rentang Tanggal</label>
                <input type="date" id="start-date" class="form-control" placeholder="Tanggal Mulai">
            </div>
            
            <div class="form-group">
                <label for="date-range" class="form-label">&nbsp;</label>
                <input type="date" id="end-date" class="form-control" placeholder="Tanggal Akhir">
            </div>
            
            <div class="form-group">
                <label for="mood" class="form-label">Mood</label>
                <select id="mood" class="form-control">
                    <option value="">Semua Mood</option>
                    <option value="happy">Bahagia</option>
                    <option value="sad">Sedih</option>
                    <option value="tired">Lelah</option>
                    <option value="angry">Marah</option>
                    <option value="neutral">Netral</option>
                </select>
            </div>
            
            <div class="form-group" style="margin-top: auto;">
                <button type="button" class="btn-filter">Filter</button>
            </div>
        </div>
    </div>
    
    <div class="chart-container">
        <h3 class="section-title">Grafik Mood Karyawan</h3>
        <canvas id="employeeMoodChart" width="400" height="200"></canvas>
    </div>
    
    <div class="mood-table-container">
        <table class="mood-table">
            <thead>
                <tr>
                    <th>Karyawan</th>
                    <th>Divisi</th>
                    <th>Tanggal</th>
                    <th>Mood</th>
                    <th>Alasan</th>
                    <th>Tindakan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($moodRecords as $record)
                <tr>
                    <td>
                        <div class="employee-info">
                            @if($record->user->avatar)
                                <img src="{{ $record->user->avatar }}" alt="Avatar" class="employee-avatar">
                            @else
                                <div class="employee-avatar bg-light d-flex align-items-center justify-content-center">
                                    {{ strtoupper(substr($record->user->name, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <div>{{ $record->user->name }}</div>
                                <div class="text-muted small">{{ $record->user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $record->user->division ?: 'Tidak ada divisi' }}</td>
                    <td>{{ $record->created_at->format('d M Y') }}</td>
                    <td>
                        @php
                            $moodClass = '';
                            $moodLabel = '';
                            switch($record->mood) {
                                case 'senyum':
                                    $moodClass = 'mood-happy';
                                    $moodLabel = 'Senang';
                                    break;
                                case 'sedih':
                                    $moodClass = 'mood-sad';
                                    $moodLabel = 'Sedih';
                                    break;
                                case 'lelah':
                                    $moodClass = 'mood-tired';
                                    $moodLabel = 'Lelah';
                                    break;
                                case 'marah':
                                    $moodClass = 'mood-angry';
                                    $moodLabel = 'Marah';
                                    break;
                                case 'netral':
                                    $moodClass = 'mood-neutral';
                                    $moodLabel = 'Biasa Saja';
                                    break;
                                default:
                                    $moodClass = 'mood-neutral';
                                    $moodLabel = $record->mood;
                            }
                        @endphp
                        <span><div class="mood-indicator {{ $moodClass }}"></div> {{ $moodLabel }}</span>
                    </td>
                    <td>{{ $record->reason ?: '-' }}</td>
                    <td>{{ $record->action_suggestion ?: '-' }}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-notification" onclick="sendNotification({{ $record->user->id }})">Notifikasi</button>
                            <button class="btn-schedule" onclick="scheduleTask({{ $record->user->id }})">Jadwal</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data mood hari ini</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="pagination">
        {{ $moodRecords->links() }}
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Initialize chart
    const moodCtx = document.getElementById('employeeMoodChart').getContext('2d');
    const moodChart = new Chart(moodCtx, {
        type: 'bar',
        data: {
            labels: ['Ahmad', 'Budi', 'Citra', 'Dian', 'Eko', 'Fani'],
            datasets: [{
                label: 'Kebahagiaan (1-10)',
                data: [8, 3, 4, 2, 7, 6],
                backgroundColor: [
                    'rgba(40, 167, 69, 0.7)',
                    'rgba(220, 53, 69, 0.7)',
                    'rgba(255, 193, 7, 0.7)',
                    'rgba(111, 66, 193, 0.7)',
                    'rgba(0, 123, 255, 0.7)',
                    'rgba(23, 162, 184, 0.7)'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Tingkat Kebahagiaan Karyawan'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 10
                }
            }
        }
    });
    
    // Filter button event
    document.querySelector('.btn-filter').addEventListener('click', function() {
        alert('Filter diterapkan!');
    });
    
    function sendNotification(userId) {
        alert('Fitur kirim notifikasi untuk user ID: ' + userId + ' akan segera tersedia');
    }
    
    function scheduleTask(userId) {
        alert('Fitur penjadwalan untuk user ID: ' + userId + ' akan segera tersedia');
    }
</script>
@endsection