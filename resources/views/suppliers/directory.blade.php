<x-app-layout>
    <div class="mb-1">
        <h1 class="text-2xl font-bold text-gray-900">Contact Directory</h1>
        <p class="text-gray-400 text-sm mt-1">Quick reference for all supplier contacts.</p>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden mt-6">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[850px]">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-left text-gray-500">
                        <th class="px-4 py-3 font-medium whitespace-nowrap">Supplier</th>
                        <th class="px-4 py-3 font-medium whitespace-nowrap">Contact Person</th>
                        <th class="px-4 py-3 font-medium whitespace-nowrap">Email</th>
                        <th class="px-4 py-3 font-medium whitespace-nowrap">Phone</th>
                        <th class="px-4 py-3 font-medium whitespace-nowrap">Supplies</th>
                        <th class="px-4 py-3 font-medium whitespace-nowrap">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($suppliers as $supplier)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-800 whitespace-nowrap">{{ $supplier->company_name }}</td>
                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $supplier->contact_person }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($supplier->email)
                                    <a href="mailto:{{ $supplier->email }}" class="text-blue-600 hover:underline">{{ $supplier->email }}</a>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $supplier->phone }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    @forelse($supplier->categories as $category)
                                        <span class="w-6 h-6 rounded flex items-center justify-center text-xs"
                                              style="background-color: {{ $category->icon_color }};" title="{{ $category->name }}">
                                            {{ $category->icon }}
                                        </span>
                                    @empty
                                        <span class="text-gray-300 text-xs">—</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="text-xs font-medium px-2.5 py-1 rounded-full
                                    {{ $supplier->status === 'Active' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ strtolower($supplier->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-14 text-center">
                                <i data-lucide="book-user" class="w-8 h-8 text-gray-300 mx-auto mb-2"></i>
                                <p class="text-gray-500 text-sm">No suppliers found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-app-layout>
