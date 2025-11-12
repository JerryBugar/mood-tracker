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

        <div class="form-group mb-3">
            <label for="message" class="form-label">Pesan</label>
            <textarea id="message" class="form-control" rows="4" placeholder="Tulis pesan notifikasi..."></textarea>
        </div>

        <div class="form-group mb-3">
            <label for="schedule-time" class="form-label">Jadwal Kirim (opsional)</label>
            <input type="datetime-local" id="schedule-time" class="form-control">
        </div>

        <button type="submit" class="btn-login">Kirim Notifikasi</button>
    </form>
</div>

<script>
    function initializeNotificationForm() {
        // Event listener untuk form notifikasi
        const notificationForm = document.getElementById('notification-form');
        if (notificationForm) {
            notificationForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const notificationType = document.getElementById('notification-type').value;
                const message = document.getElementById('message').value;
                const scheduleTime = document.getElementById('schedule-time').value;
                
                // Lakukan pengiriman notifikasi disini
                console.log({
                    type: notificationType,
                    message: message,
                    schedule: scheduleTime
                });
                
                // Tampilkan pesan sukses
                alert('Notifikasi berhasil dikirim!');
            });
        }
    }

    // Cek apakah kita sedang dalam konteks Turbo dan inisialisasi form
    if (typeof Turbo !== 'undefined') {
        document.addEventListener('turbo:load', function() {
            // Tambahkan sedikit delay untuk memastikan DOM telah diperbarui
            setTimeout(() => {
                initializeNotificationForm();
            }, 100);
        });
    } else {
        // Jika tidak menggunakan Turbo, gunakan DOMContentLoaded
        document.addEventListener('DOMContentLoaded', function() {
            initializeNotificationForm();
        });
    }
</script>
</turbo-frame>