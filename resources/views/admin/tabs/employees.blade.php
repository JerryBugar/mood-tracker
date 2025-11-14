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

<script>
    // Simpan userId yang sedang dilihat untuk filter
    // Gunakan window object untuk menghindari redeclaration error saat Turbo replaceWith
    if (typeof window.currentUserId === 'undefined') {
        window.currentUserId = null;
    }
    if (typeof window.currentUserData === 'undefined') {
        window.currentUserData = null;
    }

    // Fungsi untuk render mood records
    function renderMoodRecords(data) {
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

                // Format admin response date jika ada
                let adminResponseDate = '';
                if (record.admin_response_at) {
                    const responseDate = new Date(record.admin_response_at);
                    adminResponseDate = responseDate.toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'long',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                }

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

                        <div class="mb-3">
                            <label class="form-label fw-bold">Saran Tindakan</label>
                            <p class="mb-1">${record.action_suggestion || 'Tidak ada saran tindakan'}</p>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Respons Admin/HRD</label>
                            ${record.admin_response ? `
                                <div class="alert alert-info mb-2">
                                    <p class="mb-1">${record.admin_response}</p>
                                    <small class="text-muted">Direspons pada: ${adminResponseDate}</small>
                                </div>
                            ` : ''}
                            <div class="admin-response-form" data-record-id="${record.id}">
                                <textarea class="form-control mb-2" 
                                          id="admin-response-${record.id}" 
                                          rows="3" 
                                          placeholder="Tulis respons untuk catatan mood ini...">${record.admin_response || ''}</textarea>
                                <button type="button" 
                                        class="btn btn-sm btn-primary" 
                                        onclick="saveAdminResponse(${record.id})">
                                    ${record.admin_response ? 'Update Respons' : 'Kirim Respons'}
                                </button>
                            </div>
                        </div>
                    </div>
                `;

                container.appendChild(moodCard);
            });
        } else {
            container.innerHTML = '<p class="text-muted">Tidak ada catatan mood untuk periode yang dipilih</p>';
        }
    }

    // Fungsi untuk load mood records dengan filter
    function loadMoodRecordsWithFilter(userId, filterType, filterValue) {
        const url = new URL(`/admin/user/${userId}/detail`, window.location.origin);
        if (filterType && filterValue) {
            url.searchParams.append('filter_type', filterType);
            url.searchParams.append('filter_value', filterValue);
        }

        fetch(url)
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                window.currentUserData = data;
                renderMoodRecords(data);
            } else {
                console.error('Error loading mood records:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    function viewEmployeeDetail(userId) {
        window.currentUserId = userId;
        
        // Hilangkan highlight dari employee item yang diklik
        const employeeItem = document.querySelector(`.employee-item[data-employee-id="${userId}"]`);
        if (employeeItem) {
            employeeItem.classList.remove('has-mood-today');
            const employeeCenter = employeeItem.querySelector('.employee-center');
            if (employeeCenter) {
                employeeCenter.remove();
            }
        }
        
        // Reset filter
        document.getElementById('filter-type').value = '';
        document.getElementById('filter-day').style.display = 'none';
        document.getElementById('filter-month').style.display = 'none';
        document.getElementById('filter-year').style.display = 'none';
        document.getElementById('filter-day').value = '';
        document.getElementById('filter-month').value = '';
        document.getElementById('filter-year').value = '2025';

        // Fetch user details and all mood records
        fetch(`/admin/user/${userId}/detail`)
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                window.currentUserData = data;
                
                // Fill modal with user details
                document.getElementById('detail-user-name').textContent = data.user.name;
                document.getElementById('detail-user-email').textContent = data.user.email;
                document.getElementById('detail-user-division').textContent = data.user.division || 'Tidak ada divisi';
                document.getElementById('detail-user-gender').textContent = data.user.jenis_kelamin || 'Tidak diset';

                // Render mood records
                renderMoodRecords(data);

                // Setup filter event listeners
                setupFilterListeners();

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

    // Setup filter event listeners
    function setupFilterListeners() {
        const filterType = document.getElementById('filter-type');
        const filterDay = document.getElementById('filter-day');
        const filterMonth = document.getElementById('filter-month');
        const filterYear = document.getElementById('filter-year');

        // Reset event listeners untuk menghindari duplicate
        const newFilterType = filterType.cloneNode(true);
        filterType.parentNode.replaceChild(newFilterType, filterType);
        
        // Event listener untuk perubahan filter type
        newFilterType.addEventListener('change', function() {
            const selectedType = this.value;
            
            // Sembunyikan semua input filter
            filterDay.style.display = 'none';
            filterMonth.style.display = 'none';
            filterYear.style.display = 'none';
            
            // Tampilkan input sesuai type dan set default value
            let filterValue = '';
            if (selectedType === 'day') {
                filterDay.style.display = 'block';
                filterDay.value = new Date().toISOString().split('T')[0]; // Set default ke hari ini
                filterValue = filterDay.value;
            } else if (selectedType === 'month') {
                filterMonth.style.display = 'block';
                filterMonth.value = new Date().toISOString().slice(0, 7); // Set default ke bulan ini
                filterValue = filterMonth.value;
            } else if (selectedType === 'year') {
                filterYear.style.display = 'block';
                filterYear.value = 2025; // Set default ke tahun 2025
                filterValue = filterYear.value.toString();
            }
            
            // Load data dengan filter baru
            if (selectedType && window.currentUserId && filterValue) {
                loadMoodRecordsWithFilter(window.currentUserId, selectedType, filterValue);
            } else if (!selectedType && window.currentUserId) {
                // Load semua data jika filter direset
                loadMoodRecordsWithFilter(window.currentUserId, null, null);
            }
        });

        // Event listener untuk perubahan nilai filter
        filterDay.addEventListener('change', function() {
            if (this.value && window.currentUserId) {
                loadMoodRecordsWithFilter(window.currentUserId, 'day', this.value);
            }
        });

        filterMonth.addEventListener('change', function() {
            if (this.value && window.currentUserId) {
                loadMoodRecordsWithFilter(window.currentUserId, 'month', this.value);
            }
        });

        filterYear.addEventListener('change', function() {
            if (this.value && window.currentUserId) {
                loadMoodRecordsWithFilter(window.currentUserId, 'year', this.value.toString());
            }
        });
    }

    // Fungsi untuk menyimpan respons admin
    function saveAdminResponse(recordId) {
        const responseTextarea = document.getElementById(`admin-response-${recordId}`);
        const responseText = responseTextarea.value.trim();
        const submitButton = responseTextarea.nextElementSibling;

        if (!responseText) {
            alert('Respons tidak boleh kosong');
            return;
        }

        // Disable button saat submit
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...';

        fetch(`/admin/mood-record/${recordId}/response`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                admin_response: responseText
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload mood records untuk menampilkan respons yang baru
                if (window.currentUserId) {
                    // Ambil filter yang sedang aktif
                    const filterType = document.getElementById('filter-type').value;
                    const filterDay = document.getElementById('filter-day');
                    const filterMonth = document.getElementById('filter-month');
                    const filterYear = document.getElementById('filter-year');
                    
                    let filterValue = null;
                    if (filterType === 'day' && filterDay.value) {
                        filterValue = filterDay.value;
                    } else if (filterType === 'month' && filterMonth.value) {
                        filterValue = filterMonth.value;
                    } else if (filterType === 'year' && filterYear.value) {
                        filterValue = filterYear.value.toString();
                    }
                    
                    loadMoodRecordsWithFilter(window.currentUserId, filterType || null, filterValue);
                }
                // Tampilkan notifikasi sukses
                alert('Respons berhasil disimpan');
            } else {
                alert('Gagal menyimpan respons: ' + (data.message || 'Silakan coba lagi'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menyimpan respons');
        })
        .finally(() => {
            // Enable button kembali
            submitButton.disabled = false;
            // Button text akan diupdate setelah reload mood records
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