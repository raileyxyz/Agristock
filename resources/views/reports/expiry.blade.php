<x-app-layout>
    <div class="max-w-6xl">

        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Expiry Report</h1>
                    <p class="text-gray-400 text-sm mt-0.5">
                        {{ $summary['tracked'] }} tracked · {{ $summary['expired'] }} expired · {{ $summary['within_30'] + $summary['within_60'] }} expiring soon
                    </p>
                </div>
            </div>
        </div>

        <!-- Summary Cards (Pinanatili ang mas malinaw na UI) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white border border-gray-200/80 rounded-xl p-5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-red-600">Expired Items</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $summary['expired'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Past expiration date</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center shrink-0">
                    <i data-lucide="circle-x" class="w-6 h-6"></i>
                </div>
            </div>

            <div class="bg-white border border-gray-200/80 rounded-xl p-5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-red-500">Critical (Within 30 Days)</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $summary['within_30'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Requires immediate action</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-red-50 text-red-500 flex items-center justify-center shrink-0">
                    <i data-lucide="alarm-clock" class="w-6 h-6"></i>
                </div>
            </div>

            <div class="bg-white border border-gray-200/80 rounded-xl p-5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-amber-600">Warning (31 to 60 Days)</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $summary['within_60'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Plan for stock rotation</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <i data-lucide="clock" class="w-6 h-6"></i>
                </div>
            </div>

            <div class="bg-white border border-gray-200/80 rounded-xl p-5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">Good (Over 60 Days)</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $summary['safe'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Sufficient shelf life</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <i data-lucide="shield-check" class="w-6 h-6"></i>
                </div>
            </div>
        </div>

        <!-- Sections & Table (Ibinalik sa Orihinal) -->
        @php
            $sections = [
                'expired' => ['label' => 'Expired', 'dot' => 'bg-red-500', 'text' => 'text-red-600'],
                'within_60' => ['label' => 'Expiring within 60 days', 'dot' => 'bg-amber-500', 'text' => 'text-amber-600'],
                'safe' => ['label' => 'Safe Over 60 days', 'dot' => 'bg-green-500', 'text' => 'text-green-600'],
            ];
        @endphp

        @foreach($sections as $key => $section)
            @if(count($batches[$key]) > 0)
                <div class="mb-6">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-2 h-2 rounded-full {{ $section['dot'] }}"></span>
                        <h2 class="text-sm font-semibold {{ $section['text'] }}">
                            {{ $section['label'] }} ({{ count($batches[$key]) }})
                        </h2>
                    </div>

                    <div class="bg-white border border-gray-200/80 rounded-xl overflow-hidden shadow-sm">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm min-w-[700px]">
                                <thead>
                                    <tr class="bg-gray-50 border-b border-gray-200 text-left text-gray-500">
                                        <th class="px-4 py-3 font-medium whitespace-nowrap">Product</th>
                                        <th class="px-4 py-3 font-medium whitespace-nowrap">Category</th>
                                        <th class="px-4 py-3 font-medium whitespace-nowrap">Qty</th>
                                        <th class="px-4 py-3 font-medium whitespace-nowrap">Batch</th>
                                        <th class="px-4 py-3 font-medium whitespace-nowrap">Expiry Date</th>
                                        <th class="px-4 py-3 font-medium text-right whitespace-nowrap">Days</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($batches[$key] as $row)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-4 py-3 font-medium text-gray-800">{{ $row['product_name'] }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full text-white"
                                                      style="background-color: {{ $row['category_color'] }}">
                                                    {{ $row['category_icon'] }}
                                                    {{ $row['category_name'] }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $row['quantity'] }} {{ $row['unit_abbr'] }}</td>
                                            <td class="px-4 py-3 text-gray-400 text-xs whitespace-nowrap">{{ $row['batch_number'] }}</td>
                                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $row['expiry_date'] }}</td>
                                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                                @if($row['days'] < 0)
                                                    <span class="text-xs font-bold text-red-600">EXPIRED</span>
                                                @elseif($row['days'] <= 60)
                                                    <span class="text-xs font-bold text-amber-600">{{ $row['days'] }}d</span>
                                                @else
                                                    <span class="text-xs font-bold text-green-600">{{ $row['days'] }}d</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

    </div>
</x-app-layout>
