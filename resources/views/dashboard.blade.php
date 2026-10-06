<x-app-layout>
    <div x-data="{
            isDark: document.documentElement.classList.contains('dark'),
            init() {
                if (this.$refs.trendChart) {
                    this.renderTrendChart();
                }
                this.renderCategoryChart();
            },
            trendChart: null,
            categoryChart: null,
            renderTrendChart() {
                const gridColor = this.isDark ? '#1F2B23' : '#f1f5f9';
                const textColor = this.isDark ? '#9CA3AF' : '#6B7280';

                this.trendChart = new Chart(this.$refs.trendChart, {
                    type: 'line',
                    data: {
                        labels: @js($valueTrend['labels']),
                        datasets: [{
                            data: @js($valueTrend['values']),
                            borderColor: '#16a34a',
                            backgroundColor: 'rgba(22, 163, 74, 0.12)',
                            fill: true,
                            tension: 0.35,
                            pointRadius: 3,
                            pointBackgroundColor: '#16a34a',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: gridColor },
                                ticks: {
                                    color: textColor,
                                    callback: (v) => '₱' + (v / 1000) + 'k'
                                }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { color: textColor }
                            }
                        }
                    }
                });
            },
            renderCategoryChart() {
                const textColor = this.isDark ? '#D1D5DB' : '#374151';
                const borderColor = this.isDark ? '#111713' : '#ffffff';

                this.categoryChart = new Chart(this.$refs.categoryChart, {
                    type: 'doughnut',
                    data: {
                        labels: @js($categoryData['labels']),
                        datasets: [{
                            data: @js($categoryData['values']),
                            backgroundColor: @js($categoryData['colors'] ?? []),
                            borderWidth: 2,
                            borderColor: borderColor
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: window.innerWidth < 640 ? 'bottom' : 'right',
                                labels: {
                                    boxWidth: 10,
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    padding: 12,
                                    color: textColor,
                                    font: { size: 10, weight: '500' }
                                }
                            }
                        },
                        cutout: '60%'
                    }
                });
            },
            handleThemeChange() {
                this.isDark = document.documentElement.classList.contains('dark');

                if (this.trendChart) {
                    const gridColor = this.isDark ? '#1F2B23' : '#f1f5f9';
                    const textColor = this.isDark ? '#9CA3AF' : '#6B7280';

                    this.trendChart.options.scales.y.grid.color = gridColor;
                    this.trendChart.options.scales.y.ticks.color = textColor;
                    this.trendChart.options.scales.x.ticks.color = textColor;
                    this.trendChart.update('none');
                }

                // Mabilis na pag-update ng category chart
                if (this.categoryChart) {
                    const textColor = this.isDark ? '#D1D5DB' : '#374151';
                    const borderColor = this.isDark ? '#111713' : '#ffffff';

                    this.categoryChart.data.datasets[0].borderColor = borderColor;
                    this.categoryChart.options.plugins.legend.labels.color = textColor;
                    this.categoryChart.update('none');
                }
            }
        }"
        @theme-changed.window="handleThemeChange()">

        <!-- Header -->
        <div class="mb-4 sm:mb-6">
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 dark:text-gray-100 tracking-tight transition-colors duration-200 ease-in-out">Dashboard</h1>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5 transition-colors duration-200 ease-in-out">Last updated {{ now()->format('F d, Y') }}</p>
        </div>

        <!-- Summary Cards Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 @can('reports.valuation') xl:grid-cols-6 @else xl:grid-cols-5 @endcan gap-3 sm:gap-4 mb-6">

            <!-- Total Products -->
            <a href="{{ route('products.index') }}" class="group relative bg-white dark:bg-[#111713] border border-gray-200/80 dark:border-[#27332C] rounded-xl sm:rounded-2xl p-3 sm:p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 ease-in-out flex flex-col justify-between min-h-[110px] sm:min-h-[120px]">
                <div>
                    <div class="flex items-start justify-between mb-2 sm:mb-3">
                        <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl bg-green-50 dark:bg-green-950/40 text-green-600 dark:text-green-400 flex items-center justify-center shrink-0 transition-colors duration-200 ease-in-out">
                            <i data-lucide="package" class="w-4 h-4 sm:w-4.5 sm:h-4.5"></i>
                        </div>
                        <i data-lucide="arrow-up-right" class="w-3 h-3 text-gray-400 dark:text-gray-500 group-hover:text-green-600 dark:group-hover:text-green-400 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all duration-200 ease-in-out"></i>
                    </div>
                    <p class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-100 leading-tight truncate transition-colors duration-200 ease-in-out">{{ $summary['total_products'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 font-medium truncate transition-colors duration-200 ease-in-out">Total Products</p>
                </div>
                <p class="text-[10px] sm:text-[11px] text-gray-400 dark:text-gray-500 mt-1 truncate transition-colors duration-200 ease-in-out">{{ $summary['total_products_archived'] }} archived</p>
            </a>

            <!-- Total Categories -->
            <a href="{{ route('categories.index') }}" class="group relative bg-white dark:bg-[#111713] border border-gray-200/80 dark:border-[#27332C] rounded-xl sm:rounded-2xl p-3 sm:p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 ease-in-out flex flex-col justify-between min-h-[110px] sm:min-h-[120px]">
                <div>
                    <div class="flex items-start justify-between mb-2 sm:mb-3">
                        <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 transition-colors duration-200 ease-in-out">
                            <i data-lucide="layers" class="w-4 h-4 sm:w-4.5 sm:h-4.5"></i>
                        </div>
                        <i data-lucide="arrow-up-right" class="w-3 h-3 text-gray-400 dark:text-gray-500 group-hover:text-blue-600 dark:group-hover:text-blue-400 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all duration-200 ease-in-out"></i>
                    </div>
                    <p class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-100 leading-tight truncate transition-colors duration-200 ease-in-out">{{ $summary['total_categories'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 font-medium truncate transition-colors duration-200 ease-in-out">Total Categories</p>
                </div>
                <p class="text-[10px] sm:text-[11px] text-gray-400 dark:text-gray-500 mt-1 truncate transition-colors duration-200 ease-in-out">Active categories</p>
            </a>

            <!-- Low Stock Items -->
            <a href="{{ route('low-stock.index') }}" class="group relative bg-white dark:bg-[#111713] border border-gray-200/80 dark:border-[#27332C] rounded-xl sm:rounded-2xl p-3 sm:p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 ease-in-out flex flex-col justify-between min-h-[110px] sm:min-h-[120px]">
                <div>
                    <div class="flex items-start justify-between mb-2 sm:mb-3">
                        <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 transition-colors duration-200 ease-in-out">
                            <i data-lucide="triangle-alert" class="w-4 h-4 sm:w-4.5 sm:h-4.5"></i>
                        </div>
                        <i data-lucide="arrow-up-right" class="w-3 h-3 text-gray-400 dark:text-gray-500 group-hover:text-amber-600 dark:group-hover:text-amber-400 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all duration-200 ease-in-out"></i>
                    </div>
                    <p class="text-lg sm:text-xl font-bold text-amber-600 dark:text-amber-400 leading-tight truncate transition-colors duration-200 ease-in-out">{{ $summary['low_stock_count'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 font-medium truncate transition-colors duration-200 ease-in-out">Low Stock Items</p>
                </div>
                <p class="text-[10px] sm:text-[11px] text-gray-400 dark:text-gray-500 mt-1 truncate transition-colors duration-200 ease-in-out">{{ $summary['low_stock_critical'] }} critical</p>
            </a>

            <!-- Expiring Soon -->
            <a href="{{ route('reports.expiry') }}" class="group relative bg-white dark:bg-[#111713] border border-gray-200/80 dark:border-[#27332C] rounded-xl sm:rounded-2xl p-3 sm:p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 ease-in-out flex flex-col justify-between min-h-[110px] sm:min-h-[120px]">
                <div>
                    <div class="flex items-start justify-between mb-2 sm:mb-3">
                        <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl bg-orange-50 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 flex items-center justify-center shrink-0 transition-colors duration-200 ease-in-out">
                            <i data-lucide="clock" class="w-4 h-4 sm:w-4.5 sm:h-4.5"></i>
                        </div>
                        <i data-lucide="arrow-up-right" class="w-3 h-3 text-gray-400 dark:text-gray-500 group-hover:text-orange-600 dark:group-hover:text-orange-400 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all duration-200 ease-in-out"></i>
                    </div>
                    <p class="text-lg sm:text-xl font-bold text-orange-600 dark:text-orange-400 leading-tight truncate transition-colors duration-200 ease-in-out">{{ $summary['expiring_soon_count'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 font-medium truncate transition-colors duration-200 ease-in-out">Expiring Soon</p>
                </div>
                <p class="text-[10px] sm:text-[11px] text-gray-400 dark:text-gray-500 mt-1 truncate transition-colors duration-200 ease-in-out">Within 60 days</p>
            </a>

            <!-- Expired Products -->
            <a href="{{ route('reports.expiry') }}" class="group relative bg-white dark:bg-[#111713] border border-gray-200/80 dark:border-[#27332C] rounded-xl sm:rounded-2xl p-3 sm:p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 ease-in-out flex flex-col justify-between min-h-[110px] sm:min-h-[120px]">
                <div>
                    <div class="flex items-start justify-between mb-2 sm:mb-3">
                        <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0 transition-colors duration-200 ease-in-out">
                            <i data-lucide="circle-x" class="w-4 h-4 sm:w-4.5 sm:h-4.5"></i>
                        </div>
                        <i data-lucide="arrow-up-right" class="w-3 h-3 text-gray-400 dark:text-gray-500 group-hover:text-red-600 dark:group-hover:text-red-400 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all duration-200 ease-in-out"></i>
                    </div>
                    <p class="text-lg sm:text-xl font-bold text-red-600 dark:text-red-400 leading-tight truncate transition-colors duration-200 ease-in-out">{{ $summary['expired_count'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 font-medium truncate transition-colors duration-200 ease-in-out">Expired Products</p>
                </div>
                <p class="text-[10px] sm:text-[11px] text-gray-400 dark:text-gray-500 mt-1 truncate transition-colors duration-200 ease-in-out">Immediate action</p>
            </a>

            <!-- Monthly Inventory Value -->
            @can('reports.valuation')
            <div class="bg-white dark:bg-[#111713] border border-gray-200/80 dark:border-[#27332C] rounded-xl sm:rounded-2xl p-3 sm:p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 ease-in-out flex flex-col justify-between min-h-[110px] sm:min-h-[120px]">
                <div>
                    <div class="flex items-start justify-between mb-2 sm:mb-3">
                        <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 transition-colors duration-200 ease-in-out">
                            <i data-lucide="wallet" class="w-4 h-4 sm:w-4.5 sm:h-4.5"></i>
                        </div>
                    </div>
                    <p class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-100 leading-tight truncate transition-colors duration-200 ease-in-out">₱{{ number_format($summary['monthly_inventory_value'], 0) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 font-medium truncate transition-colors duration-200 ease-in-out">Inventory Value</p>
                </div>
                <p class="text-[10px] sm:text-[11px] text-gray-400 dark:text-gray-500 mt-1 truncate transition-colors duration-200 ease-in-out">Based on current stock</p>
            </div>
            @endcan

        </div>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-6">
            @can('reports.valuation')
            <div class="bg-white dark:bg-[#111713] border border-gray-200/80 dark:border-[#27332C] rounded-xl p-4 sm:p-6 shadow-sm transition-colors duration-200 ease-in-out">
                <h2 class="text-sm sm:text-base font-semibold text-gray-800 dark:text-gray-200 mb-3 sm:mb-4 transition-colors duration-200 ease-in-out">Monthly Inventory Value</h2>
                <div class="h-56 sm:h-64 relative">
                    <canvas x-ref="trendChart"></canvas>
                </div>
            </div>
            @endcan
            <div class="bg-white dark:bg-[#111713] border border-gray-200/80 dark:border-[#27332C] rounded-xl p-4 sm:p-6 shadow-sm transition-colors duration-200 ease-in-out @cannot('reports.valuation') lg:col-span-2 @endcannot">
                <h2 class="text-sm sm:text-base font-semibold text-gray-800 dark:text-gray-200 mb-3 sm:mb-4 transition-colors duration-200 ease-in-out">Stock by Category</h2>
                <div class="h-56 sm:h-64 relative">
                    <canvas x-ref="categoryChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Tables Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            <!-- Low Stock Table Container -->
            <div class="bg-white dark:bg-[#111713] border border-gray-200/80 dark:border-[#27332C] rounded-xl overflow-hidden shadow-sm flex flex-col justify-between transition-colors duration-200 ease-in-out">
                <div>
                    <div class="flex items-center justify-between px-4 sm:px-5 py-3.5 sm:py-4 border-b border-gray-100 dark:border-[#1F2B23] transition-colors duration-200 ease-in-out">
                        <h2 class="text-xs sm:text-sm font-semibold text-gray-800 dark:text-gray-200 flex items-center gap-2 transition-colors duration-200 ease-in-out">
                            <i data-lucide="triangle-alert" class="w-4 h-4 text-amber-500 shrink-0"></i>
                            Low Stock Items
                        </h2>
                        <a href="{{ route('low-stock.index') }}" class="text-xs font-medium text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 flex items-center gap-1 transition-colors duration-200 ease-in-out">
                            View all <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs sm:text-sm min-w-[320px]">
                            <thead>
                                <tr class="bg-gray-50/70 dark:bg-[#161F1A] border-b border-gray-100 dark:border-[#1F2B23] text-gray-400 dark:text-gray-400 text-[11px] uppercase tracking-wider transition-colors duration-200 ease-in-out">
                                    <th class="px-4 sm:px-5 py-2.5 font-medium">Product</th>
                                    <th class="px-4 sm:px-5 py-2.5 font-medium whitespace-nowrap">Stock</th>
                                    <th class="px-4 sm:px-5 py-2.5 font-medium whitespace-nowrap text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-[#1F2B23] transition-colors duration-200 ease-in-out">
                                @forelse($lowStockItems as $product)
                                    @php
                                        $remaining = $product->inventories_sum_remaining_quantity ?? 0;
                                        $isCritical = $remaining <= $product->minimum_stock;
                                    @endphp
                                    <tr class="hover:bg-gray-50/80 dark:hover:bg-[#161F1A]/60 transition-colors duration-200 ease-in-out">
                                        <td class="px-4 sm:px-5 py-3 font-medium text-gray-800 dark:text-gray-200 truncate max-w-[140px] sm:max-w-none transition-colors duration-200 ease-in-out">{{ $product->name }}</td>
                                        <td class="px-4 sm:px-5 py-3 text-gray-500 dark:text-gray-400 whitespace-nowrap transition-colors duration-200 ease-in-out">{{ $remaining }} {{ $product->unit->abbreviation ?? '' }}</td>
                                        <td class="px-4 sm:px-5 py-3 whitespace-nowrap text-right">
                                            <span class="inline-flex items-center text-[10px] sm:text-xs font-medium px-2 py-0.5 rounded-full {{ $isCritical ? 'bg-red-50 dark:bg-red-950/50 text-red-600 dark:text-red-400' : 'bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400' }} transition-colors duration-200 ease-in-out">
                                                {{ $isCritical ? 'Critical' : 'Low' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="px-5 py-8 text-center text-gray-400 dark:text-gray-500 text-xs sm:text-sm">No low stock items.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Expiring Soon Table Container -->
            <div class="bg-white dark:bg-[#111713] border border-gray-200/80 dark:border-[#27332C] rounded-xl overflow-hidden shadow-sm flex flex-col justify-between transition-colors duration-200 ease-in-out">
                <div>
                    <div class="flex items-center justify-between px-4 sm:px-5 py-3.5 sm:py-4 border-b border-gray-100 dark:border-[#1F2B23] transition-colors duration-200 ease-in-out">
                        <h2 class="text-xs sm:text-sm font-semibold text-gray-800 dark:text-gray-200 flex items-center gap-2 transition-colors duration-200 ease-in-out">
                            <i data-lucide="clock" class="w-4 h-4 text-orange-500 shrink-0"></i>
                            Expiring Soon
                        </h2>
                        <a href="{{ route('reports.expiry') }}" class="text-xs font-medium text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 flex items-center gap-1 transition-colors duration-200 ease-in-out">
                            View all <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs sm:text-sm min-w-[320px]">
                            <thead>
                                <tr class="bg-gray-50/70 dark:bg-[#161F1A] border-b border-gray-100 dark:border-[#1F2B23] text-gray-400 dark:text-gray-400 text-[11px] uppercase tracking-wider transition-colors duration-200 ease-in-out">
                                    <th class="px-4 sm:px-5 py-2.5 font-medium">Product</th>
                                    <th class="px-4 sm:px-5 py-2.5 font-medium whitespace-nowrap">Batch</th>
                                    <th class="px-4 sm:px-5 py-2.5 font-medium text-right whitespace-nowrap">Days Left</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-[#1F2B23] transition-colors duration-200 ease-in-out">
                                @forelse($expiringSoonItems as $item)
                                    <tr class="hover:bg-gray-50/80 dark:hover:bg-[#161F1A]/60 transition-colors duration-200 ease-in-out">
                                        <td class="px-4 sm:px-5 py-3 font-medium text-gray-800 dark:text-gray-200 truncate max-w-[140px] sm:max-w-none transition-colors duration-200 ease-in-out">{{ $item['product_name'] }}</td>
                                        <td class="px-4 sm:px-5 py-3 text-gray-400 dark:text-gray-500 text-xs whitespace-nowrap transition-colors duration-200 ease-in-out">{{ $item['batch_number'] }}</td>
                                        <td class="px-4 sm:px-5 py-3 text-right whitespace-nowrap">
                                            <span class="text-xs font-bold {{ $item['days'] <= 30 ? 'text-red-500 dark:text-red-400' : 'text-amber-600 dark:text-amber-400' }} transition-colors duration-200 ease-in-out">
                                                {{ $item['days'] }}d
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="px-5 py-8 text-center text-gray-400 dark:text-gray-500 text-xs sm:text-sm">Nothing expiring soon.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
