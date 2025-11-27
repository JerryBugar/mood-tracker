// Gunakan window object untuk menghindari redeclaration error
if (typeof window.moodChartInstance === 'undefined') {
    window.moodChartInstance = null;
}

// Jangan hancurkan moodChart saat dashboard_content frame di-render
// Karena moodChart berada di luar frame dan tidak terpengaruh oleh perubahan tab
// Hanya hancurkan chart yang ada di dalam frame (divisionChart di overview tab)

// Simpan state tab aktif
let currentActiveTab = 'overview'; // Default tab

// Fungsi untuk update active tab - hanya satu tab yang aktif
function updateActiveTab(activeTabName = null) {
    const tabs = document.querySelectorAll('.nav-tab');

    // Jika activeTabName diberikan, update state
    if (activeTabName) {
        currentActiveTab = activeTabName;
    }

    // Hapus SEMUA active class terlebih dahulu - pastikan benar-benar dihapus
    tabs.forEach(tab => {
        tab.classList.remove('active');
        // Pastikan class benar-benar dihapus
        if (tab.classList.contains('active')) {
            tab.className = tab.className.replace(/\bactive\b/g, '').trim();
        }
    });

    // Tambahkan active class HANYA pada tab yang sesuai dengan currentActiveTab
    tabs.forEach(tab => {
        const tabName = tab.getAttribute('data-tab');
        if (tabName && tabName === currentActiveTab) {
            tab.classList.add('active');
        }
    });
}

// Flag untuk menandai bahwa ini adalah user click (bukan hover/prefetch)
let isUserClick = false;

// Update tab saat click - menggunakan Turbo click event
document.addEventListener('turbo:click', function (event) {
    const link = event.target.closest('a[data-turbo-frame="dashboard_content"]');
    if (link) {
        const tabName = link.getAttribute('data-tab');
        if (tabName) {
            // Tandai bahwa ini adalah user click
            isUserClick = true;
            // Update tab segera saat diklik
            updateActiveTab(tabName);
        }
    }
});

// Jangan update tab saat hover/prefetch - hanya saat click
// Event turbo:before-fetch-request dihapus karena bisa dipanggil saat hover

// Update tab saat frame render hanya jika ini dari user click
// Jangan update berulang kali untuk menghindari semua tab menjadi aktif
let frameRenderTimeout = null;
document.addEventListener('turbo:frame-render', function (event) {
    if (event.target && event.target.id === 'dashboard_content' && isUserClick) {
        // Clear timeout sebelumnya jika ada
        if (frameRenderTimeout) {
            clearTimeout(frameRenderTimeout);
        }
        // Pastikan tab aktif tetap sesuai dengan state yang disimpan
        frameRenderTimeout = setTimeout(() => {
            updateActiveTab(); // Gunakan currentActiveTab yang sudah disimpan
            isUserClick = false; // Reset flag
        }, 50);
    } else {
        isUserClick = false; // Reset jika bukan dari click
    }
});

// Update tab saat frame load - hanya jika dari user click
// Jangan update berulang kali
let frameLoadTimeout = null;
document.addEventListener('turbo:frame-load', function (event) {
    if (event.target && event.target.id === 'dashboard_content' && isUserClick) {
        // Clear timeout sebelumnya jika ada
        if (frameLoadTimeout) {
            clearTimeout(frameLoadTimeout);
        }
        frameLoadTimeout = setTimeout(() => {
            updateActiveTab(); // Gunakan currentActiveTab yang sudah disimpan
            isUserClick = false; // Reset flag
        }, 100);
    } else {
        isUserClick = false; // Reset jika bukan dari click
    }
});

// Fungsi untuk inisialisasi tab berdasarkan URL
let isInitialized = false;
function initializeActiveTab() {
    // Hanya inisialisasi sekali untuk menghindari semua tab menjadi aktif
    if (isInitialized) {
        return;
    }

    const currentPath = window.location.pathname;

    if (currentPath.includes('employees')) {
        currentActiveTab = 'employees';
    } else if (currentPath.includes('notifications')) {
        currentActiveTab = 'notifications';
    } else if (currentPath.includes('overview') || currentPath.endsWith('/dashboard') || currentPath.endsWith('/admin/dashboard')) {
        currentActiveTab = 'overview';
    }

    updateActiveTab();
    isInitialized = true;
}

