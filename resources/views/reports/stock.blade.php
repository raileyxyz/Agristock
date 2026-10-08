<x-app-layout>
    <div x-data="{
        isDark: document.documentElement.classList.contains('dark'),
        barChart: null,
        donutChart: null,
        init() {
            this.renderBarChart();
            this.renderDonutChart();
        },
        renderBarChart() {
            const gridColor = this.isDark ? '#1F2B23' : '#f1f5f9';
            const tickY = this.isDark ? '#9CA3AF' : '#64748b';
            const tickX = this.isDark ? '#D1D5DB' : '#475569';

            this.barChart = new Chart(this.$refs.barChart, {
                type: 'bar',
                data: {
                    labels: @js($categoryData['labels']),
                    datasets: [{
                        data: @js($categoryData['values']),
                        backgroundColor: @js($categoryData['colors']),
                        borderRadius: 4,
                        maxBarThickness: 32
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: this.isDark ? '#1C2621' : '#1e293b',
                            borderColor: '#27332C',
                            borderWidth: this.isDark ? 1 : 0,
                            padding: 12,
                            cornerRadius: 8
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: gridColor, borderDash: [4, 4] },
                            ticks: { color: tickY, font: { size: 11 } }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: tickX, font: { size: 11, weight: '500' } }
                        }
                    }
                }
            });
        },
        renderDonutChart() {
            const isMobile = window.innerWidth < 640;
            const legendColor = this.isDark ? '#D1D5DB' : '#475569';
            const borderColor = this.isDark ? '#111713' : '#ffffff';

            this.donutChart = new Chart(this.$refs.donutChart, {
                type: 'doughnut',
                data: {
                    labels: @js($categoryData['labels']),
                    datasets: [{
                        data: @js($categoryData['values']),
                        backgroundColor: @js($categoryData['colors']),
                        borderWidth: 3,
                        borderColor: borderColor
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: isMobile ? 'bottom' : 'right',
                            labels: {
                                boxWidth: 10,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                padding: 14,
                                font: { size: 11, weight: '500' },
                                color: legendColor
                            }
                        },
                        tooltip: {
                            backgroundColor: this.isDark ? '#1C2621' : '#1e293b',
                            borderColor: '#27332C',
                            borderWidth: this.isDark ? 1 : 0
                        }
                    },
                    cutout: '65%'
                }
            });
        },
        handleThemeChange() {
            this.isDark = document.documentElement.classList.contains('dark');
            if (this.barChart && this.donutChart) {
                this.barChart.destroy();
                this.donutChart.destroy();
                this.renderBarChart();
                this.renderDonutChart();
            }
        }
    }"
    @theme-changed.window="handleThemeChange()"
    class="space-y-5 sm:space-y-6">

        <!-- Header Section with Action Controls -->
        <div class="pb-4 sm:pb-5 border-b border-gray-200 dark:border-[#27332C] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 transition-colors duration-200 ease-in-out">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-gray-100 tracking-tight">Stock Report</h1>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5 sm:mt-1">
                    Current inventory snapshot &mdash; <span class="font-medium text-gray-700 dark:text-gray-200">{{ now()->format('M d, Y') }}</span>
                </p>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-auto">
                <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white dark:bg-[#111713] border border-gray-200 dark:border-[#27332C] text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-medium rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-[#1C2621] transition-colors">
                    <i data-lucide="filter" class="w-4 h-4 text-gray-500 dark:text-gray-400"></i>
                    <span>Filter</span>
                </button>
                <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-600 text-white text-xs sm:text-sm font-medium rounded-lg shadow-sm hover:bg-green-700 transition-colors">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    <span>Export</span>
                </button>
            </div>
        </div>

        <!-- Metric Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
            <!-- Total SKUs -->
            <div class="bg-white dark:bg-[#111713] border border-gray-200/80 dark:border-[#27332C] rounded-xl p-4 sm:p-5 shadow-sm hover:shadow-md transition-all flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400">Total SKUs</span>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1 sm:mt-1.5">{{ number_format($summary['total_skus']) }}</p>
                    <p class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 mt-0.5">Tracked products</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                    <i data-lucide="package" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </div>
            </div>

            <!-- In Stock -->
            <div class="bg-white dark:bg-[#111713] border border-gray-200/80 dark:border-[#27332C] rounded-xl p-4 sm:p-5 shadow-sm hover:shadow-md transition-all flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-green-600 dark:text-green-400">In Stock</span>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1 sm:mt-1.5">{{ number_format($summary['in_stock']) }}</p>
                    <p class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 mt-0.5">Healthy inventory levels</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-green-600 dark:text-green-400 flex items-center justify-center shrink-0">
                    <i data-lucide="check-circle-2" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </div>
            </div>

            <!-- Low / Critical -->
            <div class="bg-white dark:bg-[#111713] border border-gray-200/80 dark:border-[#27332C] rounded-xl p-4 sm:p-5 shadow-sm hover:shadow-md transition-all flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-amber-600 dark:text-amber-400">Low / Critical</span>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1 sm:mt-1.5">{{ number_format($summary['low_critical']) }}</p>
                    <p class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 mt-0.5">Reorder point reached</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <i data-lucide="alert-triangle" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </div>
            </div>

            <!-- Out of Stock -->
            <div class="bg-white dark:bg-[#111713] border border-gray-200/80 dark:border-[#27332C] rounded-xl p-4 sm:p-5 shadow-sm hover:shadow-md transition-all flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-red-600 dark:text-red-400">Out of Stock</span>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1 sm:mt-1.5">{{ number_format($summary['out_of_stock']) }}</p>
                    <p class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 mt-0.5">Zero availability</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0">
                    <i data-lucide="x-circle" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            <!-- Bar Chart Card -->
            <div class="bg-white dark:bg-[#111713] border border-gray-200 dark:border-[#27332C] rounded-xl p-4 sm:p-6 shadow-sm transition-colors duration-200 ease-in-out">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm sm:text-base font-semibold text-gray-800 dark:text-gray-200">Stock Quantity by Category</h2>
                </div>
                <div class="h-64 sm:h-72 relative w-full overflow-hidden">
                    <canvas x-ref="barChart"></canvas>
                </div>
            </div>

            <!-- Donut Chart Card -->
            <div class="bg-white dark:bg-[#111713] border border-gray-200 dark:border-[#27332C] rounded-xl p-4 sm:p-6 shadow-sm transition-colors duration-200 ease-in-out">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm sm:text-base font-semibold text-gray-800 dark:text-gray-200">Category Distribution</h2>
                </div>
                <div class="h-64 sm:h-72 relative w-full overflow-hidden">
                    <canvas x-ref="donutChart"></canvas>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
