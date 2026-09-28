<x-app-layout>
    <div x-data="{
            init() {
                this.renderTrendChart();
                this.renderCategoryChart();
            },
            renderTrendChart() {
                new Chart(this.$refs.trendChart, {
                    type: 'line',
                    data: {
                        labels: @js($valueTrend['labels']),
                        datasets: [{
                            data: @js($valueTrend['values']),
                            borderColor: '#16a34a',
                            backgroundColor: 'rgba(22, 163, 74, 0.08)',
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
                                grid: { color: '#f1f5f9' },
                                ticks: { callback: (v) => '₱' + (v / 1000) + 'k' }
                            },
                            x: { grid: { display: false } }
                        }
                    }
                });
            },
            renderCategoryChart() {
                new Chart(this.$refs.categoryChart, {
                    type: 'doughnut',
                    data: {
                        labels: @js($categoryData['labels']),
                        datasets: [{
                            data: @js($categoryData['values']),
                            backgroundColor: @js($categoryData['colors'] ?? []),
                            borderWidth: 2,
                            borderColor: '#ffffff'
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
                                    font: { size: 10, weight: '500' }
                                }
                            }
                        },
                        cutout: '60%'
                    }
                });
            }
        }">

        <!-- Header -->
        <div class="mb-4 sm:mb-6">
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 tracking-tight">Dashboard</h1>
            <p class="text-xs sm:text-sm text-gray-400 mt-0.5">Last updated {{ now()->format('F d, Y') }}</p>
        </div>

        <!-- Summary Cards Grid (Uniform Sizes) -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 xl:grid-cols-6 gap-3 sm:gap-4 mb-6">

            <!-- Total Products -->
            <a href="{{ route('products.index') }}" class="group relative bg-white border border-gray-200/80 rounded-xl sm:rounded-2xl p-3 sm:p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all flex flex-col justify-between min-h-[110px] sm:min-h-[120px]">
                <div>
                    <div class="flex items-start justify-between mb-2 sm:mb-3">
                        <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl bg-green-50 text-green-600 flex items-center justify-center shrink-0">
                            <i data-lucide="package" class="w-4 h-4 sm:w-4.5 sm:h-4.5"></i>
                        </div>
                        <i data-lucide="arrow-up-right" class="w-3 h-3 text-gray-400 group-hover:text-green-600 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all"></i>
                    </div>
                    <p class="text-lg sm:text-xl font-bold text-gray-900 leading-tight truncate">{{ $summary['total_products'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5 font-medium truncate">Total Products</p>
                </div>
                <p class="text-[10px] sm:text-[11px] text-gray-400 mt-1 truncate">{{ $summary['total_products_archived'] }} archived</p>
            </a>

            <!-- Total Categories -->
            <a href="{{ route('categories.index') }}" class="group relative bg-white border border-gray-200/80 rounded-xl sm:rounded-2xl p-3 sm:p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all flex flex-col justify-between min-h-[110px] sm:min-h-[120px]">
                <div>
                    <div class="flex items-start justify-between mb-2 sm:mb-3">
                        <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <i data-lucide="layers" class="w-4 h-4 sm:w-4.5 sm:h-4.5"></i>
                        </div>
                        <i data-lucide="arrow-up-right" class="w-3 h-3 text-gray-400 group-hover:text-blue-600 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all"></i>
                    </div>
                    <p class="text-lg sm:text-xl font-bold text-gray-900 leading-tight truncate">{{ $summary['total_categories'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5 font-medium truncate">Total Categories</p>
                </div>
                <p class="text-[10px] sm:text-[11px] text-gray-400 mt-1 truncate">Active categories</p>
            </a>

            <!-- Low Stock Items -->
            <a href="{{ route('low-stock.index') }}" class="group relative bg-white border border-gray-200/80 rounded-xl sm:rounded-2xl p-3 sm:p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all flex flex-col justify-between min-h-[110px] sm:min-h-[120px]">
                <div>
                    <div class="flex items-start justify-between mb-2 sm:mb-3">
                        <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                            <i data-lucide="triangle-alert" class="w-4 h-4 sm:w-4.5 sm:h-4.5"></i>
                        </div>
                        <i data-lucide="arrow-up-right" class="w-3 h-3 text-gray-400 group-hover:text-amber-600 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all"></i>
                    </div>
                    <p class="text-lg sm:text-xl font-bold text-amber-600 leading-tight truncate">{{ $summary['low_stock_count'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5 font-medium truncate">Low Stock Items</p>
                </div>
                <p class="text-[10px] sm:text-[11px] text-gray-400 mt-1 truncate">{{ $summary['low_stock_critical'] }} critical</p>
            </a>

            <!-- Expiring Soon -->
            <a href="{{ route('reports.expiry') }}" class="group relative bg-white border border-gray-200/80 rounded-xl sm:rounded-2xl p-3 sm:p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all flex flex-col justify-between min-h-[110px] sm:min-h-[120px]">
                <div>
                    <div class="flex items-start justify-between mb-2 sm:mb-3">
                        <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center shrink-0">
                            <i data-lucide="clock" class="w-4 h-4 sm:w-4.5 sm:h-4.5"></i>
                        </div>
                        <i data-lucide="arrow-up-right" class="w-3 h-3 text-gray-400 group-hover:text-orange-600 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all"></i>
                    </div>
                    <p class="text-lg sm:text-xl font-bold text-orange-600 leading-tight truncate">{{ $summary['expiring_soon_count'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5 font-medium truncate">Expiring Soon</p>
                </div>
                <p class="text-[10px] sm:text-[11px] text-gray-400 mt-1 truncate">Within 60 days</p>
            </a>

            <!-- Expired Products -->
            <a href="{{ route('reports.expiry') }}" class="group relative bg-white border border-gray-200/80 rounded-xl sm:rounded-2xl p-3 sm:p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all flex flex-col justify-between min-h-[110px] sm:min-h-[120px]">
                <div>
                    <div class="flex items-start justify-between mb-2 sm:mb-3">
                        <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl bg-red-50 text-red-600 flex items-center justify-center shrink-0">
                            <i data-lucide="circle-x" class="w-4 h-4 sm:w-4.5 sm:h-4.5"></i>
                        </div>
                        <i data-lucide="arrow-up-right" class="w-3 h-3 text-gray-400 group-hover:text-red-600 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all"></i>
                    </div>
                    <p class="text-lg sm:text-xl font-bold text-red-600 leading-tight truncate">{{ $summary['expired_count'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5 font-medium truncate">Expired Products</p>
                </div>
                <p class="text-[10px] sm:text-[11px] text-gray-400 mt-1 truncate">Immediate action</p>
            </a>

            <!-- Monthly Inventory Value -->
            <div class="bg-white border border-gray-200/80 rounded-xl sm:rounded-2xl p-3 sm:p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all flex flex-col justify-between min-h-[110px] sm:min-h-[120px]">
                <div>
                    <div class="flex items-start justify-between mb-2 sm:mb-3">
                        <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                            <i data-lucide="wallet" class="w-4 h-4 sm:w-4.5 sm:h-4.5"></i>
                        </div>
                    </div>
                    <p class="text-lg sm:text-xl font-bold text-gray-900 leading-tight truncate">₱{{ number_format($summary['monthly_inventory_value'], 0) }}</p>
                    <p class="text-xs text-gray-500 mt-0.5 font-medium truncate">Inventory Value</p>
                </div>
                <p class="text-[10px] sm:text-[11px] text-gray-400 mt-1 truncate">Based on current stock</p>
            </div>

        </div>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-6">
            <div class="bg-white border border-gray-200/80 rounded-xl p-4 sm:p-6 shadow-sm">
                <h2 class="text-sm sm:text-base font-semibold text-gray-800 mb-3 sm:mb-4">Monthly Inventory Value</h2>
                <div class="h-56 sm:h-64 relative">
                    <canvas x-ref="trendChart"></canvas>
                </div>
            </div>
            <div class="bg-white border border-gray-200/80 rounded-xl p-4 sm:p-6 shadow-sm">
                <h2 class="text-sm sm:text-base font-semibold text-gray-800 mb-3 sm:mb-4">Stock by Category</h2>
                <div class="h-56 sm:h-64 relative">
                    <canvas x-ref="categoryChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Tables Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            <!-- Low Stock Table Container -->
            <div class="bg-white border border-gray-200/80 rounded-xl overflow-hidden shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between px-4 sm:px-5 py-3.5 sm:py-4 border-b border-gray-100">
                        <h2 class="text-xs sm:text-sm font-semibold text-gray-800 flex items-center gap-2">
                            <i data-lucide="triangle-alert" class="w-4 h-4 text-amber-500 shrink-0"></i>
                            Low Stock Items
                        </h2>
                        <a href="{{ route('low-stock.index') }}" class="text-xs font-medium text-green-600 hover:text-green-700 flex items-center gap-1 transition-colors">
                            View all <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs sm:text-sm min-w-[320px]">
                            <thead>
                                <tr class="bg-gray-50/70 border-b border-gray-100 text-gray-400 text-[11px] uppercase tracking-wider">
                                    <th class="px-4 sm:px-5 py-2.5 font-medium">Product</th>
                                    <th class="px-4 sm:px-5 py-2.5 font-medium whitespace-nowrap">Stock</th>
                                    <th class="px-4 sm:px-5 py-2.5 font-medium whitespace-nowrap text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($lowStockItems as $product)
                                    @php
                                        $remaining = $product->inventories_sum_remaining_quantity ?? 0;
                                        $isCritical = $remaining <= $product->minimum_stock;
                                    @endphp
                                    <tr class="hover:bg-gray-50/80 transition-colors">
                                        <td class="px-4 sm:px-5 py-3 font-medium text-gray-800 truncate max-w-[140px] sm:max-w-none">{{ $product->name }}</td>
                                        <td class="px-4 sm:px-5 py-3 text-gray-500 whitespace-nowrap">{{ $remaining }} {{ $product->unit->abbreviation ?? '' }}</td>
                                        <td class="px-4 sm:px-5 py-3 whitespace-nowrap text-right">
                                            <span class="inline-flex items-center text-[10px] sm:text-xs font-medium px-2 py-0.5 rounded-full {{ $isCritical ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-600' }}">
                                                {{ $isCritical ? 'Critical' : 'Low' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="px-5 py-8 text-center text-gray-400 text-xs sm:text-sm">No low stock items.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Expiring Soon Table Container -->
            <div class="bg-white border border-gray-200/80 rounded-xl overflow-hidden shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between px-4 sm:px-5 py-3.5 sm:py-4 border-b border-gray-100">
                        <h2 class="text-xs sm:text-sm font-semibold text-gray-800 flex items-center gap-2">
                            <i data-lucide="clock" class="w-4 h-4 text-orange-500 shrink-0"></i>
                            Expiring Soon
                        </h2>
                        <a href="{{ route('reports.expiry') }}" class="text-xs font-medium text-green-600 hover:text-green-700 flex items-center gap-1 transition-colors">
                            View all <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs sm:text-sm min-w-[320px]">
                            <thead>
                                <tr class="bg-gray-50/70 border-b border-gray-100 text-gray-400 text-[11px] uppercase tracking-wider">
                                    <th class="px-4 sm:px-5 py-2.5 font-medium">Product</th>
                                    <th class="px-4 sm:px-5 py-2.5 font-medium whitespace-nowrap">Batch</th>
                                    <th class="px-4 sm:px-5 py-2.5 font-medium text-right whitespace-nowrap">Days Left</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($expiringSoonItems as $item)
                                    <tr class="hover:bg-gray-50/80 transition-colors">
                                        <td class="px-4 sm:px-5 py-3 font-medium text-gray-800 truncate max-w-[140px] sm:max-w-none">{{ $item['product_name'] }}</td>
                                        <td class="px-4 sm:px-5 py-3 text-gray-400 text-xs whitespace-nowrap">{{ $item['batch_number'] }}</td>
                                        <td class="px-4 sm:px-5 py-3 text-right whitespace-nowrap">
                                            <span class="text-xs font-bold {{ $item['days'] <= 30 ? 'text-red-500' : 'text-amber-600' }}">
                                                {{ $item['days'] }}d
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="px-5 py-8 text-center text-gray-400 text-xs sm:text-sm">Nothing expiring soon.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
