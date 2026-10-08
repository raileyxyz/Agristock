<x-app-layout>
    <div x-data="{
        isDark: document.documentElement.classList.contains('dark'),
        movementChart: null,
        init() {
            this.renderGroupedBarChart();
        },
        renderGroupedBarChart() {
            const gridColor = this.isDark ? '#1F2B23' : '#f1f5f9';
            const tickY = this.isDark ? '#9CA3AF' : '#64748b';
            const tickX = this.isDark ? '#D1D5DB' : '#475569';
            const legendColor = this.isDark ? '#D1D5DB' : '#475569';

            this.movementChart = new Chart(this.$refs.movementChart, {
                type: 'bar',
                data: {
                    labels: @js($productData['labels']),
                    datasets: [
                        {
                            label: 'Received',
                            data: @js($productData['received']),
                            backgroundColor: this.isDark ? '#22c55e' : '#16a34a',
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
                                color: legendColor
                            }
                        },
                        tooltip: {
                            backgroundColor: this.isDark ? '#1C2621' : '#1e293b',
                            borderColor: '#27332C',
                            borderWidth: this.isDark ? 1 : 0,
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
                            grid: { color: gridColor, borderDash: [4, 4] },
                            ticks: { color: tickY, font: { size: 11 } }
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                color: tickX,
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
        },
        handleThemeChange() {
            this.isDark = document.documentElement.classList.contains('dark');
            if (this.movementChart) {
                this.movementChart.destroy();
                this.renderGroupedBarChart();
            }
        }
    }"
    @theme-changed.window="handleThemeChange()"
    class="space-y-5 sm:space-y-6">

        <!-- Header Section with Responsive Range Selectors -->
        <div class="pb-4 sm:pb-5 border-b border-gray-200 dark:border-[#27332C] flex flex-col md:flex-row md:items-center md:justify-between gap-4 transition-colors duration-200 ease-in-out">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-gray-100 tracking-tight">Movement Report</h1>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5 sm:mt-1">All stock movements and inventory flows to date</p>
            </div>

            <!-- Range Selector Control -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2" x-data="{ showCustom: {{ $range === 'custom' ? 'true' : 'false' }} }">
                <!-- Preset Options Pills -->
                <div class="inline-flex items-center rounded-xl border border-gray-200 dark:border-[#27332C] bg-white dark:bg-[#111713] p-1 shadow-sm overflow-x-auto">
                    <a href="{{ route('reports.movement', ['range' => '7d']) }}"
                       class="px-3 py-1.5 text-xs font-medium rounded-lg whitespace-nowrap transition-colors flex-1 text-center {{ $range === '7d' ? 'bg-green-600 text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-100' }}">
                        7 Days
                    </a>
                    <a href="{{ route('reports.movement', ['range' => '30d']) }}"
                       class="px-3 py-1.5 text-xs font-medium rounded-lg whitespace-nowrap transition-colors flex-1 text-center {{ $range === '30d' ? 'bg-green-600 text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-100' }}">
                        30 Days
                    </a>
                    <a href="{{ route('reports.movement', ['range' => '1y']) }}"
                       class="px-3 py-1.5 text-xs font-medium rounded-lg whitespace-nowrap transition-colors flex-1 text-center {{ $range === '1y' ? 'bg-green-600 text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-100' }}">
                        1 Year
                    </a>
                    <a href="{{ route('reports.movement', ['range' => 'all']) }}"
                       class="px-3 py-1.5 text-xs font-medium rounded-lg whitespace-nowrap transition-colors flex-1 text-center {{ $range === 'all' ? 'bg-green-600 text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-100' }}">
                        All Time
                    </a>
                    <button type="button" @click="showCustom = !showCustom"
                            class="px-3 py-1.5 text-xs font-medium rounded-lg whitespace-nowrap transition-colors flex-1 text-center {{ $range === 'custom' ? 'bg-green-600 text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-100' }}">
                        Custom
                    </button>
                </div>

                <!-- Custom Date Range Form -->
                <form method="GET" action="{{ route('reports.movement') }}" x-show="showCustom" x-cloak class="flex items-center gap-2 bg-white dark:bg-[#111713] p-1 border border-gray-200 dark:border-[#27332C] rounded-xl shadow-sm">
                    <input type="hidden" name="range" value="custom">
                    <input type="date" name="start_date" value="{{ $customStart }}" required
                           class="w-full sm:w-auto border border-gray-200 dark:border-[#27332C] dark:bg-[#0B0F0D] dark:[color-scheme:dark] rounded-lg px-2.5 py-1 text-xs focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 dark:focus:border-green-400 text-gray-700 dark:text-gray-100">
                    <span class="text-xs text-gray-400 dark:text-gray-500 font-medium">to</span>
                    <input type="date" name="end_date" value="{{ $customEnd }}" required
                           class="w-full sm:w-auto border border-gray-200 dark:border-[#27332C] dark:bg-[#0B0F0D] dark:[color-scheme:dark] rounded-lg px-2.5 py-1 text-xs focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 dark:focus:border-green-400 text-gray-700 dark:text-gray-100">
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
            <div class="bg-white dark:bg-[#111713] border border-gray-200/80 dark:border-[#27332C] rounded-xl p-4 sm:p-5 shadow-sm hover:shadow-md transition-all flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-green-600 dark:text-green-400">Total Received</span>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1 sm:mt-1.5">{{ number_format($summary['total_received'], 0) }}</p>
                    <p class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 mt-0.5">Incoming stock movements</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-green-50 dark:bg-green-950/40 text-green-600 dark:text-green-400 flex items-center justify-center shrink-0">
                    <i data-lucide="arrow-down-left" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </div>
            </div>

            <!-- Total Consumed -->
            <div class="bg-white dark:bg-[#111713] border border-gray-200/80 dark:border-[#27332C] rounded-xl p-4 sm:p-5 shadow-sm hover:shadow-md transition-all flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-amber-600 dark:text-amber-400">Total Consumed</span>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1 sm:mt-1.5">{{ number_format($summary['total_consumed'], 0) }}</p>
                    <p class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 mt-0.5">Outgoing stock movements</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <i data-lucide="arrow-up-right" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </div>
            </div>

            <!-- Adjustments -->
            <div class="bg-white dark:bg-[#111713] border border-gray-200/80 dark:border-[#27332C] rounded-xl p-4 sm:p-5 shadow-sm hover:shadow-md transition-all flex items-center justify-between sm:col-span-2 lg:col-span-1">
                <div>
                    <span class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Adjustments</span>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1 sm:mt-1.5">{{ number_format($summary['adjustments_count']) }}</p>
                    <p class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 mt-0.5">Manual inventory corrections</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gray-100 dark:bg-[#1C2621] text-gray-600 dark:text-gray-400 flex items-center justify-center shrink-0">
                    <i data-lucide="sliders" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </div>
            </div>
        </div>

        <!-- Main Chart Section -->
        <div class="bg-white dark:bg-[#111713] border border-gray-200 dark:border-[#27332C] rounded-xl p-4 sm:p-6 shadow-sm transition-colors duration-200 ease-in-out">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm sm:text-base font-semibold text-gray-800 dark:text-gray-200">Stock In vs Out by Product</h2>
            </div>
            <div class="h-72 sm:h-80 relative w-full overflow-hidden">
                <canvas x-ref="movementChart"></canvas>
            </div>
        </div>

    </div>
</x-app-layout>
