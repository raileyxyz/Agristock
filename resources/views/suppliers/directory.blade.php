<x-app-layout>
    <!-- Header Section -->
    <div class="pb-4 sm:pb-5 border-b border-gray-200">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-800 tracking-tight">Contact Directory</h1>
        <p class="text-xs sm:text-sm text-gray-500 mt-0.5 sm:mt-1">Quick reference for all supplier contacts.</p>
    </div>

    <!-- Table Container -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden mt-4 sm:mt-6">
        <div class="overflow-x-auto">
            <table class="w-full text-xs sm:text-sm min-w-[850px]">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-left text-gray-500">
                        <th class="px-3.5 sm:px-4 py-2.5 sm:py-3 font-medium whitespace-nowrap">Supplier</th>
                        <th class="px-3.5 sm:px-4 py-2.5 sm:py-3 font-medium whitespace-nowrap">Contact Person</th>
                        <th class="px-3.5 sm:px-4 py-2.5 sm:py-3 font-medium whitespace-nowrap">Email</th>
                        <th class="px-3.5 sm:px-4 py-2.5 sm:py-3 font-medium whitespace-nowrap">Phone</th>
                        <th class="px-3.5 sm:px-4 py-2.5 sm:py-3 font-medium whitespace-nowrap">Supplies</th>
                        <th class="px-3.5 sm:px-4 py-2.5 sm:py-3 font-medium whitespace-nowrap">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($suppliers as $supplier)
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <td class="px-3.5 sm:px-4 py-2.5 sm:py-3 font-medium text-gray-800 whitespace-nowrap">{{ $supplier->company_name }}</td>
                            <td class="px-3.5 sm:px-4 py-2.5 sm:py-3 text-gray-600 whitespace-nowrap">{{ $supplier->contact_person }}</td>
                            <td class="px-3.5 sm:px-4 py-2.5 sm:py-3 whitespace-nowrap">
                                @if($supplier->email)
                                    <a href="mailto:{{ $supplier->email }}" class="text-green-600 hover:text-green-700 hover:underline transition-colors">{{ $supplier->email }}</a>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-3.5 sm:px-4 py-2.5 sm:py-3 text-gray-600 whitespace-nowrap">{{ $supplier->phone }}</td>
                            <td class="px-3.5 sm:px-4 py-2.5 sm:py-3 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    @forelse($supplier->categories as $category)
                                        <span class="w-6 h-6 rounded flex items-center justify-center text-xs shrink-0"
                                              style="background-color: {{ $category->icon_color }};"
                                              title="{{ $category->name }}">
                                            {{ $category->icon }}
                                        </span>
                                    @empty
                                        <span class="text-gray-300 text-xs">—</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-3.5 sm:px-4 py-2.5 sm:py-3 whitespace-nowrap">
                                <span class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full
                                    {{ $supplier->status === \App\Enums\Status::ACTIVE ? 'bg-green-50 text-green-700 border border-green-200/60' : 'bg-gray-100 text-gray-600 border border-gray-200/60' }}">
                                    {{ $supplier->status->value }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 sm:py-14 text-center">
                                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-gray-50 text-gray-300 flex items-center justify-center mb-2.5 mx-auto">
                                    <i data-lucide="book-user" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                                </div>
                                <p class="text-xs sm:text-sm font-medium text-gray-700">No suppliers found</p>
                                <p class="text-[11px] sm:text-xs text-gray-400 mt-0.5">There are currently no supplier contacts registered in the system.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
