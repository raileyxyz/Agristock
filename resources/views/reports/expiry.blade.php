<x-app-layout>
    <div class="space-y-5 sm:space-y-6">

        <!-- Header Section -->
        <div class="pb-4 sm:pb-5 border-b border-gray-200 dark:border-[#27332C] transition-colors duration-200 ease-in-out">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-gray-100 tracking-tight">Expiry Report</h1>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5 sm:mt-1">
                {{ $summary['tracked'] }} tracked · <span class="text-red-600 dark:text-red-400 font-medium">{{ $summary['expired'] }} expired</span> · <span class="text-amber-600 dark:text-amber-400 font-medium">{{ $summary['within_30'] + $summary['within_60'] }} expiring soon</span>
            </p>
        </div>

        <!-- Summary KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
            <!-- Expired Items -->
            <div class="bg-white dark:bg-[#111713] border border-gray-200/80 dark:border-[#27332C] rounded-xl p-4 sm:p-5 shadow-sm hover:shadow-md transition-all flex items-center justify-between">
                <div>
                    <p class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-red-600 dark:text-red-400">Expired Items</p>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1 sm:mt-1.5">{{ $summary['expired'] }}</p>
                    <p class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 mt-0.5">Past expiration date</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0">
                    <i data-lucide="circle-x" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </div>
            </div>

            <!-- Critical (Within 30 Days) -->
            <div class="bg-white dark:bg-[#111713] border border-gray-200/80 dark:border-[#27332C] rounded-xl p-4 sm:p-5 shadow-sm hover:shadow-md transition-all flex items-center justify-between">
                <div>
                    <p class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-red-500 dark:text-red-400">Critical (&le; 30 Days)</p>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1 sm:mt-1.5">{{ $summary['within_30'] }}</p>
                    <p class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 mt-0.5">Requires immediate action</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-red-50 dark:bg-red-950/40 text-red-500 dark:text-red-400 flex items-center justify-center shrink-0">
                    <i data-lucide="alarm-clock" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </div>
            </div>

            <!-- Warning (31 to 60 Days) -->
            <div class="bg-white dark:bg-[#111713] border border-gray-200/80 dark:border-[#27332C] rounded-xl p-4 sm:p-5 shadow-sm hover:shadow-md transition-all flex items-center justify-between">
                <div>
                    <p class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-amber-600 dark:text-amber-400">Warning (31-60 Days)</p>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1 sm:mt-1.5">{{ $summary['within_60'] }}</p>
                    <p class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 mt-0.5">Plan for stock rotation</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <i data-lucide="clock" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </div>
            </div>

            <!-- Safe (Over 60 Days) -->
            <div class="bg-white dark:bg-[#111713] border border-gray-200/80 dark:border-[#27332C] rounded-xl p-4 sm:p-5 shadow-sm hover:shadow-md transition-all flex items-center justify-between">
                <div>
                    <p class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-green-600 dark:text-green-400">Good (&gt; 60 Days)</p>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1 sm:mt-1.5">{{ $summary['safe'] }}</p>
                    <p class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 mt-0.5">Sufficient shelf life</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-emerald-50 dark:bg-green-950/40 text-green-600 dark:text-green-400 flex items-center justify-center shrink-0">
                    <i data-lucide="shield-check" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </div>
            </div>
        </div>

        <!-- Grouped Expiry Sections -->
        @php
            $sections = [
                'expired' => ['label' => 'Expired', 'dot' => 'bg-red-500', 'text' => 'text-red-600 dark:text-red-400'],
                'within_60' => ['label' => 'Expiring within 60 days', 'dot' => 'bg-amber-500', 'text' => 'text-amber-600 dark:text-amber-400'],
                'safe' => ['label' => 'Safe (Over 60 days)', 'dot' => 'bg-emerald-500', 'text' => 'text-emerald-600 dark:text-emerald-400'],
            ];
        @endphp

        <div class="space-y-6">
            @foreach($sections as $key => $section)
                @if(isset($batches[$key]) && count($batches[$key]) > 0)
                    <div>
                        <!-- Section Heading -->
                        <div class="flex items-center gap-2 mb-2.5">
                            <span class="w-2 h-2 rounded-full {{ $section['dot'] }}"></span>
                            <h2 class="text-xs sm:text-sm font-semibold {{ $section['text'] }}">
                                {{ $section['label'] }} ({{ count($batches[$key]) }})
                            </h2>
                        </div>

                        <!-- Data Table Container -->
                        <div class="bg-white dark:bg-[#111713] border border-gray-200 dark:border-[#27332C] rounded-xl shadow-sm overflow-hidden transition-colors duration-200 ease-in-out">
                            <div class="overflow-x-auto">
                                <table class="w-full text-xs sm:text-sm min-w-[750px]">
                                    <thead>
                                        <tr class="bg-gray-50 dark:bg-[#161F1A] border-b border-gray-200 dark:border-[#1F2B23] text-left text-gray-500 dark:text-gray-400 transition-colors duration-200 ease-in-out">
                                            <th class="px-3.5 sm:px-4 py-2.5 sm:py-3 font-medium whitespace-nowrap">Product</th>
                                            <th class="px-3.5 sm:px-4 py-2.5 sm:py-3 font-medium whitespace-nowrap">Category</th>
                                            <th class="px-3.5 sm:px-4 py-2.5 sm:py-3 font-medium whitespace-nowrap">Qty</th>
                                            <th class="px-3.5 sm:px-4 py-2.5 sm:py-3 font-medium whitespace-nowrap">Batch</th>
                                            <th class="px-3.5 sm:px-4 py-2.5 sm:py-3 font-medium whitespace-nowrap">Expiry Date</th>
                                            <th class="px-3.5 sm:px-4 py-2.5 sm:py-3 font-medium text-right whitespace-nowrap">Days Left</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-[#1F2B23]">
                                        @foreach($batches[$key] as $row)
                                            <tr class="hover:bg-gray-50/80 dark:hover:bg-[#161F1A]/60 transition-colors duration-200 ease-in-out">
                                                <td class="px-3.5 sm:px-4 py-2.5 sm:py-3 font-medium text-gray-800 dark:text-gray-200 whitespace-nowrap">{{ $row['product_name'] }}</td>
                                                <td class="px-3.5 sm:px-4 py-2.5 sm:py-3 whitespace-nowrap">
                                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-0.5 rounded-full text-white"
                                                          style="background-color: {{ $row['category_color'] }}">
                                                        {{ $row['category_icon'] }}
                                                        {{ $row['category_name'] }}
                                                    </span>
                                                </td>
                                                <td class="px-3.5 sm:px-4 py-2.5 sm:py-3 text-gray-600 dark:text-gray-200 whitespace-nowrap font-medium">
                                                    {{ $row['quantity'] }} <span class="text-xs text-gray-400 dark:text-gray-500 font-normal">{{ $row['unit_abbr'] }}</span>
                                                </td>
                                                <td class="px-3.5 sm:px-4 py-2.5 sm:py-3 text-gray-400 dark:text-gray-500 text-xs font-mono whitespace-nowrap">{{ $row['batch_number'] }}</td>
                                                <td class="px-3.5 sm:px-4 py-2.5 sm:py-3 text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ $row['expiry_date'] }}</td>
                                                <td class="px-3.5 sm:px-4 py-2.5 sm:py-3 text-right whitespace-nowrap">
                                                    @if($row['days'] < 0)
                                                        <span class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 border border-red-200/60 dark:border-red-900/40">EXPIRED</span>
                                                    @elseif($row['days'] <= 30)
                                                        <span class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 border border-red-200/60 dark:border-red-900/40">{{ $row['days'] }}d</span>
                                                    @elseif($row['days'] <= 60)
                                                        <span class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200/60 dark:border-amber-900/40">{{ $row['days'] }}d</span>
                                                    @else
                                                        <span class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-900/40">{{ $row['days'] }}d</span>
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

    </div>
</x-app-layout>
