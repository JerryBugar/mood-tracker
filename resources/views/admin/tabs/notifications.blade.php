<turbo-frame id="dashboard_content">
<div class="chart-container notification-form-container">
    <h3 class="section-title">Kirim Notifikasi</h3>
    <form id="notification-form" 
          class="notification-form"
          data-route="{{ route('admin.notification.send') }}"
          data-csrf="{{ csrf_token() }}">
        <div class="form-group mb-3">
            <label for="notification-type" class="form-label">Jenis Notifikasi</label>
            <select id="notification-type" class="form-control">
                <option value="individual">Individu</option>
                <option value="group">Grup/Divisi</option>
                <option value="all">Semua Karyawan</option>
            </select>
        </div>

        <!-- Dropdown untuk memilih karyawan (muncul jika individual) -->
        <div class="form-group mb-3" id="employee-select-container" style="display: none;">
            <label for="employee-select" class="form-label">Pilih Karyawan</label>
            <div class="searchable-dropdown">
                <input type="text" 
                       id="employee-search-input" 
                       class="form-control employee-search-input" 
                       placeholder="Cari karyawan..." 
                       autocomplete="off">
                <input type="hidden" id="employee-select" name="employee_id" value="">
                <div class="dropdown-list" id="employee-dropdown-list">
                    @foreach($employees as $employee)
                        <div class="dropdown-item" 
                             data-value="{{ $employee->id }}" 
                             data-name="{{ $employee->name }}" 
                             data-email="{{ $employee->email }}">
                            <strong>{{ $employee->name }}</strong>
                            <span class="dropdown-email">{{ $employee->email }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Dropdown untuk memilih divisi (muncul jika group) -->
        <div class="form-group mb-3" id="division-select-container" style="display: none;">
            <label for="division-select" class="form-label">Pilih Divisi</label>
            <select id="division-select" class="form-control">
                <option value="">-- Pilih Divisi --</option>
                @foreach($divisions as $division)
                    <option value="{{ $division }}">{{ $division }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group mb-3">
            <label for="message" class="form-label">Pesan</label>
            <textarea id="message" class="form-control" rows="4" placeholder="Tulis pesan notifikasi..." required></textarea>
        </div>

        <div class="form-group mb-3">
            <label for="schedule-time" class="form-label">Jadwal Kirim (opsional)</label>
            <input type="datetime-local" id="schedule-time" class="form-control">
        </div>

        <button type="submit" class="btn-notification-submit">Kirim Notifikasi</button>
    </form>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 99999;">
    <div id="notificationToast" class="toast custom-toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-content-wrapper">
            <div class="toast-icon-wrapper">
                <i class="toast-icon bi bi-check-circle-fill"></i>
            </div>
            <div class="toast-body-content">
                <div class="toast-title" id="toast-title">Berhasil</div>
                <div class="toast-message" id="toast-message">Notifikasi berhasil dikirim!</div>
            </div>
            <button type="button" class="toast-close-btn" data-bs-dismiss="toast" aria-label="Close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>
</div>
</turbo-frame>
