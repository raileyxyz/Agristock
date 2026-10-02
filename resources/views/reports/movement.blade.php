<x-app-layout>
    <div x-data="{
        init() {
            this.renderGroupedBarChart();
        },
        renderGroupedBarChart() {
            new Chart(this.$refs.movementChart, {
                type: 'bar',
                data: {
                    labels: @js($productData['labels']),
                    datasets: [
                        {
                            label: 'Received',
                            data: @js($productData['received']),
                            backgroundColor: '#16a34a',
                            borderRadius: 4,
                            maxBarThickness: 24,
                        },
                        {
                            label: 'Consumed',
                            data: @js($productData['consumed']),
                            backgroundColor: '#f59e0b',
                            borderRadius: 4,
                            maxBarThickness: 24,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            align: window.innerWidth < 640 ? 'center' : 'end',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'circle',
                                boxWidth: 10,
                                font: { size: 10, weight: '500' },
                                color: '#475569'
                            }
                        },
                        tooltip: {
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                title: (items) => {
                                    return @js($productData['labels'])[items[0].dataIndex];
                                }
                            }
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
                            ticks: {
                                color: '#475569',
                                font: { size: 10, weight: '500' },
                                callback: function(value) {
                                    let label = this.getLabelForValue(value);
                                    const maxLength = window.innerWidth < 640 ? 8 : 14;
                                    return label.length > maxLength ? label.substr(0, maxLength) + '…' : label;
                                }
                            }
                        }
                    }
                }
            });
        }
    }" class="space-y-5 sm:space-y-6">

        <!-- Header Section with Responsive Range Selectors -->
        <div class="pb-4 sm:pb-5 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800 tracking-tight">Movement Report</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5 sm:mt-1">All stock movements and inventory flows to date</p>
            </div>

            <!-- Range Selector Control -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2" x-data="{ showCustom: {{ $range === 'custom' ? 'true' : 'false' }} }">
                <!-- Preset Options Pills -->
                <div class="inline-flex items-center rounded-xl border border-gray-200 bg-white p-1 shadow-sm overflow-x-auto">
                    <a href="{{ route('reports.movement', ['range' => '7d']) }}"
                       class="px-3 py-1.5 text-xs font-medium rounded-lg whitespace-nowrap transition-colors flex-1 text-center {{ $range === '7d' ? 'bg-green-600 text-white' : 'text-gray-500 hover:text-gray-800' }}">
                        7 Days
                    </a>
                    <a href="{{ route('reports.movement', ['range' => '30d']) }}"
                       class="px-3 py-1.5 text-xs font-medium rounded-lg whitespace-nowrap transition-colors flex-1 text-center {{ $range === '30d' ? 'bg-green-600 text-white' : 'text-gray-500 hover:text-gray-800' }}">
                        30 Days
                    </a>
                    <a href="{{ route('reports.movement', ['range' => '1y']) }}"
                       class="px-3 py-1.5 text-xs font-medium rounded-lg whitespace-nowrap transition-colors flex-1 text-center {{ $range === '1y' ? 'bg-green-600 text-white' : 'text-gray-500 hover:text-gray-800' }}">
                        1 Year
                    </a>
                    <a href="{{ route('reports.movement', ['range' => 'all']) }}"
                       class="px-3 py-1.5 text-xs font-medium rounded-lg whitespace-nowrap transition-colors flex-1 text-center {{ $range === 'all' ? 'bg-green-600 text-white' : 'text-gray-500 hover:text-gray-800' }}">
                        All Time
                    </a>
                    <button type="button" @click="showCustom = !showCustom"
                            class="px-3 py-1.5 text-xs font-medium rounded-lg whitespace-nowrap transition-colors flex-1 text-center {{ $range === 'custom' ? 'bg-green-600 text-white' : 'text-gray-500 hover:text-gray-800' }}">
                        Custom
                    </button>
                </div>

                <!-- Custom Date Range Form -->
                <form method="GET" action="{{ route('reports.movement') }}" x-show="showCustom" x-cloak class="flex items-center gap-2 bg-white p-1 border border-gray-200 rounded-xl shadow-sm">
                    <input type="hidden" name="range" value="custom">
                    <input type="date" name="start_date" value="{{ $customStart }}" required
                           class="w-full sm:w-auto border border-gray-200 rounded-lg px-2.5 py-1 text-xs focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 text-gray-700">
                    <span class="text-xs text-gray-400 font-medium">to</span>
                    <input type="date" name="end_date" value="{{ $customEnd }}" required
                           class="w-full sm:w-auto border border-gray-200 rounded-lg px-2.5 py-1 text-xs focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 text-gray-700">
                    <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-lg text-xs font-medium transition-colors shrink-0">
                        Apply
                    </button>
                </form>
            </div>
        </div>

        <!-- Metric Summary Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5 sm:gap-4">
            <!-- Total Received -->
            <div class="bg-white border border-gray-200/80 rounded-xl p-4 sm:p-5 shadow-sm hover:shadow-md transition-all flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-green-600">Total Received</span>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900 mt-1 sm:mt-1.5">{{ number_format($summary['total_received'], 0) }}</p>
                    <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5">Incoming stock movements</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-green-50 text-green-600 flex items-center justify-center shrink-0">
                    <i data-lucide="arrow-down-left" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </div>
            </div>

            <!-- Total Consumed -->
            <div class="bg-white border border-gray-200/80 rounded-xl p-4 sm:p-5 shadow-sm hover:shadow-md transition-all flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-amber-600">Total Consumed</span>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900 mt-1 sm:mt-1.5">{{ number_format($summary['total_consumed'], 0) }}</p>
                    <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5">Outgoing stock movements</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <i data-lucide="arrow-up-right" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </div>
            </div>

            <!-- Adjustments -->
            <div class="bg-white border border-gray-200/80 rounded-xl p-4 sm:p-5 shadow-sm hover:shadow-md transition-all flex items-center justify-between sm:col-span-2 lg:col-span-1">
                <div>
                    <span class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-gray-500">Adjustments</span>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900 mt-1 sm:mt-1.5">{{ number_format($summary['adjustments_count']) }}</p>
                    <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5">Manual inventory corrections</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gray-100 text-gray-600 flex items-center justify-center shrink-0">
                    <i data-lucide="sliders" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </div>
            </div>
        </div>

        <!-- Main Chart Section -->
        <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm sm:text-base font-semibold text-gray-800">Stock In vs Out by Product</h2>
            </div>
            <div class="h-72 sm:h-80 relative w-full overflow-hidden">
                <canvas x-ref="movementChart"></canvas>
            </div>
        </div>

    </div>
</x-app-layout>
