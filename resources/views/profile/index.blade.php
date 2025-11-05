@extends('layouts.internal')

@section('main-content')
<style>
    .profile-card {
        background-color: #fff;
        border-radius: 15px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        padding: 30px;
        margin-top: 20px;
    }

    .profile-header {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-bottom: 30px;
    }

    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #d98695;
        margin-bottom: 15px;
    }

    .profile-name {
        font-size: 1.8rem;
        color: #82272c;
        margin-bottom: 5px;
        font-weight: bold;
    }

    .profile-email {
        color: #6c757d;
        margin-bottom: 20px;
    }

    .profile-detail {
        display: flex;
        flex-direction: column;
        padding: 12px 0;
        border-bottom: 1px solid #eee;
    }

    .detail-label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 5px;
    }

    .detail-value {
        color: #6c757d;
    }
    
    @media (min-width: 768px) {
        .profile-detail {
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
        }
        
        .detail-label {
            margin-bottom: 0;
        }
    }

    .btn-edit {
        background-color: #661118ff;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 1rem;
        cursor: pointer;
        width: 100%;
        margin-top: 20px;
    }

    .btn-logout {
        background-color: #dc3545;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 1rem;
        cursor: pointer;
        width: 100%;
        margin-top: 10px;
    }
    
    .profile-section {
        margin-bottom: 20px;
    }
    
    .button-container {
        display: flex;
        gap: 10px;
        flex-direction: column;
    }
    
    @media (min-width: 768px) {
        .profile-detail {
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }
        
        .detail-label {
            flex: 1;
            text-align: left;
            margin-bottom: 0;
        }
        
        .detail-value {
            flex: 1.5;
            text-align: right;
            padding-left: 15px;
        }
        
        .button-container {
            flex-direction: row;
        }
        
        .btn-edit, .btn-logout {
            flex: 1;
            width: auto;
            margin-top: 20px;
        }
    }
    
    @media (max-width: 767px) {
        .profile-card {
            padding: 20px;
            margin: 10px;
        }
        
        .profile-name {
            font-size: 1.5rem;
        }
    }
</style>

<div class="container-fluid">

    <div class="profile-card">
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

<!-- Modal Edit Profil -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProfileModalLabel">Edit Profil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editProfileForm" method="POST" action="{{ route('profile.update') }}">
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
                        <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                        <select class="form-select" id="jenis_kelamin" name="jenis_kelamin">
                            <option value="">Pilih jenis kelamin</option>
                            <option value="Laki-laki" {{ Auth::user()->jenis_kelamin === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Cewek" {{ Auth::user()->jenis_kelamin === 'Cewek' ? 'selected' : '' }}>Cewek</option>
                        </select>
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

<script>
    function showEditProfile() {
        // Buka modal edit profil
        const modal = new bootstrap.Modal(document.getElementById('editProfileModal'));
        modal.show();
    }
    
    function confirmLogout() {
        if (confirm('Apakah Anda yakin ingin keluar?')) {
            document.getElementById('logout-form').submit();
        }
    }
    
    // Setelah form disubmit, tutup modal dan tampilkan notifikasi
    document.getElementById('editProfileForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Dapatkan data form
        const formData = new FormData(this);
        
        // Kirim data menggunakan fetch
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-HTTP-Method-Override': 'PUT'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update informasi di halaman
                document.querySelector('.profile-name').textContent = data.user.name;
                document.querySelector('.profile-email').textContent = data.user.email;
                
                // Update info detail
                document.querySelectorAll('.detail-value')[0].textContent = data.user.name;
                document.querySelectorAll('.detail-value')[1].textContent = data.user.email;
                document.querySelectorAll('.detail-value')[2].textContent = data.user.division || '-';
                document.querySelectorAll('.detail-value')[3].textContent = data.user.jenis_kelamin || '-';
                
                // Tutup modal
                bootstrap.Modal.getInstance(document.getElementById('editProfileModal')).hide();
                
                // Tampilkan notifikasi sukses
                alert('Profil berhasil diperbarui!');
                
                // Refresh halaman untuk memastikan semua data terupdate
                location.reload();
            } else {
                alert('Terjadi kesalahan saat memperbarui profil: ' + (data.message || 'Silakan coba lagi'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat memperbarui profil. Silakan coba lagi.');
        });
    });
</script>
@endsection