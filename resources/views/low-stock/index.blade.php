<x-app-layout>
    <div class="mb-1">
        <h1 class="text-2xl font-bold text-gray-900">Low Stock Monitoring</h1>
        <p class="text-gray-400 text-sm mt-1">{{ $totalCount }} {{ Str::plural('item', $totalCount) }} need attention</p>
    </div>

    <!-- Search + Category filter -->
    <form method="GET"
          x-data="{ search: '{{ addslashes(request('search')) }}' }"
          x-init="$watch('search', value => {
              clearTimeout(window._lowStockSearchDebounce);
              window._lowStockSearchDebounce = setTimeout(() => $el.submit(), 500);
          })"
          class="flex flex-col sm:flex-row gap-3 mt-6">

        <div class="relative flex-1">
            <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
            <input type="text" name="search" x-model="search" placeholder="Search products..."
                   class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
        </div>

        <div class="relative">
            <select name="category_id" onchange="this.form.submit()"
                    class="appearance-none border border-gray-300 rounded-lg pl-3.5 pr-9 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
        </div>
    </form>

    <!-- Product cards -->
    <div class="space-y-3 mt-4">
        @forelse($products as $product)
            @php
                $current = (float) ($product->inventories_sum_remaining_quantity ?? 0);
                $reorderPoint = (float) $product->reorder_point;
                $minimumStock = (float) $product->minimum_stock;
                $percent = $reorderPoint > 0 ? min(100, ($current / $reorderPoint) * 100) : 0;

                if ($current <= 0) {
                    $status = ['label' => 'Out of Stock', 'badge' => 'bg-gray-100 text-gray-600', 'bar' => 'bg-gray-300'];
                } elseif ($current <= $minimumStock) {
                    $status = ['label' => 'Critical', 'badge' => 'bg-red-50 text-red-600', 'bar' => 'bg-red-500'];
                } else {
                    $status = ['label' => 'Low Stock', 'badge' => 'bg-amber-50 text-amber-600', 'bar' => 'bg-amber-500'];
                }
            @endphp
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <div class="flex items-center gap-4">
                    <div class="w-11 h-11 rounded-lg flex items-center justify-center text-xl shrink-0"
                         style="background-color: {{ $product->category->icon_color ?? '#6b7280' }}22;">
                        {{ $product->category->icon ?? '📦' }}
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-3 mb-2">
                            <p class="font-semibold text-gray-800 truncate">{{ $product->name }}</p>
                            <div class="flex items-center gap-3 shrink-0">
                                <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $status['badge'] }}">
                                    {{ $status['label'] }}
                                </span>
                                <button type="button" title="Coming soon"
                                        class="bg-gray-200 text-gray-400 px-4 py-1.5 rounded-lg text-sm font-medium cursor-not-allowed">
                                    Reorder
                                </button>
                            </div>
                        </div>

                        <div class="relative h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div class="absolute inset-y-0 left-0 rounded-full {{ $status['bar'] }}" style="width: {{ $percent }}%;"></div>
                        </div>

                        <div class="flex items-center justify-between mt-1.5">
                            <p class="text-xs text-gray-400">Reorder at: {{ rtrim(rtrim(number_format($reorderPoint, 2), '0'), '.') }} {{ $product->unit->abbreviation ?? '' }}</p>
                            <p class="text-xs text-gray-500">{{ rtrim(rtrim(number_format($current, 2), '0'), '.') }} / {{ rtrim(rtrim(number_format($reorderPoint, 2), '0'), '.') }} {{ $product->unit->abbreviation ?? '' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white border border-gray-200 rounded-xl px-5 py-14 text-center">
                <i data-lucide="check-circle" class="w-8 h-8 text-green-300 mx-auto mb-2"></i>
                <p class="text-gray-500 text-sm">No products need attention right now.</p>
                <p class="text-gray-400 text-xs mt-1">All active products are above their reorder point.</p>
            </div>
        @endforelse
    </div>

    @if($products->hasPages())
        <div class="mt-6">
            {{ $products->links() }}
        </div>
    @endif

</x-app-layout>
