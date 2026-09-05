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
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } }
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
                        }]
                    },
                    options: {
                        plugins: { legend: { position: 'right' } },
                        cutout: '65%'
                    }
                });
            }
        }" class="max-w-6xl">

        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-50 text-green-600 flex items-center justify-center shrink-0">
                    <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Stock Report</h1>
                    <p class="text-gray-400 text-sm">Current inventory snapshot — {{ now()->format('M d, Y') }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 border border-blue-100 rounded-xl p-5 text-center">
                <p class="text-3xl font-bold text-blue-700">{{ $summary['total_skus'] }}</p>
                <p class="text-sm text-blue-600 mt-1">Total SKUs</p>
            </div>
            <div class="bg-green-50 border border-green-100 rounded-xl p-5 text-center">
                <p class="text-3xl font-bold text-green-700">{{ $summary['in_stock'] }}</p>
                <p class="text-sm text-green-600 mt-1">In Stock</p>
            </div>
            <div class="bg-amber-50 border border-amber-100 rounded-xl p-5 text-center">
                <p class="text-3xl font-bold text-amber-700">{{ $summary['low_critical'] }}</p>
                <p class="text-sm text-amber-600 mt-1">Low / Critical</p>
            </div>
            <div class="bg-red-50 border border-red-100 rounded-xl p-5 text-center">
                <p class="text-3xl font-bold text-red-700">{{ $summary['out_of_stock'] }}</p>
                <p class="text-sm text-red-600 mt-1">Out of Stock</p>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-6">
            <div class="bg-white border border-gray-200 rounded-xl p-6">
                <h2 class="text-base font-semibold text-gray-800 mb-4">Stock Quantity by Category</h2>
                <canvas x-ref="barChart"></canvas>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-6">
                <h2 class="text-base font-semibold text-gray-800 mb-4">Category Distribution</h2>
                <canvas x-ref="donutChart"></canvas>
            </div>
        </div>

    </div>
</x-app-layout>
