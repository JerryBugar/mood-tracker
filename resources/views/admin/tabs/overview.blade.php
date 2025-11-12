<turbo-frame id="dashboard_content">
<div class="chart-container">
    <h3 class="section-title">Statistik Mood per Divisi</h3>
    <canvas id="divisionChart" width="400" height="200"></canvas>
</div>

<script>
    // Gunakan window object untuk menghindari redeclaration error
    if (typeof window.divisionChartInstance === 'undefined') {
        window.divisionChartInstance = null;
    }

    // Fungsi untuk memuat chart divisi (gunakan window untuk menghindari redeclaration)
    if (typeof window.loadDivisionChart === 'undefined') {
        window.loadDivisionChart = function() {
        const divisionChartCanvas = document.getElementById('divisionChart');
        
        if (!divisionChartCanvas) {
            return;
        }

        // Hancurkan chart yang sudah ada jika ada
        if (window.divisionChartInstance) {
            try {
                window.divisionChartInstance.destroy();
            } catch (e) {
                console.warn('Error destroying chart:', e);
            }
            window.divisionChartInstance = null;
        }

        // Cek juga dengan Chart.getChart untuk memastikan
        const existingChart = Chart.getChart(divisionChartCanvas);
        if (existingChart) {
            try {
                existingChart.destroy();
            } catch (e) {
                console.warn('Error destroying existing chart:', e);
            }
        }

        // Tunggu sedikit untuk memastikan canvas sudah siap
        setTimeout(() => {
            fetch('/admin/dashboard/chart-data')
            .then(response => response.json())
            .then(data => {
                // Pastikan canvas masih ada sebelum membuat chart baru
                const canvas = document.getElementById('divisionChart');
                if (!canvas) {
                    return;
                }

                // Hancurkan lagi jika ada chart yang baru dibuat
                const existingChart = Chart.getChart(canvas);
                if (existingChart) {
                    existingChart.destroy();
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
            })
            .catch(error => {
                console.error('Error loading division chart data:', error);
            });
        }, 50);
        };
    }

    // Hancurkan chart sebelum Turbo frame render
    document.addEventListener('turbo:before-frame-render', function(event) {
        if (event.target.id === 'dashboard_content') {
            // Hancurkan chart instance global
            if (window.divisionChartInstance) {
                try {
                    window.divisionChartInstance.destroy();
                } catch (e) {
                    console.warn('Error destroying chart before render:', e);
                }
                window.divisionChartInstance = null;
            }

            // Hancurkan chart dari canvas jika ada
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
    });

    // Inisialisasi chart setelah frame render
    document.addEventListener('turbo:frame-render', function(event) {
        if (event.target.id === 'dashboard_content') {
            // Tunggu DOM selesai diupdate
            setTimeout(() => {
                if (document.getElementById('divisionChart')) {
                    window.loadDivisionChart();
                }
            }, 150);
        }
    });

    // Inisialisasi saat page load (untuk non-Turbo)
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('divisionChart')) {
                window.loadDivisionChart();
            }
        });
    } else {
        // DOM sudah siap
        if (document.getElementById('divisionChart')) {
            window.loadDivisionChart();
        }
    }
</script>
</turbo-frame>