// Update active tab berdasarkan URL saat page load (hanya sekali)
document.addEventListener('turbo:load', function () {
    // Reset flag saat page load baru
    isInitialized = false;
    initializeActiveTab();
});

// Inisialisasi saat DOM ready (hanya sekali)
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeActiveTab);
} else {
    // DOM sudah siap
    initializeActiveTab();
}

// Fungsi untuk memuat chart mood trend
function loadChart(filterType = null, filterValue = null) {
    const moodChartCanvas = document.getElementById('moodChart');

    if (!moodChartCanvas) {
        return;
    }

    // Hancurkan chart yang sudah ada jika ada
    if (window.moodChartInstance) {
        try {
            window.moodChartInstance.destroy();
        } catch (e) {
        }
        window.moodChartInstance = null;
    }

    // Cek juga dengan Chart.getChart untuk memastikan
    const existingChart = Chart.getChart(moodChartCanvas);
    if (existingChart) {
        try {
            existingChart.destroy();
        } catch (e) {
        }
    }

    // Build URL dengan filter parameters
    let url = '/admin/dashboard/chart-data?chart_type=mood';
    if (filterType && filterValue) {
        url += `&filter_type=${filterType}&filter_value=${filterValue}`;
    }

    fetch(url)
        .then(response => response.json())
        .then(data => {
            // Pastikan canvas masih ada sebelum membuat chart baru
            const canvas = document.getElementById('moodChart');
            if (!canvas) {
                return;
            }

            // Hancurkan lagi jika ada chart yang baru dibuat
            const existingChart = Chart.getChart(canvas);
            if (existingChart) {
                existingChart.destroy();
            }

            const moodCtx = canvas.getContext('2d');

            // Generate dynamic title based on filter
            let chartTitle = 'Tren Mood Minggu Ini';
            if (filterType === 'day' && filterValue) {
                const date = new Date(filterValue);
                chartTitle = `Tren Mood - ${date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}`;
            } else if (filterType === 'month' && filterValue) {
                const [year, month] = filterValue.split('-');
                const date = new Date(year, month - 1);
                chartTitle = `Tren Mood - ${date.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })}`;
            } else if (filterType === 'year' && filterValue) {
                chartTitle = `Tren Mood - Tahun ${filterValue}`;
            }

            window.moodChartInstance = new Chart(moodCtx, {
                type: 'line',
                data: {
                    labels: data.moodTrend.labels,
                    datasets: data.moodTrend.datasets
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: chartTitle
                        }
                    },
                    scales: {
                        y: {
                            stacked: true
                        }
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error loading mood chart:', error);
        });
}

// Initialize charts when page loads
function initializeMoodChart() {
    // Pastikan canvas ada sebelum memuat chart
    const moodChartCanvas = document.getElementById('moodChart');
    if (moodChartCanvas) {
        loadChart();
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeMoodChart);
} else {
    initializeMoodChart();
}

// Event listener for Turbo - reload chart setelah page load
document.addEventListener('turbo:load', function () {
    // Tunggu sedikit untuk memastikan DOM sudah siap
    setTimeout(initializeMoodChart, 100);
});

// Pastikan chart tidak hilang saat frame render
// moodChart berada di dalam mood_chart_frame yang terpisah dari dashboard_content
// Jadi tidak perlu dihancurkan saat dashboard_content di-render
document.addEventListener('turbo:frame-render', function (event) {
    // Jika frame yang di-render adalah dashboard_content, pastikan moodChart tetap ada
    if (event.target && event.target.id === 'dashboard_content') {
        // Cek apakah moodChart masih ada, jika tidak, reload
        setTimeout(() => {
            const moodChartCanvas = document.getElementById('moodChart');
            if (moodChartCanvas) {
                const existingChart = Chart.getChart(moodChartCanvas);
                // Jika chart hilang, reload
                if (!existingChart) {
                    loadChart();
                }
            }
        }, 200);
    }

    // Jika frame yang di-render adalah mood_chart_frame, reload chart
    if (event.target && event.target.id === 'mood_chart_frame') {
        setTimeout(() => {
            loadChart();
        }, 100);
    }
});

// Pastikan chart tidak hilang saat frame load
document.addEventListener('turbo:frame-load', function (event) {
    if (event.target && event.target.id === 'mood_chart_frame') {
        setTimeout(() => {
            loadChart();
        }, 100);
    }
});

// Perbaiki masalah aria-hidden pada modal
// Gunakan window object untuk menyimpan flag agar tidak ter-reset
if (typeof window.modalAriaHandlersSetup === 'undefined') {
    window.modalAriaHandlersSetup = false;
}

function setupModalAriaHandlers() {
    // Hanya setup sekali untuk menghindari duplikasi
    if (window.modalAriaHandlersSetup) {
        return;
    }

    const modal = document.getElementById('employeeDetailModal');
    if (!modal) {
        return;
    }

    // Saat modal akan disembunyikan, hapus focus dari elemen di dalamnya
    modal.addEventListener('hide.bs.modal', function () {
        const activeElement = document.activeElement;
        if (modal.contains(activeElement)) {
            activeElement.blur();
        }
    });

    // Saat modal benar-benar tersembunyi, pastikan aria-hidden benar
    modal.addEventListener('hidden.bs.modal', function () {
        modal.setAttribute('aria-hidden', 'true');
        // Hapus focus dari elemen apapun yang masih focused
        if (document.activeElement && modal.contains(document.activeElement)) {
            document.activeElement.blur();
        }
    });

    // Saat modal ditampilkan, pastikan aria-hidden dihapus
    modal.addEventListener('show.bs.modal', function () {
        modal.removeAttribute('aria-hidden');
    });

    // Saat modal benar-benar ditampilkan
    modal.addEventListener('shown.bs.modal', function () {
        modal.removeAttribute('aria-hidden');
    });

    window.modalAriaHandlersSetup = true;
}

// Setup saat DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupModalAriaHandlers);
} else {
    setupModalAriaHandlers();
}

// Setup ulang saat Turbo load (jika modal belum di-setup)
document.addEventListener('turbo:load', setupModalAriaHandlers);

// ======================================
// MOOD CHART FILTER LOGIC
// ======================================

// Debounce helper to prevent excessive requests
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Setup filter logic for mood chart
function setupMoodChartFilters() {
    const filterTypeSelect = document.getElementById('mood-chart-filter-type');
    const filterDayInput = document.getElementById('mood-chart-filter-day');
    const filterMonthInput = document.getElementById('mood-chart-filter-month');
    const filterYearInput = document.getElementById('mood-chart-filter-year');

    if (!filterTypeSelect) return;

    // Show/hide appropriate filter input
    filterTypeSelect.addEventListener('change', function () {
        const filterType = this.value;

        // Hide all inputs first
        filterDayInput.style.display = 'none';
        filterMonthInput.style.display = 'none';
        filterYearInput.style.display = 'none';

        // Show appropriate input and reload chart
        if (filterType === 'day') {
            filterDayInput.style.display = 'block';
            // Set default to today if empty
            if (!filterDayInput.value) {
                filterDayInput.value = new Date().toISOString().split('T')[0];
            }
            loadChart(filterType, filterDayInput.value);
        } else if (filterType === 'month') {
            filterMonthInput.style.display = 'block';
            // Set default to current month if empty
            if (!filterMonthInput.value) {
                const now = new Date();
                filterMonthInput.value = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;
            }
            loadChart(filterType, filterMonthInput.value);
        } else if (filterType === 'year') {
            filterYearInput.style.display = 'block';
            // Default value already set in HTML
            loadChart(filterType, filterYearInput.value);
        } else {
            // Default: minggu ini
            loadChart();
        }
    });

    // Debounced reload for date inputs
    const debouncedLoadMoodChart = debounce((filterType, filterValue) => {
        loadChart(filterType, filterValue);
    }, 500);

    filterDayInput.addEventListener('change', function () {
        if (this.value) {
            debouncedLoadMoodChart('day', this.value);
        }
    });

    filterMonthInput.addEventListener('change', function () {
        if (this.value) {
            debouncedLoadMoodChart('month', this.value);
        }
    });

    filterYearInput.addEventListener('input', function () {
        if (this.value && this.value.length === 4) {
            debouncedLoadMoodChart('year', this.value);
        }
    });
}

// Initialize mood chart filters when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupMoodChartFilters);
} else {
    setupMoodChartFilters();
}

// Re-initialize after Turbo load
document.addEventListener('turbo:load', setupMoodChartFilters);

