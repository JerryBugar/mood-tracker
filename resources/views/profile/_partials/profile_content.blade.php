<div class="profile-header">
    @if(Auth::user()->avatar)
        <img src="{{ Auth::user()->avatar }}" alt="Avatar" class="profile-avatar">
    @else
        <div class="profile-avatar bg-light d-flex align-items-center justify-content-center" style="border: 4px solid #d98695;">
            <span class="text-muted" style="font-size: 3rem;">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
        </div>
    @endif
    <h2 class="profile-name">{{ Auth::user()->name }}</h2>
    <p class="profile-email">{{ Auth::user()->email }}</p>
</div>

<div class="profile-section">
    <div class="profile-detail">
        <span class="detail-label">Nama Lengkap</span>
        <span class="detail-value">{{ Auth::user()->name }}</span>
    </div>
    
    <div class="profile-detail">
        <span class="detail-label">Email</span>
        <span class="detail-value">{{ Auth::user()->email }}</span>
    </div>
    
    <div class="profile-detail">
        <span class="detail-label">Divisi</span>
        <span class="detail-value">{{ Auth::user()->division ?: '-' }}</span>
    </div>
    
    <div class="profile-detail">
        <span class="detail-label">Role</span>
        <span class="detail-value">{{ Auth::user()->role ?: '-' }}</span>
    </div>
    
    <div class="profile-detail">
        <span class="detail-label">Jenis Kelamin</span>
        <span class="detail-value">{{ Auth::user()->jenis_kelamin ?: '-' }}</span>
    </div>
    
    <div class="profile-detail">
        <span class="detail-label">Status Verifikasi</span>
        <span class="detail-value">
            @if(Auth::user()->is_verified)
                <span class="badge bg-success">Terverifikasi</span>
            @else
                <span class="badge bg-warning text-dark">Belum Terverifikasi</span>
            @endif
        </span>
    </div>
</div>

