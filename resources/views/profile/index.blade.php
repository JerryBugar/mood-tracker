@extends('layouts.internal')

@push('styles')
@vite(['resources/css/profile/profile.css'])
@endpush

@section('main-content')

<div class="container-fluid">

    <div class="profile-card">
        <div id="profile-content">
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
        </div>

        <div class="button-container">
            <button class="btn-edit" onclick="showEditProfile()">
                <i class="bi bi-pencil-square"></i>
                Edit Profil
            </button>
            
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
            
            <button class="btn-logout" onclick="confirmLogout()">
                <i class="bi bi-box-arrow-right"></i>
                Keluar
            </button>
        </div>
    </div>
</div>



<!-- Modal Edit Profil (dengan data-turbo-permanent untuk mencegah perubahan oleh Turbo) -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true" data-turbo-permanent>
    <div id="edit-profile-modal-container" class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProfileModalLabel">Edit Profil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="edit-profile-form" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" data-turbo="true">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ Auth::user()->name }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ Auth::user()->email }}" disabled>
                        <div class="form-text">Email tidak dapat diubah</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="division" class="form-label">Divisi</label>
                        <input type="text" class="form-control" id="division" name="division" value="{{ Auth::user()->division ?: '' }}">
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <input type="text" class="form-control" id="role" name="role" value="{{ Auth::user()->role ?: '' }}">
                    </div>
                    
                    <div class="mb-3">
                        <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                        <select class="form-select" id="jenis_kelamin" name="jenis_kelamin">
                            <option value="">Pilih jenis kelamin</option>
                            <option value="Laki-laki" {{ Auth::user()->jenis_kelamin === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Perempuan" {{ Auth::user()->jenis_kelamin === 'Perempuan' || Auth::user()->jenis_kelamin === 'Cewek' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="avatar" class="form-label">Avatar</label>
                        <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                        <div class="form-text">Pilih gambar baru untuk avatar Anda (Maksimal: 3 MB)</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn" style="background-color: #83282f; color: white;">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Logout -->
<div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-labelledby="logoutConfirmModalLabel" aria-hidden="true" data-turbo-permanent>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutConfirmModalLabel">Konfirmasi Keluar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="closeLogoutModalBtn"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Apakah Anda yakin ingin keluar?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelLogoutBtn">Tidak</button>
                <button type="button" class="btn btn-danger" id="confirmLogoutBtn">Ya, Keluar</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;">
    <div id="successToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header" style="background-color: #d4edda; color: #155724;">
            <strong class="me-auto">Notifikasi</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" style="background-color: #d4edda; color: #155724;">
            Profil berhasil diperbarui!
        </div>
    </div>
</div>

@push('scripts')
@vite(['resources/js/profile/profile.js'])
@endpush
@endsection