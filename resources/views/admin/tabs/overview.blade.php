<turbo-frame id="dashboard_content">
<div class="chart-container">
    <h3 class="section-title">Statistik Mood per Divisi</h3>
    <canvas id="divisionChart" width="400" height="200"></canvas>
</div>

<script>
    // Inisialisasi variabel global hanya sekali
    if (typeof window.divisionChartInstance === 'undefined') {
        window.divisionChartInstance = null;
    }
    if (typeof window.divisionChartLoading === 'undefined') {
        window.divisionChartLoading = false;
    }
    if (typeof window.divisionChartInitialized === 'undefined') {
        window.divisionChartInitialized = false;
    }

    // Fungsi untuk memuat chart divisi (hanya didefinisikan sekali)
    if (typeof window.loadDivisionChart === 'undefined') {
        window.loadDivisionChart = function() {
            const divisionChartCanvas = document.getElementById('divisionChart');
            
            if (!divisionChartCanvas) {
                return;
            }

            // Cek apakah chart sudah ada dan masih valid
            const existingChart = Chart.getChart(divisionChartCanvas);
            if (existingChart && window.divisionChartInstance === existingChart) {
                // Chart sudah ada dan masih valid, tidak perlu reload
                return;
            }

            // Cegah double loading
            if (window.divisionChartLoading) {
                return;
            }

            window.divisionChartLoading = true;

            // Hancurkan chart yang sudah ada jika ada
            if (window.divisionChartInstance) {
                try {
                    window.divisionChartInstance.destroy();
                } catch (e) {
                    console.warn('Error destroying chart:', e);
                }
                window.divisionChartInstance = null;
            }

            // Hancurkan chart dari canvas jika ada
            if (existingChart) {
                try {
                    existingChart.destroy();
                } catch (e) {
                    console.warn('Error destroying existing chart:', e);
                }
            }

            // Fetch data dan buat chart
            fetch('/admin/dashboard/chart-data')
            .then(response => response.json())
            .then(data => {
                // Pastikan canvas masih ada sebelum membuat chart baru
                const canvas = document.getElementById('divisionChart');
                if (!canvas) {
                    window.divisionChartLoading = false;
                    return;
                }

                // Double check - hancurkan lagi jika ada chart yang baru dibuat
                const existingChart = Chart.getChart(canvas);
                if (existingChart) {
                    try {
                        existingChart.destroy();
                    } catch (e) {
                        console.warn('Error destroying chart before create:', e);
                    }
                }

                const divisionCtx = canvas.getContext('2d');
                window.divisionChartInstance = new Chart(divisionCtx, {
                    type: 'bar',
                    data: data.divisionMood,
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Rata-rata Mood per Divisi'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });

                window.divisionChartInitialized = true;
                window.divisionChartLoading = false;
            })
            .catch(error => {
                console.error('Error loading division chart data:', error);
                window.divisionChartLoading = false;
            });
        };
    }

    // Setup event listener hanya sekali (gunakan flag untuk mencegah duplicate)
    if (typeof window.divisionChartListenersSetup === 'undefined') {
        window.divisionChartListenersSetup = false;
    }

    if (!window.divisionChartListenersSetup) {
        // Hancurkan chart hanya saat frame akan di-replace dengan konten lain (bukan overview)
        document.addEventListener('turbo:before-frame-render', function(event) {
            if (event.target.id === 'dashboard_content') {
                // Cek apakah frame baru berisi divisionChart
                const newContent = event.detail?.newBody;
                const hasDivisionChart = newContent && newContent.querySelector('#divisionChart') !== null;
                
                // Hanya hancurkan jika frame baru tidak memiliki divisionChart
                if (!hasDivisionChart) {
                    if (window.divisionChartInstance) {
                        try {
                            window.divisionChartInstance.destroy();
                        } catch (e) {
                            console.warn('Error destroying chart before render:', e);
                        }
                        window.divisionChartInstance = null;
                        window.divisionChartInitialized = false;
                    }

                    const divisionChartCanvas = document.getElementById('divisionChart');
                    if (divisionChartCanvas) {
                        const existingChart = Chart.getChart(divisionChartCanvas);
                        if (existingChart) {
                            try {
                                existingChart.destroy();
                            } catch (e) {
                                console.warn('Error destroying chart from canvas:', e);
                            }
                        }
                    }
                }
            }
        });

        // Inisialisasi chart setelah frame render - hanya jika belum diinisialisasi
        document.addEventListener('turbo:frame-render', function(event) {
            if (event.target.id === 'dashboard_content') {
                // Clear timeout jika ada untuk mencegah multiple calls
                if (window.divisionChartTimeout) {
                    clearTimeout(window.divisionChartTimeout);
                }

                // Tunggu DOM selesai diupdate, tapi hanya sekali
                window.divisionChartTimeout = setTimeout(() => {
                    const canvas = document.getElementById('divisionChart');
                    if (canvas) {
                        // Cek apakah chart sudah ada dan valid
                        const existingChart = Chart.getChart(canvas);
                        if (!existingChart || window.divisionChartInstance !== existingChart) {
                            window.loadDivisionChart();
                        }
                    }
                }, 100);
            }
        });

        window.divisionChartListenersSetup = true;
    }

    // Inisialisasi saat page load (untuk non-Turbo) - hanya sekali
    if (!window.divisionChartInitialized) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                const canvas = document.getElementById('divisionChart');
                if (canvas && !Chart.getChart(canvas)) {
                    window.loadDivisionChart();
                }
            });
        } else {
            // DOM sudah siap
            const canvas = document.getElementById('divisionChart');
            if (canvas && !Chart.getChart(canvas)) {
                window.loadDivisionChart();
            }
        }
    }
</script>
</turbo-frame>