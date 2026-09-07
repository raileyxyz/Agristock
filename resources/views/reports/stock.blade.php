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
                            padding: 10,
                            cornerRadius: 8
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9' },
                            ticks: { color: '#64748b', font: { size: 11 } }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: '#64748b', font: { size: 11 } }
                        }
                    }
                }
            });
        },
        renderDonutChart() {
            new Chart(this.$refs.donutChart, {
                type: 'doughnut',
                data: {
                    labels: @js($categoryData['labels']),
                    datasets: [{
                        data: @js($categoryData['values']),
                        backgroundColor: @js($categoryData['colors']),
                        borderWidth: 4,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                boxWidth: 8,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                padding: 14,
                                font: { size: 12, weight: '500' },
                                color: '#475569'
                            }
                        }
                    },
                    cutout: '60%'
                }
            });
        }
    }">

        <!-- Header with Controls -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Stock Report</h1>
                <p class="text-gray-500 text-sm mt-0.5">Current inventory snapshot — {{ now()->format('M d, Y') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <button class="inline-flex items-center gap-2 px-3 py-2 bg-white border text-green-600 text-sm font-medium rounded-lg shadow hover:bg-gray-100 transition-colors">
                    <i data-lucide="filter" class="w-4 h-4 text-green-700"></i> Filter
                </button>
                <button class="inline-flex items-center gap-2 px-3 py-2 bg-green-600 text-white text-sm font-medium rounded-lg shadow hover:bg-green-700 transition-colors">
                    <i data-lucide="download" class="w-4 h-4"></i> Export
                </button>
            </div>
        </div>

        <!-- Consistent Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Total SKUs -->
            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total SKUs</span>
                    <p class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($summary['total_skus']) }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center shrink-0">
                    <i data-lucide="boxes" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- In Stock -->
            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">In Stock</span>
                    <p class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($summary['in_stock']) }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- Low / Critical -->
            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Low / Critical</span>
                    <p class="text-2xl font-bold text-amber-600 mt-1">{{ number_format($summary['low_critical']) }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- Out of Stock -->
            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Out of Stock</span>
                    <p class="text-2xl font-bold text-rose-600 mt-1">{{ number_format($summary['out_of_stock']) }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                    <i data-lucide="x-circle" class="w-5 h-5"></i>
                </div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="grid lg:grid-cols-2 gap-6">
            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-semibold text-slate-800">Stock Quantity by Category</h2>
                </div>
                <div class="h-64 relative">
                    <canvas x-ref="barChart"></canvas>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-semibold text-slate-800">Category Distribution</h2>
                </div>
                <div class="h-64 relative">
                    <canvas x-ref="donutChart"></canvas>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
