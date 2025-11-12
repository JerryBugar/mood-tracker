<turbo-frame id="dashboard_content">
<div class="form-group mb-3">
    <div class="input-group">
        <input type="text" id="employee-search" class="form-control" placeholder="Cari karyawan berdasarkan nama atau divisi...">
        <button class="btn btn-outline-secondary" type="button" id="search-button">Cari</button>
    </div>
</div>
<div class="employee-list">
    @forelse($employees as $employee)
    <div class="employee-item">
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

<script>
    function viewEmployeeDetail(userId) {
        // Fetch user details and all mood records
        fetch(`/admin/user/${userId}/detail`)
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                // Fill modal with user details
                document.getElementById('detail-user-name').textContent = data.user.name;
                document.getElementById('detail-user-email').textContent = data.user.email;
                document.getElementById('detail-user-division').textContent = data.user.division || 'Tidak ada divisi';
                document.getElementById('detail-user-gender').textContent = data.user.jenis_kelamin || 'Tidak diset';

                // Get container for mood records
                const container = document.getElementById('mood-records-container');

                if(data.moodRecords && data.moodRecords.length > 0) {
                    // Clear container
                    container.innerHTML = '';

                    // Determine gender for emoticon selection
                    const isFemale = data.user.jenis_kelamin === 'Perempuan' || data.user.jenis_kelamin === 'Cewek';

                    // Define emoticon paths based on mood and gender
                    const emoticonPaths = {
                        'netral': isFemale ? '{{ asset("logo/netral1.png") }}' : '{{ asset("logo/netral.png") }}',
                        'senyum': isFemale ? '{{ asset("logo/senyum1.png") }}' : '{{ asset("logo/senyum.png") }}',
                        'sedih': isFemale ? '{{ asset("logo/sedih1.png") }}' : '{{ asset("logo/sedih.png") }}',
                        'lelah': isFemale ? '{{ asset("logo/lelah1.png") }}' : '{{ asset("logo/lelah.png") }}',
                        'marah': isFemale ? '{{ asset("logo/marah1.png") }}' : '{{ asset("logo/marah.png") }}',
                    };

                    // Define mood labels
                    const moodLabels = {
                        'senyum': 'Senang',
                        'sedih': 'Sedih',
                        'lelah': 'Lelah',
                        'marah': 'Marah',
                        'netral': 'Biasa Saja'
                    };

                    // Add each mood record to container
                    data.moodRecords.forEach((record, index) => {
                        const moodCard = document.createElement('div');
                        moodCard.className = 'card mb-3';

                        // Format date using Indonesian locale
                        const date = new Date(record.created_at);
                        const formattedDate = date.toLocaleDateString('id-ID', {
                            day: '2-digit',
                            month: 'long',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });

                        moodCard.innerHTML = `
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    <img src="${emoticonPaths[record.mood] || emoticonPaths['netral']}"
                                         class="me-2"
                                         style="width: 30px; height: 30px;"
                                         alt="${record.mood}">
                                    <span class="fw-bold">${moodLabels[record.mood] || record.mood}</span>
                                    <span class="text-muted ms-2">${formattedDate}</span>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label fw-bold">Alasan</label>
                                    <p class="mb-1">${record.reason || 'Tidak ada alasan'}</p>
                                </div>

                                <div>
                                    <label class="form-label fw-bold">Saran Tindakan</label>
                                    <p class="mb-1">${record.action_suggestion || 'Tidak ada saran tindakan'}</p>
                                </div>
                            </div>
                        `;

                        container.appendChild(moodCard);
                    });
                } else {
                    container.innerHTML = '<p>Tidak ada catatan mood</p>';
                }

                // Show the modal
                const detailModal = new bootstrap.Modal(document.getElementById('employeeDetailModal'));
                detailModal.show();
            } else {
                alert('Gagal mengambil data karyawan');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mengambil data karyawan');
        });
    }

    // Event listener untuk search
    function initializeEmployeeSearch() {
        const searchButton = document.getElementById('search-button');
        const searchInput = document.getElementById('employee-search');

        if (searchButton && searchInput) {
            searchButton.addEventListener('click', function() {
                const searchTerm = searchInput.value.toLowerCase();
                const employeeItems = document.querySelectorAll('.employee-item');

                employeeItems.forEach(item => {
                    const employeeName = item.querySelector('.employee-name').textContent.toLowerCase();
                    const employeeDivision = item.querySelector('.text-muted').textContent.toLowerCase();
                    
                    if (employeeName.includes(searchTerm) || employeeDivision.includes(searchTerm)) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });

            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    document.getElementById('search-button').click();
                }
            });
        }
    }

    // Cek apakah kita sedang dalam konteks Turbo dan inisialisasi pencarian
    if (typeof Turbo !== 'undefined') {
        document.addEventListener('turbo:load', function() {
            // Tambahkan sedikit delay untuk memastikan DOM telah diperbarui
            setTimeout(() => {
                initializeEmployeeSearch();
            }, 100);
        });
    } else {
        // Jika tidak menggunakan Turbo, gunakan DOMContentLoaded
        document.addEventListener('DOMContentLoaded', function() {
            initializeEmployeeSearch();
        });
    }
</script>
</turbo-frame>