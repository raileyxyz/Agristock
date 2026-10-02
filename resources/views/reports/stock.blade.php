<x-app-layout>
    <div x-data="{
        init() {
            this.renderBarChart();
            this.renderDonutChart();
        },
        renderBarChart() {
            new Chart(this.$refs.barChart, {
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
                            backgroundColor: '#1e293b',
                            padding: 12,
                            cornerRadius: 8
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9', borderDash: [4, 4] },
                            ticks: { color: '#64748b', font: { size: 11 } }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: '#475569', font: { size: 11, weight: '500' } }
                        }
                    }
                }
            });
        },
        renderDonutChart() {
            const isMobile = window.innerWidth < 640;
            new Chart(this.$refs.donutChart, {
                type: 'doughnut',
                data: {
                    labels: @js($categoryData['labels']),
                    datasets: [{
                        data: @js($categoryData['values']),
                        backgroundColor: @js($categoryData['colors']),
                        borderWidth: 3,
                        borderColor: '#ffffff'
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
                                color: '#475569'
                            }
                        }
                    },
                    cutout: '65%'
                }
            });
        }
    }" class="space-y-5 sm:space-y-6">

        <!-- Header Section with Action Controls -->
        <div class="pb-4 sm:pb-5 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800 tracking-tight">Stock Report</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5 sm:mt-1">
                    Current inventory snapshot &mdash; <span class="font-medium text-gray-700">{{ now()->format('M d, Y') }}</span>
                </p>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-auto">
                <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-200 text-gray-700 text-xs sm:text-sm font-medium rounded-lg shadow-sm hover:bg-gray-50 transition-colors">
                    <i data-lucide="filter" class="w-4 h-4 text-gray-500"></i>
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
            <div class="bg-white border border-gray-200/80 rounded-xl p-4 sm:p-5 shadow-sm hover:shadow-md transition-all flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-blue-600">Total SKUs</span>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900 mt-1 sm:mt-1.5">{{ number_format($summary['total_skus']) }}</p>
                    <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5">Tracked products</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <i data-lucide="package" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </div>
            </div>

            <!-- In Stock -->
            <div class="bg-white border border-gray-200/80 rounded-xl p-4 sm:p-5 shadow-sm hover:shadow-md transition-all flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-emerald-600">In Stock</span>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900 mt-1 sm:mt-1.5">{{ number_format($summary['in_stock']) }}</p>
                    <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5">Healthy inventory levels</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <i data-lucide="check-circle-2" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </div>
            </div>

            <!-- Low / Critical -->
            <div class="bg-white border border-gray-200/80 rounded-xl p-4 sm:p-5 shadow-sm hover:shadow-md transition-all flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-amber-600">Low / Critical</span>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900 mt-1 sm:mt-1.5">{{ number_format($summary['low_critical']) }}</p>
                    <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5">Reorder point reached</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <i data-lucide="alert-triangle" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </div>
            </div>

            <!-- Out of Stock -->
            <div class="bg-white border border-gray-200/80 rounded-xl p-4 sm:p-5 shadow-sm hover:shadow-md transition-all flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-red-600">Out of Stock</span>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900 mt-1 sm:mt-1.5">{{ number_format($summary['out_of_stock']) }}</p>
                    <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5">Zero availability</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center shrink-0">
                    <i data-lucide="x-circle" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            <!-- Bar Chart Card -->
            <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm sm:text-base font-semibold text-gray-800">Stock Quantity by Category</h2>
                </div>
                <div class="h-64 sm:h-72 relative w-full overflow-hidden">
                    <canvas x-ref="barChart"></canvas>
                </div>
            </div>

            <!-- Donut Chart Card -->
            <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm sm:text-base font-semibold text-gray-800">Category Distribution</h2>
                </div>
                <div class="h-64 sm:h-72 relative w-full overflow-hidden">
                    <canvas x-ref="donutChart"></canvas>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
