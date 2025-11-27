<turbo-frame id="dashboard_content">
<div class="chart-container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="section-title mb-0">Statistik Mood per Divisi</h3>
        <div class="d-flex gap-2 align-items-center">
            <select id="division-chart-filter-type" class="form-select form-select-sm" style="width: auto;">
                <option value="">Semua Data</option>
                <option value="day">Per Hari</option>
                <option value="month">Per Bulan</option>
                <option value="year">Per Tahun</option>
            </select>
            <input type="date" id="division-chart-filter-day" class="form-control form-control-sm" style="width: auto; display: none;" max="{{ date('Y-m-d') }}">
            <input type="month" id="division-chart-filter-month" class="form-control form-control-sm" style="width: auto; display: none;" max="{{ date('Y-m') }}">
            <input type="number" id="division-chart-filter-year" class="form-control form-control-sm" style="width: auto; display: none;" min="2020" max="{{ date('Y') }}" value="{{ date('Y') }}" placeholder="Tahun">
        </div>
    </div>
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
        window.loadDivisionChart = function(filterType = null, filterValue = null) {
            const divisionChartCanvas = document.getElementById('divisionChart');
            
            if (!divisionChartCanvas) {
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
                }
                window.divisionChartInstance = null;
            }

            // Hancurkan chart dari canvas jika ada
            const existingChart = Chart.getChart(divisionChartCanvas);
            if (existingChart) {
                try {
                    existingChart.destroy();
                } catch (e) {
                }
            }

            // Build URL dengan filter parameters
            let url = '/admin/dashboard/chart-data?chart_type=division';
            if (filterType && filterValue) {
                url += `&filter_type=${filterType}&filter_value=${filterValue}`;
            }

            // Fetch data dan buat chart
            fetch(url)
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
                    }
                }

                // Generate dynamic title
                let chartTitle = 'Rata-rata Mood per Divisi';
                if (filterType === 'day' && filterValue) {
                    const date = new Date(filterValue);
                    chartTitle = `Mood per Divisi - ${date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}`;
                } else if (filterType === 'month' && filterValue) {
                    const [year, month] = filterValue.split('-');
                    const date = new Date(year, month - 1);
                    chartTitle = `Mood per Divisi - ${date.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })}`;
                } else if (filterType === 'year' && filterValue) {
                    chartTitle = `Mood per Divisi - Tahun ${filterValue}`;
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
                                text: chartTitle
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
                console.error('Error loading division chart:', error);
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
                        
                        // Setup filter event listeners setelah chart load
                        if (typeof window.setupDivisionChartFilters === 'function') {
                            window.setupDivisionChartFilters();
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

    // ======================================
    // DIVISION CHART FILTER LOGIC
    // ======================================

    // Debounce helper (defined in this scope if not already defined)
    if (typeof window.debounceDiv === 'undefined') {
        window.debounceDiv = function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        };
    }

    // Setup filter logic for division chart
    if (typeof window.setupDivisionChartFilters === 'undefined') {
        window.setupDivisionChartFilters = function() {
            const filterTypeSelect = document.getElementById('division-chart-filter-type');
            const filterDayInput = document.getElementById('division-chart-filter-day');
            const filterMonthInput = document.getElementById('division-chart-filter-month');
            const filterYearInput = document.getElementById('division-chart-filter-year');

            if (!filterTypeSelect) return;

            // Remove old listeners to prevent duplicates
            const newFilterTypeSelect = filterTypeSelect.cloneNode(true);
            filterTypeSelect.parentNode.replaceChild(newFilterTypeSelect, filterTypeSelect);

            // Show/hide appropriate filter input
            newFilterTypeSelect.addEventListener('change', function() {
                const filterType = this.value;
                const dayInput = document.getElementById('division-chart-filter-day');
                const monthInput = document.getElementById('division-chart-filter-month');
                const yearInput = document.getElementById('division-chart-filter-year');

                // Hide all inputs first
                dayInput.style.display = 'none';
                monthInput.style.display = 'none';
                yearInput.style.display = 'none';

                // Show appropriate input and reload chart
                if (filterType === 'day') {
                    dayInput.style.display = 'block';
                    if (!dayInput.value) {
                        dayInput.value = new Date().toISOString().split('T')[0];
                    }
                    window.loadDivisionChart(filterType, dayInput.value);
                } else if (filterType === 'month') {
                    monthInput.style.display = 'block';
                    if (!monthInput.value) {
                        const now = new Date();
                        monthInput.value = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;
                    }
                    window.loadDivisionChart(filterType, monthInput.value);
                } else if (filterType === 'year') {
                    yearInput.style.display = 'block';
                    window.loadDivisionChart(filterType, yearInput.value);
                } else {
                    // Default: semua data
                    window.loadDivisionChart();
                }
            });

            // Debounced reload for date inputs
            const debouncedLoadDivChart = window.debounceDiv((filterType, filterValue) => {
                window.loadDivisionChart(filterType, filterValue);
            }, 500);

            // Create new event listeners for inputs
            const newDayInput = filterDayInput.cloneNode(true);
            filterDayInput.parentNode.replaceChild(newDayInput, filterDayInput);
            newDayInput.addEventListener('change', function() {
                if (this.value) {
                    debouncedLoadDivChart('day', this.value);
                }
            });

            const newMonthInput = filterMonthInput.cloneNode(true);
            filterMonthInput.parentNode.replaceChild(newMonthInput, filterMonthInput);
            newMonthInput.addEventListener('change', function() {
                if (this.value) {
                    debouncedLoadDivChart('month', this.value);
                }
            });

            const newYearInput = filterYearInput.cloneNode(true);
            filterYearInput.parentNode.replaceChild(newYearInput, filterYearInput);
            newYearInput.addEventListener('input', function() {
                if (this.value && this.value.length === 4) {
                    debouncedLoadDivChart('year', this.value);
                }
            });
        };
    }

    // Initialize division chart filters when this tab loads
    setTimeout(() => {
        if (document.getElementById('division-chart-filter-type')) {
            window.setupDivisionChartFilters();
        }
    }, 100);
</script>
</turbo-frame>