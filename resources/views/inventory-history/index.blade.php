<x-app-layout>
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100 transition-colors duration-200 ease-in-out">Inventory History</h1>
            <p class="text-gray-400 dark:text-gray-400 text-sm mt-1 transition-colors duration-200 ease-in-out">{{ $movements->total() }} total movement records</p>
        </div>
    </div>

    <!-- Type filter tabs -->
    <div class="inline-flex items-center rounded-lg border border-slate-200 dark:border-[#27332C] bg-white dark:bg-[#111713] p-1 shadow-sm overflow-x-auto w-full sm:w-auto transition-colors duration-200 ease-in-out">
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
            class="px-3 py-1.5 text-xs font-medium rounded-md whitespace-nowrap transition-colors flex-1 text-center {{ request('type', 'all') === $value ? 'bg-green-600 text-white' : 'text-slate-500 dark:text-gray-400 hover:text-slate-900 dark:hover:text-gray-100' }}">
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
            <i data-lucide="search" class="w-4 h-4 text-gray-400 dark:text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
            <input type="text" name="search" x-model="search" placeholder="Search by product..."
                   class="w-full border border-gray-300 dark:border-[#27332C] bg-white dark:bg-[#111713] text-gray-800 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500 rounded-lg pl-9 pr-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400/50 focus:border-transparent transition-colors duration-200 ease-in-out">
        </div>
    </form>

    <!-- Table -->
    <div class="bg-white dark:bg-[#111713] border border-gray-200 dark:border-[#27332C] rounded-xl overflow-hidden mt-4 transition-colors duration-200 ease-in-out">
        <div class="overflow-x-auto -webkit-overflow-scrolling-touch">
            <table class="w-full text-sm min-w-[1000px]">
                <thead>
                    <tr class="bg-gray-50 dark:bg-[#161F1A] border-b border-gray-200 dark:border-[#1F2B23] text-left text-gray-500 dark:text-gray-400 transition-colors duration-200 ease-in-out">
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
                <tbody class="divide-y divide-gray-100 dark:divide-[#1F2B23]">
                    @forelse($movements as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-[#161F1A]/60 transition-colors duration-200 ease-in-out">
                            <td class="px-4 py-3 text-gray-400 dark:text-gray-500 text-xs whitespace-nowrap">{{ $row->date->format('Y-m-d') }}</td>
                            <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-200 max-w-[180px] truncate" title="{{ $row->product_name }}">
                                {{ $row->product_name }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $row->type_class }}">
                                    {{ $row->type }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-400 dark:text-gray-500 font-mono text-xs whitespace-nowrap">{{ $row->batch_number }}</td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $row->location }}</td>
                            <td class="px-4 py-3 text-right font-semibold whitespace-nowrap {{ $row->quantity > 0 ? 'text-green-600 dark:text-green-400' : ($row->quantity < 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-400 dark:text-gray-500') }}">
                                {{ $row->quantity > 0 ? '+' : '' }}{{ rtrim(rtrim(number_format($row->quantity, 2), '0'), '.') }}
                                <span class="text-gray-400 dark:text-gray-500 font-normal">{{ $row->unit_abbr }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400 max-w-[180px] truncate" title="{{ $row->reason }}">{{ $row->reason }}</td>
                            <td class="px-4 py-3 whitespace-nowrap leading-tight font-bold">
                                <div class="text-xs text-gray-600 dark:text-gray-200">{{ $row->user_name }}</div>
                                @if($row->user_role && $row->user_role !== '—')
                                    <div class="text-[10px]
                                        {{ $row->user_role === 'Admin' ? 'text-purple-600 dark:text-purple-400' : ($row->user_role === 'Manager' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400')}}">
                                        {{ $row->user_role }}
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-14 text-center">
                                <i data-lucide="history" class="w-8 h-8 text-gray-300 dark:text-[#27332C] mx-auto mb-2"></i>
                                <p class="text-gray-500 dark:text-gray-400 text-sm">No movement records found.</p>
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
