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
                                position: 'right',
                                labels: {
                                    boxWidth: 10,
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    padding: 14,
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
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-gray-400 text-sm mt-0.5">Last updated {{ now()->format('F d, Y') }}</p>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
            <!-- Total Products -->
            <a href="{{ route('products.index') }}" class="group relative bg-white border border-gray-200/80 rounded-2xl p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all block">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-9 h-9 rounded-xl bg-green-50 text-green-600 flex items-center justify-center">
                        <i data-lucide="package" class="w-4.5 h-4.5"></i>
                    </div>
                    <i data-lucide="arrow-up-right" class="w-3 h-3 text-gray-400 group-hover:text-green-600 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all"></i>
                </div>
                <p class="text-xl font-bold text-gray-900">{{ $summary['total_products'] }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Total Products</p>
                <p class="text-[11px] text-gray-400 mt-0.5">{{ $summary['total_products_archived'] }} archived</p>
            </a>

            <!-- Total Categories -->
            <a href="{{ route('categories.index') }}" class="group relative bg-white border border-gray-200/80 rounded-2xl p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all block">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i data-lucide="layers" class="w-4.5 h-4.5"></i>
                    </div>
                    <i data-lucide="arrow-up-right" class="w-3 h-3 text-gray-400 group-hover:text-blue-600 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all"></i>
                </div>
                <p class="text-xl font-bold text-gray-900">{{ $summary['total_categories'] }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Total Categories</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Active categories</p>
            </a>

            <!-- Low Stock Items -->
            <a href="{{ route('low-stock.index') }}" class="group relative bg-white border border-gray-200/80 rounded-2xl p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all block">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <i data-lucide="triangle-alert" class="w-4.5 h-4.5"></i>
                    </div>
                    <i data-lucide="arrow-up-right" class="w-3 h-3 text-gray-400 group-hover:text-amber-600 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all"></i>
                </div>
                <p class="text-xl font-bold text-amber-600">{{ $summary['low_stock_count'] }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Low Stock Items</p>
                <p class="text-[11px] text-gray-400 mt-0.5">{{ $summary['low_stock_critical'] }} critical</p>
            </a>

            <!-- Expiring Soon -->
            <a href="{{ route('reports.expiry') }}" class="group relative bg-white border border-gray-200/80 rounded-2xl p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all block">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-9 h-9 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center">
                        <i data-lucide="clock" class="w-4.5 h-4.5"></i>
                    </div>
                    <i data-lucide="arrow-up-right" class="w-3 h-3 text-gray-400 group-hover:text-orange-600 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all"></i>
                </div>
                <p class="text-xl font-bold text-orange-600">{{ $summary['expiring_soon_count'] }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Expiring Soon</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Within 60 days</p>
            </a>

            <!-- Expired Products -->
            <a href="{{ route('reports.expiry') }}" class="group relative bg-white border border-gray-200/80 rounded-2xl p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all block">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-9 h-9 rounded-xl bg-red-50 text-red-600 flex items-center justify-center">
                        <i data-lucide="circle-x" class="w-4.5 h-4.5"></i>
                    </div>
                    <i data-lucide="arrow-up-right" class="w-3 h-3 text-gray-400 group-hover:text-red-600 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all"></i>
                </div>
                <p class="text-xl font-bold text-red-600">{{ $summary['expired_count'] }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Expired Products</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Immediate action</p>
            </a>

            <!-- Monthly Inventory Value (Non-link card) -->
            <div class="bg-white border border-gray-200/80 rounded-2xl p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i data-lucide="wallet" class="w-4.5 h-4.5"></i>
                    </div>
                </div>
                <p class="text-xl font-bold text-gray-900">₱{{ number_format($summary['monthly_inventory_value'], 0) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Current Inventory Value</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Based on current stock</p>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white border border-gray-200/80 rounded-xl p-6 shadow-sm">
                <h2 class="text-base font-semibold text-gray-800 mb-4">Monthly Inventory Value</h2>
                <div class="h-64 relative">
                    <canvas x-ref="trendChart"></canvas>
                </div>
            </div>
            <div class="bg-white border border-gray-200/80 rounded-xl p-6 shadow-sm">
                <h2 class="text-base font-semibold text-gray-800 mb-4">Stock by Category</h2>
                <div class="h-64 relative">
                    <canvas x-ref="categoryChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Tables -->
        <div class="grid lg:grid-cols-2 gap-6">

            <div class="bg-white border border-gray-200/80 rounded-xl overflow-hidden shadow-sm">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                        <i data-lucide="triangle-alert" class="w-4 h-4 text-amber-500"></i>
                        Low Stock Items
                    </h2>
                    <a href="{{ route('low-stock.index') }}" class="text-xs font-medium text-green-600 hover:text-green-700 flex items-center gap-1">
                        View all <i data-lucide="arrow-right" class="w-3 h-3"></i>
                    </a>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-left text-gray-400 text-xs">
                            <th class="px-5 py-2.5 font-medium w-full">Product</th>
                            <th class="px-5 py-2.5 font-medium whitespace-nowrap">Stock</th>
                            <th class="px-5 py-2.5 font-medium whitespace-nowrap">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($lowStockItems as $product)
                            @php
                                $remaining = $product->inventories_sum_remaining_quantity ?? 0;
                                $isCritical = $remaining <= $product->minimum_stock;
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-3 w-full font-medium text-gray-800">{{ $product->name }}</td>
                                <td class="px-5 py-3 text-gray-500 whitespace-nowrap">{{ $remaining }} {{ $product->unit->abbreviation ?? '' }}</td>
                                <td class="px-5 py-3 whitespace-nowrap">
                                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $isCritical ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-600' }}">
                                        {{ $isCritical ? 'Critical' : 'Low' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-8 text-center text-gray-400 text-sm">No low stock items.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bg-white border border-gray-200/80 rounded-xl overflow-hidden shadow-sm">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                        <i data-lucide="clock" class="w-4 h-4 text-orange-500"></i>
                        Expiring Soon
                    </h2>
                    <a href="{{ route('reports.expiry') }}" class="text-xs font-medium text-green-600 hover:text-green-700 flex items-center gap-1">
                        View all <i data-lucide="arrow-right" class="w-3 h-3"></i>
                    </a>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-left text-gray-400 text-xs">
                            <th class="px-5 py-2.5 font-medium w-full">Product</th>
                            <th class="px-5 py-2.5 font-medium whitespace-nowrap">Batch</th>
                            <th class="px-5 py-2.5 font-medium text-right whitespace-nowrap">Days Left</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($expiringSoonItems as $item)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-3 w-full font-medium text-gray-800">{{ $item['product_name'] }}</td>
                                <td class="px-5 py-3 text-gray-400 text-xs whitespace-nowrap">{{ $item['batch_number'] }}</td>
                                <td class="px-5 py-3 text-right whitespace-nowrap">
                                    <span class="text-xs font-bold {{ $item['days'] <= 30 ? 'text-red-500' : 'text-amber-600' }}">
                                        {{ $item['days'] }}d
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-8 text-center text-gray-400 text-sm">Nothing expiring soon.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

    </div>
</x-app-layout>
