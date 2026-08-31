<x-app-layout>
    <div class="flex items-center justify-between mb-1">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Inventory History</h1>
            <p class="text-gray-400 text-sm mt-1">{{ $movements->total() }} total movement records</p>
        </div>
    </div>

    <!-- Type filter tabs -->
    <div class="flex items-center gap-1.5 bg-gray-100 p-1 rounded-lg w-full sm:w-fit mt-6 overflow-x-auto">
        @php
            $tabs = [
                'all' => 'All',
                'stock-in' => 'Stock In',
                'stock-out' => 'Stock Out',
                'transfer' => 'Transfer',
                'adjustment' => 'Adjustments',
            ];
        @endphp
        @foreach($tabs as $value => $label)
            <a href="{{ request()->fullUrlWithQuery(['type' => $value, 'page' => null]) }}"
               class="px-3.5 py-1.5 rounded-md text-sm font-medium transition-colors whitespace-nowrap shrink-0
               {{ request('type', 'all') === $value ? 'bg-green-600 text-white shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <!-- Search -->
    <form method="GET"
          x-data="{ search: '{{ addslashes(request('search')) }}' }"
          x-init="$watch('search', value => {
              clearTimeout(window._historySearchDebounce);
              window._historySearchDebounce = setTimeout(() => $el.submit(), 500);
          })"
          class="mt-4">
        <input type="hidden" name="type" value="{{ request('type', 'all') }}">
        <div class="relative w-full sm:max-w-sm">
            <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
            <input type="text" name="search" x-model="search" placeholder="Search by product..."
                   class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
        </div>
    </form>

    <!-- Table -->
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden mt-4">
        <div class="overflow-x-auto -webkit-overflow-scrolling-touch">
            <table class="w-full text-sm min-w-[1000px]">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-left text-gray-500">
                        <th class="px-4 py-3 font-medium whitespace-nowrap">Date</th>
                        <th class="px-4 py-3 font-medium whitespace-nowrap">Product</th>
                        <th class="px-4 py-3 font-medium whitespace-nowrap">Type</th>
                        <th class="px-4 py-3 font-medium whitespace-nowrap">Batch No.</th>
                        <th class="px-4 py-3 font-medium whitespace-nowrap">Location</th>
                        <th class="px-4 py-3 font-medium text-right whitespace-nowrap">Qty</th>
                        <th class="px-4 py-3 font-medium whitespace-nowrap">Reason</th>
                        <th class="px-4 py-3 font-medium whitespace-nowrap">By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($movements as $row)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-gray-400 text-xs whitespace-nowrap">{{ $row->date->format('Y-m-d') }}</td>
                            <td class="px-4 py-3 font-medium text-gray-800 max-w-[180px] truncate" title="{{ $row->product_name }}">
                                {{ $row->product_name }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $row->type_class }}">
                                    {{ $row->type }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-400 font-mono text-xs whitespace-nowrap">{{ $row->batch_number }}</td>
                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $row->location }}</td>
                            <td class="px-4 py-3 text-right font-semibold whitespace-nowrap {{ $row->quantity > 0 ? 'text-green-600' : ($row->quantity < 0 ? 'text-red-600' : 'text-gray-400') }}">
                                {{ $row->quantity > 0 ? '+' : '' }}{{ rtrim(rtrim(number_format($row->quantity, 2), '0'), '.') }}
                                <span class="text-gray-400 font-normal">{{ $row->unit_abbr }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 max-w-[180px] truncate" title="{{ $row->reason }}">{{ $row->reason }}</td>
                            <td class="px-4 py-3 whitespace-nowrap leading-tight font-bold">
                                <div class="text-xs text-gray-600">{{ $row->user_name }}</div>
                                @if($row->user_role && $row->user_role !== '—')
                                    <div class="text-[10px]
                                        {{ $row->user_role === 'Admin' ? 'text-purple-600' : ($row->user_role === 'Manager' ? 'text-blue-600' : 'text-gray-500')}}">
                                        {{ $row->user_role }}
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-14 text-center">
                                <i data-lucide="history" class="w-8 h-8 text-gray-300 mx-auto mb-2"></i>
                                <p class="text-gray-500 text-sm">No movement records found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($movements->hasPages())
        <div class="mt-6">
            {{ $movements->links() }}
        </div>
    @endif

</x-app-layout>
