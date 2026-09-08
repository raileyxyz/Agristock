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
                            barThickness: 18,
                        },
                        {
                            label: 'Consumed',
                            data: @js($productData['consumed']),
                            backgroundColor: '#f59e0b',
                            borderRadius: 4,
                            barThickness: 18,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'end',
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
                                    return label.length > 14 ? label.substr(0, 14) + '…' : label;
                                }
                            }
                        }
                    }
                }
            });
        }
    }">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Movement Report</h1>
                <p class="text-gray-500 text-sm mt-0.5">All stock movements and inventory flows to date</p>
            </div>
            <!-- Range Selector Control -->
            <div class="flex flex-wrap items-center gap-2" x-data="{ showCustom: {{ $range === 'custom' ? 'true' : 'false' }} }">
                <div class="inline-flex rounded-lg border border-slate-200 bg-white p-1 shadow-sm">
                    <a href="{{ route('reports.movement', ['range' => '7d']) }}"
                    class="px-3 py-1.5 text-xs font-medium rounded-md transition-colors {{ $range === '7d' ? 'bg-green-600 text-white' : 'text-slate-500 hover:text-slate-900' }}">
                        7 Days
                    </a>
                    <a href="{{ route('reports.movement', ['range' => '30d']) }}"
                    class="px-3 py-1.5 text-xs font-medium rounded-md transition-colors {{ $range === '30d' ? 'bg-green-600 text-white' : 'text-slate-500 hover:text-slate-900' }}">
                        30 Days
                    </a>
                    <a href="{{ route('reports.movement', ['range' => '1y']) }}"
                    class="px-3 py-1.5 text-xs font-medium rounded-md transition-colors {{ $range === '1y' ? 'bg-green-600 text-white' : 'text-slate-500 hover:text-slate-900' }}">
                        1 Year
                    </a>
                    <a href="{{ route('reports.movement', ['range' => 'all']) }}"
                    class="px-3 py-1.5 text-xs font-medium rounded-md transition-colors {{ $range === 'all' ? 'bg-green-600 text-white' : 'text-slate-500 hover:text-slate-900' }}">
                        All Time
                    </a>
                    <button type="button" @click="showCustom = !showCustom"
                            class="px-3 py-1.5 text-xs font-medium rounded-md transition-colors {{ $range === 'custom' ? 'bg-green-600 text-white' : 'text-slate-500 hover:text-slate-900' }}">
                        Custom
                    </button>
                </div>

                <form method="GET" action="{{ route('reports.movement') }}" x-show="showCustom" x-cloak class="flex items-center gap-2">
                    <input type="hidden" name="range" value="custom">
                    <input type="date" name="start_date" value="{{ $customStart }}" required
                        class="border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500">
                    <span class="text-xs text-slate-400">to</span>
                    <input type="date" name="end_date" value="{{ $customEnd }}" required
                        class="border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500">
                    <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition-colors">
                        Apply
                    </button>
                </form>
            </div>
        </div>

        <!-- Metric Summary Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Received</span>
                    <p class="text-2xl font-bold text-green-600 mt-1">{{ number_format($summary['total_received'], 0) }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-green-50 text-green-600 flex items-center justify-center shrink-0">
                    <i data-lucide="arrow-down-left" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Consumed</span>
                    <p class="text-2xl font-bold text-amber-600 mt-1">{{ number_format($summary['total_consumed'], 0) }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <i data-lucide="arrow-up-right" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Adjustments</span>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($summary['adjustments_count']) }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center shrink-0">
                    <i data-lucide="sliders" class="w-5 h-5"></i>
                </div>
            </div>
        </div>

        <!-- Main Chart Container -->
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-semibold text-slate-800">Stock In vs Out by Product</h2>
            </div>
            <div class="h-80 relative">
                <canvas x-ref="movementChart"></canvas>
            </div>
        </div>

    </div>
</x-app-layout>
