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
document.addEventListener('turbo:click', function(event) {
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
document.addEventListener('turbo:frame-render', function(event) {
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
document.addEventListener('turbo:frame-load', function(event) {
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
document.addEventListener('turbo:load', function() {
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
function loadChart() {
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

    fetch('/admin/dashboard/chart-data')
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
                        text: 'Tren Mood Minggu Ini'
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
document.addEventListener('turbo:load', function() {
    // Tunggu sedikit untuk memastikan DOM sudah siap
    setTimeout(initializeMoodChart, 100);
});

// Pastikan chart tidak hilang saat frame render
// moodChart berada di dalam mood_chart_frame yang terpisah dari dashboard_content
// Jadi tidak perlu dihancurkan saat dashboard_content di-render
document.addEventListener('turbo:frame-render', function(event) {
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
document.addEventListener('turbo:frame-load', function(event) {
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
    modal.addEventListener('hide.bs.modal', function() {
        const activeElement = document.activeElement;
        if (modal.contains(activeElement)) {
            activeElement.blur();
        }
    });
    
    // Saat modal benar-benar tersembunyi, pastikan aria-hidden benar
    modal.addEventListener('hidden.bs.modal', function() {
        modal.setAttribute('aria-hidden', 'true');
        // Hapus focus dari elemen apapun yang masih focused
        if (document.activeElement && modal.contains(document.activeElement)) {
            document.activeElement.blur();
        }
    });
    
    // Saat modal ditampilkan, pastikan aria-hidden dihapus
    modal.addEventListener('show.bs.modal', function() {
        modal.removeAttribute('aria-hidden');
    });
    
    // Saat modal benar-benar ditampilkan
    modal.addEventListener('shown.bs.modal', function() {
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

