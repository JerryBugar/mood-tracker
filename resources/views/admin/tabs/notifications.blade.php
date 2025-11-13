<turbo-frame id="dashboard_content">
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

        <!-- Dropdown untuk memilih karyawan (muncul jika individual) -->
        <div class="form-group mb-3" id="employee-select-container" style="display: none;">
            <label for="employee-select" class="form-label">Pilih Karyawan</label>
            <select id="employee-select" class="form-control">
                <option value="">-- Pilih Karyawan --</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->email }})</option>
                @endforeach
            </select>
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

        <button type="submit" class="btn-login">Kirim Notifikasi</button>
    </form>
</div>

<script>
    // Flag untuk mencegah multiple submissions
    let isSubmitting = false;
    let submitHandler = null;

    function initializeNotificationForm() {
        const notificationForm = document.getElementById('notification-form');
        const notificationType = document.getElementById('notification-type');
        const employeeSelectContainer = document.getElementById('employee-select-container');
        const divisionSelectContainer = document.getElementById('division-select-container');
        const employeeSelect = document.getElementById('employee-select');
        const divisionSelect = document.getElementById('division-select');

        if (!notificationForm || !notificationType) {
            console.error('Form elements not found');
            return;
        }

        // Hapus event listener lama jika ada
        if (submitHandler) {
            notificationForm.removeEventListener('submit', submitHandler);
        }

        // Toggle visibility dropdown berdasarkan jenis notifikasi
        function toggleDropdowns() {
            const type = notificationType.value;
            
            if (type === 'individual') {
                if (employeeSelectContainer) employeeSelectContainer.style.display = 'block';
                if (divisionSelectContainer) divisionSelectContainer.style.display = 'none';
                if (employeeSelect) employeeSelect.required = true;
                if (divisionSelect) divisionSelect.required = false;
            } else if (type === 'group') {
                if (employeeSelectContainer) employeeSelectContainer.style.display = 'none';
                if (divisionSelectContainer) divisionSelectContainer.style.display = 'block';
                if (employeeSelect) employeeSelect.required = false;
                if (divisionSelect) divisionSelect.required = true;
            } else {
                if (employeeSelectContainer) employeeSelectContainer.style.display = 'none';
                if (divisionSelectContainer) divisionSelectContainer.style.display = 'none';
                if (employeeSelect) employeeSelect.required = false;
                if (divisionSelect) divisionSelect.required = false;
            }
        }

        // Event listener untuk perubahan jenis notifikasi
        notificationType.addEventListener('change', toggleDropdowns);

        // Inisialisasi saat pertama kali load
        toggleDropdowns();

        // Buat submit handler baru
        submitHandler = function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Cegah multiple submissions
            if (isSubmitting) {
                return false;
            }

            const type = notificationType.value;
            const message = document.getElementById('message').value;
            const scheduleTime = document.getElementById('schedule-time').value;
            const userId = employeeSelect ? employeeSelect.value : null;
            const division = divisionSelect ? divisionSelect.value : null;

            // Validasi
            if (!message.trim()) {
                alert('Pesan tidak boleh kosong!');
                return false;
            }

            if (type === 'individual' && !userId) {
                alert('Silakan pilih karyawan!');
                return false;
            }

            if (type === 'group' && !division) {
                alert('Silakan pilih divisi!');
                return false;
            }

            // Set flag untuk mencegah multiple submissions
            isSubmitting = true;

            // Disable button saat submit
            const submitBtn = notificationForm.querySelector('button[type="submit"]');
            let originalText = 'Kirim Notifikasi';
            if (submitBtn) {
                originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = 'Mengirim...';
            }

            // Kirim request ke server
            fetch('{{ route("admin.notification.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    type: type,
                    message: message,
                    user_id: userId || null,
                    division: division || null,
                    scheduled_at: scheduleTime || null
                })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Terjadi kesalahan');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    // Reset form
                    notificationForm.reset();
                    toggleDropdowns();
                } else {
                    alert('Gagal mengirim notifikasi: ' + (data.message || 'Terjadi kesalahan'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengirim notifikasi: ' + error.message);
            })
            .finally(() => {
                // Reset flag dan enable button kembali
                isSubmitting = false;
                const btn = notificationForm.querySelector('button[type="submit"]');
                if (btn) {
                    btn.disabled = false;
                    btn.textContent = originalText || 'Kirim Notifikasi';
                }
            });

            return false;
        };

        // Attach submit event listener
        notificationForm.addEventListener('submit', submitHandler);
    }

    // Inisialisasi saat Turbo Frame load
    document.addEventListener('turbo:frame-load', function(event) {
        if (event.target.id === 'dashboard_content') {
            setTimeout(() => {
                initializeNotificationForm();
            }, 100);
        }
    });

    // Fallback untuk DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                initializeNotificationForm();
            }, 100);
        });
    } else {
        setTimeout(() => {
            initializeNotificationForm();
        }, 100);
    }
</script>
</turbo-frame>
