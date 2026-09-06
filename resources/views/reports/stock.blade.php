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
                            backgroundColor: ['#16a34a', '#0891b2', '#dc2626', '#d97706', '#7c3aed', '#be185d'],
                            borderRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: '#f3f4f6' } },
                            x: { grid: { display: false } }
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
                            backgroundColor: ['#16a34a', '#0891b2', '#dc2626', '#d97706', '#7c3aed', '#be185d'],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { boxWidth: 12, padding: 15, font: { size: 11 } }
                            }
                        },
                        cutout: '50%'
                    }
                });
            }
        }">

        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Stock Report</h1>
                <p class="text-gray-400 text-sm mt-0.5">Current inventory snapshot — {{ now()->format('M d, Y') }}</p>
            </div>
        </div>

        <!-- Modern Summary Cards with Icons -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Total SKUs -->
            <div class="bg-white border border-gray-200/80 rounded-xl p-5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-400">Total SKUs</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $summary['total_skus'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <i data-lucide="boxes" class="w-6 h-6"></i>
                </div>
            </div>

            <!-- In Stock -->
            <div class="bg-white border border-gray-200/80 rounded-xl p-5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-400">In Stock</p>
                    <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $summary['in_stock'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <i data-lucide="check-circle-2" class="w-6 h-6"></i>
                </div>
            </div>

            <!-- Low / Critical -->
            <div class="bg-white border border-gray-200/80 rounded-xl p-5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-400">Low / Critical</p>
                    <p class="text-2xl font-bold text-amber-600 mt-1">{{ $summary['low_critical'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                </div>
            </div>

            <!-- Out of Stock -->
            <div class="bg-white border border-gray-200/80 rounded-xl p-5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-400">Out of Stock</p>
                    <p class="text-2xl font-bold text-rose-600 mt-1">{{ $summary['out_of_stock'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                    <i data-lucide="x-circle" class="w-6 h-6"></i>
                </div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="grid lg:grid-cols-2 gap-6">
            <!-- Bar Chart -->
            <div class="bg-white border border-gray-200/80 rounded-xl p-6 shadow-sm">
                <h2 class="text-base font-semibold text-gray-800 mb-4">Stock Quantity by Category</h2>
                <div class="h-64 relative">
                    <canvas x-ref="barChart"></canvas>
                </div>
            </div>

            <!-- Controlled-size Donut Chart -->
            <div class="bg-white border border-gray-200/80 rounded-xl p-6 shadow-sm">
                <h2 class="text-base font-semibold text-gray-800 mb-4">Category Distribution</h2>
                <div class="h-64 relative flex items-center justify-center">
                    <canvas x-ref="donutChart"></canvas>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
