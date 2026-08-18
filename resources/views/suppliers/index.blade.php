<x-app-layout>
    <div
        x-data="{
            showArchiveModal: false,
            archiveTarget: { id: null, name: '' },
            showSuccessModal: false,
            successMessage: '{{ addslashes(session('success', '')) }}',

            openArchive(id, name) {
                this.archiveTarget = { id, name };
                this.showArchiveModal = true;
            }
        }"
        x-init="
            @if(session('success'))
                showSuccessModal = true;
            @endif
        ">

        <div class="flex items-center justify-between mb-1">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">All Suppliers</h1>
                <p class="text-gray-400 text-sm mt-1">{{ $suppliers->total() }} suppliers</p>
            </div>
            <a href="{{ route('suppliers.create') }}"
               class="bg-green-700 hover:bg-green-800 text-white px-4 py-2.5 rounded-lg text-sm font-medium flex items-center gap-2 transition-colors">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Add Supplier
            </a>
        </div>

        <!-- Search + Category + Status filters -->
        <form method="GET"
              x-data="{ search: '{{ addslashes(request('search')) }}' }"
              x-init="$watch('search', value => {
                  clearTimeout(window._supplierSearchDebounce);
                  window._supplierSearchDebounce = setTimeout(() => $el.submit(), 500);
              })"
              class="flex flex-col sm:flex-row gap-3 mt-6">

            <div class="relative flex-1">
                <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="search" x-model="search" placeholder="Search by company or contact person..."
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

            <div class="flex items-center gap-1.5 bg-gray-100 p-1 rounded-lg w-fit">
                <a href="{{ request()->fullUrlWithQuery(['status' => 'Active', 'page' => null]) }}"
                   class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors {{ request('status', 'Active') === 'Active' ? 'bg-white text-gray-800 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    Active
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'Archived', 'page' => null]) }}"
                   class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors {{ request('status') === 'Archived' ? 'bg-white text-gray-800 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    Archived
                </a>
            </div>
        </form>

        <!-- Supplier cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
            @forelse($suppliers as $supplier)
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <p class="font-semibold text-gray-800">{{ $supplier->company_name }}</p>
                        @if($supplier->status === 'Archived')
                            <span class="text-[10px] font-medium text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded shrink-0">Archived</span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-500">{{ $supplier->contact_person }}</p>
                    <p class="text-sm text-gray-400">{{ $supplier->phone }}</p>
                    @if($supplier->email)
                        <p class="text-sm text-gray-400 truncate">{{ $supplier->email }}</p>
                    @endif

                    <div class="flex flex-wrap gap-1.5 mt-3">
                        @foreach($supplier->categories as $category)
                            <span class="text-[11px] font-medium px-2 py-0.5 rounded-full text-white"
                                  style="background-color: {{ $category->icon_color }};">
                                {{ $category->icon }} {{ $category->name }}
                            </span>
                        @endforeach
                    </div>

                    <div class="flex items-center justify-end gap-1 mt-3 pt-3 border-t border-gray-100">
                        <a href="{{ route('suppliers.edit', $supplier) }}"
                           class="text-gray-400 hover:text-green-700 p-1.5 rounded-md hover:bg-gray-100">
                            <i data-lucide="pencil" class="w-4 h-4"></i>
                        </a>
                        @if($supplier->status === 'Active')
                            <button @click="openArchive({{ $supplier->id }}, '{{ addslashes($supplier->company_name) }}')"
                                    class="text-gray-400 hover:text-red-600 p-1.5 rounded-md hover:bg-gray-100">
                                <i data-lucide="archive" class="w-4 h-4"></i>
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-gray-400 text-sm col-span-full text-center py-10">No suppliers found.</p>
            @endforelse
        </div>

        @if($suppliers->hasPages())
            <div class="mt-6">{{ $suppliers->links() }}</div>
        @endif

        <!-- Archive Confirmation Modal -->
        <div x-show="showArchiveModal"
             x-transition:enter="transition-opacity ease-out duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-150"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
             style="display: none;" x-cloak>
            <div @click.outside="showArchiveModal = false"
                 x-show="showArchiveModal"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
                <div class="px-6 pt-6 pb-5">
                    <div class="w-11 h-11 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center mb-4">
                        <i data-lucide="archive" class="w-5 h-5"></i>
                    </div>
                    <h2 class="font-semibold text-gray-800 text-base mb-1.5">Archive supplier?</h2>
                    <p class="text-sm text-gray-500 leading-relaxed">
                        <span class="font-medium text-gray-700" x-text="archiveTarget.name"></span> will be moved to Archived.
                    </p>
                </div>
                <form method="POST" :action="`/suppliers/${archiveTarget.id}`"
                      class="flex items-center justify-end gap-2.5 px-6 py-4 bg-gray-50 border-t border-gray-100">
                    @csrf @method('DELETE')
                    <button type="button" @click="showArchiveModal = false"
                            class="text-gray-600 hover:bg-gray-200/70 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                        Archive
                    </button>
                </form>
            </div>
        </div>

        <!-- Success Modal -->
        <div x-show="showSuccessModal"
             x-transition:enter="transition-opacity ease-out duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-150"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
             style="display: none;" x-cloak>
            <div @click.outside="showSuccessModal = false"
                 x-show="showSuccessModal"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
                <div class="px-6 pt-6 pb-5 text-center">
                    <div class="w-12 h-12 rounded-full bg-green-50 text-green-600 flex items-center justify-center mb-4 mx-auto">
                        <i data-lucide="check" class="w-6 h-6"></i>
                    </div>
                    <h2 class="font-semibold text-gray-800 text-base mb-1.5">Done</h2>
                    <p class="text-sm text-gray-500" x-text="successMessage"></p>
                </div>
                <div class="flex items-center justify-center px-6 py-4 bg-gray-50 border-t border-gray-100">
                    <button @click="showSuccessModal = false"
                            class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                        Got it
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
