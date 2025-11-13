<turbo-frame id="dashboard_content">
<div class="chart-container notification-form-container">
    <h3 class="section-title">Kirim Notifikasi</h3>
    <form id="notification-form" class="notification-form">
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

<style>
    .notification-form-container {
        max-width: 700px;
        margin: 0 auto;
        position: relative;
        overflow: visible;
    }

    .chart-container {
        position: relative;
        overflow: visible;
    }

    .notification-form {
        background: linear-gradient(135deg, #fff5f5 0%, #ffe5e5 100%);
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(130, 36, 45, 0.1);
        border: 2px solid #ffd0d0;
        position: relative;
        overflow: visible;
    }

    .notification-form .form-group {
        margin-bottom: 25px;
        position: relative;
        z-index: 1;
    }



    .notification-form .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #82242d;
        font-size: 0.95rem;
    }

    .notification-form .form-control {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #ffd0d0;
        border-radius: 10px;
        font-size: 1rem;
        background-color: #ffffff;
        color: #333;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(130, 36, 45, 0.05);
    }

    .notification-form .form-control:focus {
        outline: none;
        border-color: #82242d;
        box-shadow: 0 0 0 3px rgba(130, 36, 45, 0.1);
        background-color: #fff;
    }

    .notification-form .form-control:hover {
        border-color: #ffb3b3;
    }

    .notification-form textarea.form-control {
        resize: vertical;
        min-height: 120px;
        font-family: inherit;
    }

    .notification-form select.form-control {
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2382242d' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 16px center;
        padding-right: 40px;
    }

    .btn-notification-submit {
        width: 100%;
        background: linear-gradient(135deg, #82242d 0%, #a82d3a 100%);
        color: white;
        border: none;
        padding: 14px 24px;
        border-radius: 10px;
        font-size: 1.05rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(130, 36, 45, 0.3);
        margin-top: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn-notification-submit:hover:not(:disabled) {
        background: linear-gradient(135deg, #6a1d24 0%, #82242d 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(130, 36, 45, 0.4);
    }

    .btn-notification-submit:active:not(:disabled) {
        transform: translateY(0);
        box-shadow: 0 2px 10px rgba(130, 36, 45, 0.3);
    }

    .btn-notification-submit:disabled {
        background: linear-gradient(135deg, #d98695 0%, #ffb3b3 100%);
        cursor: not-allowed;
        opacity: 0.7;
        transform: none;
    }

    /* Animasi untuk form fields */
    .notification-form .form-group {
        animation: fadeInUp 0.4s ease forwards;
    }

    .notification-form .form-group:nth-child(1) { animation-delay: 0.1s; }
    .notification-form .form-group:nth-child(2) { animation-delay: 0.2s; }
    .notification-form .form-group:nth-child(3) { animation-delay: 0.3s; }
    .notification-form .form-group:nth-child(4) { animation-delay: 0.4s; }
    .notification-form .form-group:nth-child(5) { animation-delay: 0.5s; }
    .notification-form .form-group:nth-child(6) { animation-delay: 0.6s; }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Styling untuk section title */
    .notification-form-container .section-title {
        color: #82242d;
        margin-bottom: 25px;
        font-size: 1.8rem;
        text-align: center;
        position: relative;
        padding-bottom: 15px;
    }

    .notification-form-container .section-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 3px;
        background: linear-gradient(90deg, #82242d 0%, #ffb3b3 100%);
        border-radius: 2px;
    }

    /* Searchable Dropdown Styles */
    .searchable-dropdown {
        position: relative;
        z-index: 1;
    }

    .employee-search-input {
        position: relative;
        z-index: 2;
    }

    .dropdown-list {
        position: absolute;
        top: calc(100% + 2px);
        left: 0;
        right: 0;
        background: white;
        border: 2px solid #ffd0d0;
        border-radius: 0 0 10px 10px;
        max-height: 300px;
        overflow-y: auto;
        overflow-x: hidden;
        display: none;
        z-index: 9999;
        box-shadow: 0 8px 25px rgba(130, 36, 45, 0.2);
        margin-top: 0;
    }

    .dropdown-list.show {
        display: block;
    }

    .dropdown-item {
        padding: 12px 16px;
        cursor: pointer;
        transition: all 0.2s ease;
        border-bottom: 1px solid #ffe5e5;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .dropdown-item:last-child {
        border-bottom: none;
    }

    .dropdown-item:hover {
        background-color: #fff5f5;
    }

    .dropdown-item.selected {
        background-color: #ffe5e5;
        border-left: 3px solid #82242d;
    }

    .dropdown-item strong {
        color: #82242d;
        font-size: 0.95rem;
    }

    .dropdown-email {
        color: #6c757d;
        font-size: 0.85rem;
    }

    .dropdown-item.no-results {
        padding: 20px;
        text-align: center;
        color: #6c757d;
        cursor: default;
    }

    .dropdown-item.no-results:hover {
        background-color: white;
    }

    /* Scrollbar styling untuk dropdown */
    .dropdown-list::-webkit-scrollbar {
        width: 8px;
    }

    .dropdown-list::-webkit-scrollbar-track {
        background: #ffe5e5;
    }

    .dropdown-list::-webkit-scrollbar-thumb {
        background: #ffb3b3;
        border-radius: 4px;
    }

    .dropdown-list::-webkit-scrollbar-thumb:hover {
        background: #d98695;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .notification-form {
            padding: 20px;
        }

        .notification-form-container .section-title {
            font-size: 1.5rem;
        }

        .btn-notification-submit {
            padding: 12px 20px;
            font-size: 1rem;
        }

        .dropdown-list {
            max-height: 250px;
        }
    }
</style>

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
        const employeeSearchInput = document.getElementById('employee-search-input');
        const employeeDropdownList = document.getElementById('employee-dropdown-list');
        const divisionSelect = document.getElementById('division-select');

        if (!notificationForm || !notificationType) {
            console.error('Form elements not found');
            return;
        }

        // Hapus event listener lama jika ada
        if (submitHandler) {
            notificationForm.removeEventListener('submit', submitHandler);
        }

        // Initialize searchable dropdown untuk karyawan
        function initializeEmployeeSearchable() {
            if (!employeeSearchInput || !employeeDropdownList) return;

            const allItems = employeeDropdownList.querySelectorAll('.dropdown-item:not(.no-results)');
            let selectedItem = null;

            // Fungsi untuk filter items
            function filterItems(searchTerm) {
                const term = searchTerm.toLowerCase().trim();
                let visibleCount = 0;

                allItems.forEach(item => {
                    const name = item.dataset.name.toLowerCase();
                    const email = item.dataset.email.toLowerCase();
                    const matches = name.includes(term) || email.includes(term);

                    if (matches) {
                        item.style.display = 'flex';
                        visibleCount++;
                    } else {
                        item.style.display = 'none';
                    }
                });

                // Tampilkan "no results" jika tidak ada yang cocok
                let noResults = employeeDropdownList.querySelector('.no-results');
                if (visibleCount === 0 && term !== '') {
                    if (!noResults) {
                        noResults = document.createElement('div');
                        noResults.className = 'dropdown-item no-results';
                        noResults.textContent = 'Tidak ada karyawan yang ditemukan';
                        employeeDropdownList.appendChild(noResults);
                    }
                    noResults.style.display = 'flex';
                } else if (noResults) {
                    noResults.style.display = 'none';
                }

                // Tampilkan dropdown jika ada hasil
                if (term !== '' && visibleCount > 0) {
                    employeeDropdownList.classList.add('show');
                    // Pastikan z-index dan margin sudah diatur
                    if (employeeSelectContainer) {
                        employeeSelectContainer.style.zIndex = '10000';
                        employeeSelectContainer.style.position = 'relative';
                        employeeSelectContainer.style.marginBottom = '280px';
                    }
                } else if (term === '') {
                    employeeDropdownList.classList.add('show');
                    if (employeeSelectContainer) {
                        employeeSelectContainer.style.zIndex = '10000';
                        employeeSelectContainer.style.position = 'relative';
                        employeeSelectContainer.style.marginBottom = '280px';
                    }
                } else {
                    // Jika tidak ada hasil, tetap tampilkan dropdown dengan pesan no results
                    employeeDropdownList.classList.add('show');
                    if (employeeSelectContainer) {
                        employeeSelectContainer.style.zIndex = '10000';
                        employeeSelectContainer.style.position = 'relative';
                        employeeSelectContainer.style.marginBottom = '280px';
                    }
                }
            }

            // Event listener untuk input search
            employeeSearchInput.addEventListener('input', function(e) {
                filterItems(this.value);
            });

            // Event listener untuk focus
            employeeSearchInput.addEventListener('focus', function() {
                if (this.value.trim() === '') {
                    allItems.forEach(item => item.style.display = 'flex');
                }
                employeeDropdownList.classList.add('show');
                // Tingkatkan z-index form-group saat dropdown terbuka
                if (employeeSelectContainer) {
                    employeeSelectContainer.style.zIndex = '10000';
                    employeeSelectContainer.style.position = 'relative';
                    employeeSelectContainer.style.marginBottom = '280px';
                }
            });

            // Event listener untuk click item
            allItems.forEach(item => {
                item.addEventListener('click', function() {
                    const value = this.dataset.value;
                    const name = this.dataset.name;
                    const email = this.dataset.email;

                    // Update hidden input
                    if (employeeSelect) {
                        employeeSelect.value = value;
                    }

                    // Update search input dengan nama yang dipilih
                    employeeSearchInput.value = `${name} (${email})`;

                    // Update selected state
                    if (selectedItem) {
                        selectedItem.classList.remove('selected');
                    }
                    this.classList.add('selected');
                    selectedItem = this;

                    // Sembunyikan dropdown
                    employeeDropdownList.classList.remove('show');
                    // Reset z-index dan margin form-group
                    if (employeeSelectContainer) {
                        employeeSelectContainer.style.zIndex = '';
                        employeeSelectContainer.style.marginBottom = '';
                    }
                });
            });

            // Sembunyikan dropdown saat klik di luar
            document.addEventListener('click', function(e) {
                if (!employeeSelectContainer.contains(e.target)) {
                    employeeDropdownList.classList.remove('show');
                    // Reset z-index dan margin form-group
                    if (employeeSelectContainer) {
                        employeeSelectContainer.style.zIndex = '';
                        employeeSelectContainer.style.marginBottom = '';
                    }
                }
            });
        }

        // Toggle visibility dropdown berdasarkan jenis notifikasi
        function toggleDropdowns() {
            const type = notificationType.value;
            
            if (type === 'individual') {
                if (employeeSelectContainer) employeeSelectContainer.style.display = 'block';
                if (divisionSelectContainer) divisionSelectContainer.style.display = 'none';
                if (employeeSelect) employeeSelect.required = true;
                if (divisionSelect) divisionSelect.required = false;
                // Initialize searchable dropdown
                setTimeout(() => initializeEmployeeSearchable(), 100);
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

            if (type === 'individual' && (!userId || userId === '')) {
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
                    // Reset search input juga
                    if (employeeSearchInput) {
                        employeeSearchInput.value = '';
                    }
                    // Reset selected item
                    if (employeeDropdownList) {
                        const selected = employeeDropdownList.querySelector('.selected');
                        if (selected) {
                            selected.classList.remove('selected');
                        }
                    }
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
