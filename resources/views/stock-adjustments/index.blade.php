<x-app-layout>
    <div class="flex items-center justify-between mb-1">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Adjustment History</h1>
            <p class="text-gray-400 text-sm mt-1">{{ $adjustments->total() }} adjustments recorded</p>
        </div>
        @if(auth()->user()->role === 'Admin')
            <a href="{{ route('stock-adjustments.create') }}"
               class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2.5 rounded-lg text-sm font-medium flex items-center gap-2 transition-colors">
                <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                New Adjustment
            </a>
        @endif
    </div>

    <!-- Search + Reason filter -->
    <form method="GET"
          x-data="{ search: '{{ addslashes(request('search')) }}' }"
          x-init="$watch('search', value => {
              clearTimeout(window._adjustmentSearchDebounce);
              window._adjustmentSearchDebounce = setTimeout(() => $el.submit(), 500);
          })"
          class="flex flex-col sm:flex-row gap-3 mt-6">

        <div class="relative flex-1">
            <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
            <input type="text" name="search" x-model="search" placeholder="Search by product or batch..."
                   class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
        </div>

        <div class="relative">
            <select name="reason" onchange="this.form.submit()"
                    class="appearance-none border border-gray-300 rounded-lg pl-3.5 pr-9 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                <option value="">All Reasons</option>
                @foreach(['Physical Count', 'Damaged Goods', 'Theft/Loss', 'Expired Removal', 'Data Entry Error', 'Other'] as $reason)
                    <option value="{{ $reason }}" {{ request('reason') === $reason ? 'selected' : '' }}>{{ $reason }}</option>
                @endforeach
            </select>
            <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
        </div>
    </form>

    <!-- Table -->
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden mt-4">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[900px]">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-left text-gray-500">
                        <th class="px-4 py-3 font-medium whitespace-nowrap">Product</th>
                        <th class="px-4 py-3 font-medium whitespace-nowrap">Batch</th>
                        <th class="px-4 py-3 font-medium text-right whitespace-nowrap">System</th>
                        <th class="px-4 py-3 font-medium text-right whitespace-nowrap">Actual</th>
                        <th class="px-4 py-3 font-medium text-right whitespace-nowrap">Difference</th>
                        <th class="px-4 py-3 font-medium whitespace-nowrap">Reason</th>
                        <th class="px-4 py-3 font-medium whitespace-nowrap">By</th>
                        <th class="px-4 py-3 font-medium whitespace-nowrap">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($adjustments as $adjustment)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-800 max-w-[200px] truncate" title="{{ $adjustment->inventory->product->name ?? '—' }}">
                                {{ $adjustment->inventory->product->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-400 font-mono text-xs whitespace-nowrap">{{ $adjustment->inventory->batch_number ?? '—' }}</td>
                            <td class="px-4 py-3 text-right text-gray-500 whitespace-nowrap">{{ rtrim(rtrim(number_format($adjustment->system_quantity, 2), '0'), '.') }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900 whitespace-nowrap">{{ rtrim(rtrim(number_format($adjustment->actual_quantity, 2), '0'), '.') }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                @php $diff = $adjustment->difference; @endphp
                                <span class="font-medium {{ $diff > 0 ? 'text-green-600' : ($diff < 0 ? 'text-red-600' : 'text-gray-400') }}">
                                    {{ $diff > 0 ? '+' : '' }}{{ rtrim(rtrim(number_format($diff, 2), '0'), '.') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $adjustment->reason }}</td>
                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $adjustment->user->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-400 text-xs whitespace-nowrap">{{ $adjustment->created_at->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-14 text-center">
                                <i data-lucide="sliders-horizontal" class="w-8 h-8 text-gray-300 mx-auto mb-2"></i>
                                <p class="text-gray-500 text-sm">No stock adjustments recorded yet.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($adjustments->hasPages())
        <div class="mt-6">
            {{ $adjustments->links() }}
        </div>
    @endif

</x-app-layout>
