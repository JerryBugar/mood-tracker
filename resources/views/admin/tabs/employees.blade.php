<turbo-frame id="dashboard_content">
<div class="form-group mb-3">
    <div class="input-group">
        <input type="text" id="employee-search" class="form-control" placeholder="Cari karyawan berdasarkan nama atau divisi...">
        <button class="btn btn-outline-secondary" type="button" id="search-button">Cari</button>
    </div>
</div>
<div class="employee-list">
    @forelse($employees as $employee)
    <div class="employee-item {{ $employee->has_mood_today ?? false ? 'has-mood-today' : '' }}" data-employee-id="{{ $employee->id }}">
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
        @if($employee->has_mood_today ?? false)
        <div class="employee-center">
            <span class="employee-date">
                {{ \Carbon\Carbon::parse($employee->mood_today_date ?? now())->locale('id_ID')->translatedFormat('l, j F Y') }}
            </span>
        </div>
        @endif
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
</turbo-frame>