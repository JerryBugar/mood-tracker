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
            <select id="division" name="division" class="form-select">
                <option value="" {{ !Auth::user()->division ? 'selected' : '' }}>Pilih Divisi</option>
                <option value="Marketing" {{ Auth::user()->division == 'Marketing' ? 'selected' : '' }}>Marketing</option>
                <option value="Product" {{ Auth::user()->division == 'Product' ? 'selected' : '' }}>Product</option>
                <option value="IT" {{ Auth::user()->division == 'IT' ? 'selected' : '' }}>IT</option>
                <option value="CDS" {{ Auth::user()->division == 'CDS' ? 'selected' : '' }}>CDS</option>
                <option value="HRD" {{ Auth::user()->division == 'HRD' ? 'selected' : '' }}>HRD</option>
            </select>
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

