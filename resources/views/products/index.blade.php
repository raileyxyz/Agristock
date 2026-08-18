<x-app-layout>
    <div
        x-data="{
            showArchiveModal: false,
            archiveTarget: { id: null, name: '' },
            showSuccessModal: false,
            successMessage: '{{ addslashes(session('success', '')) }}',
            showViewModal: false,
            viewTarget: null,
            showEditModal: false,
            editForm: null,
            editErrors: {},
            originalForm: null,

            openArchive(id, name) {
                this.archiveTarget = { id, name };
                this.showArchiveModal = true;
            },

            openView(product) {
                this.viewTarget = product;
                this.showViewModal = true;
                this.$nextTick(() => lucide.createIcons());
            },

            openEdit(product) {
                this.editForm = { ...product };
                this.originalForm = { ...product };
                this.editErrors = {};
                this.showEditModal = true;
                this.$nextTick(() => lucide.createIcons());
            },

            hasChanges() {
                if (!this.editForm || !this.originalForm) return false;
                return JSON.stringify(this.editForm) !== JSON.stringify(this.originalForm);
            },

            closeEdit() {
                this.showEditModal = false;
                this.editForm = null;
                this.originalForm = null;
                this.editErrors = {};
            }
        }"
        x-init="
            @if(session('success'))
                showSuccessModal = true;
            @endif
            @if($errors->any() && old('id'))
                editForm = {
                    id: '{{ old('id') }}',
                    name: '{{ addslashes(old('name')) }}',
                    sku: '{{ addslashes(old('sku')) }}',
                    category_id: '{{ old('category_id') }}',
                    unit_id: '{{ old('unit_id') }}',
                    cost_price: '{{ old('cost_price') }}',
                    selling_price: '{{ old('selling_price') }}',
                    minimum_stock: '{{ old('minimum_stock') }}',
                    reorder_point: '{{ old('reorder_point') }}',
                    description: '{{ addslashes(old('description')) }}',
                    status: '{{ old('status') }}',
                    expiry_track: {{ old('expiry_track') ? 'true' : 'false' }}
                };
                editErrors = @js($errors->messages());
                showEditModal = true;
                $nextTick(() => lucide.createIcons());
            @endif
        ">

        <div class="flex items-center justify-between gap-3 mb-1">
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800">All Products</h1>

                <p class="mt-1 text-xs sm:text-sm text-gray-500 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                    <span>{{ $statistics['total'] }} Products</span>
                    <span class="text-gray-300">•</span>

                    <span class="text-green-600 font-medium">
                        {{ $statistics['active'] }} Active
                    </span>

                    <span class="text-gray-300">•</span>

                    <span>
                        {{ $statistics['archived'] }} Archived
                    </span>
                </p>

            </div>
            <a href="{{ route('products.create') }}"
            class="bg-green-600 hover:bg-green-700 text-white px-2.5 sm:px-3.5 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm font-medium flex items-center justify-center gap-1 sm:gap-1.5 transition-colors shrink-0">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                Add Product
            </a>
        </div>

        <!-- Search + Category + Status filters -->
        <form method="GET"
            x-data="{ search: '{{ addslashes(request('search')) }}' }"
            x-init="$watch('search', value => {
                clearTimeout(window._productSearchDebounce);
                window._productSearchDebounce = setTimeout(() => $el.submit(), 500);
            })"
            class="flex flex-col sm:flex-row sm:flex-wrap gap-3 mt-6">

            <div class="relative flex-1 min-w-0 sm:min-w-[200px]">
                <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="search" x-model="search" placeholder="Search by product name or SKU"
                    class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>

            <div class="relative w-full sm:w-auto">
                <select name="category_id" onchange="this.form.submit()"
                        class="w-full sm:w-auto appearance-none border border-gray-300 rounded-lg pl-3.5 pr-9 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <option value="">All Categories</option>
                    @foreach($categories ?? [] as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
            </div>

            <div class="relative w-full sm:w-auto">
                <select name="status" onchange="this.form.submit()"
                        class="w-full sm:w-auto appearance-none border border-gray-300 rounded-lg pl-3.5 pr-9 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <option value="Active" {{ request('status', 'Active') === 'Active' ? 'selected' : '' }}>Active</option>
                    <option value="Archived" {{ request('status') === 'Archived' ? 'selected' : '' }}>Archived</option>
                    <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All</option>
                </select>
                <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
            </div>
        </form>

        <!-- Products table -->
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden mt-4">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[1100px]">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-left text-gray-500">
                            <th class="px-3 py-2 font-medium whitespace-nowrap">ID</th>
                            <th class="px-3 py-2 font-medium whitespace-nowrap">SKU</th>
                            <th class="px-3 py-2 font-medium whitespace-nowrap">Product Name</th>
                            <th class="px-3 py-2 font-medium whitespace-nowrap">Category</th>
                            <th class="px-3 py-2 font-medium whitespace-nowrap">Unit</th>
                            <th class="px-3 py-2 font-medium text-right whitespace-nowrap">Cost</th>
                            <th class="px-3 py-2 font-medium text-right whitespace-nowrap">Price</th>
                            <th class="px-3 py-2 font-medium text-right whitespace-nowrap">Stock</th>
                            <th class="px-3 py-2 font-medium text-right whitespace-nowrap">Min</th>
                            <th class="px-3 py-2 font-medium text-right whitespace-nowrap">Reorder</th>
                            <th class="px-3 py-2 font-medium whitespace-nowrap">Expiry</th>
                            <th class="px-3 py-2 font-medium whitespace-nowrap">Status</th>
                            <th class="px-3 py-2 font-medium text-right whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($products as $product)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-3 py-2 text-gray-400 font-mono whitespace-nowrap">P{{ str_pad($product->id, 3, '0', STR_PAD_LEFT) }}</td>
                                <td class="px-3 py-2 text-gray-500 whitespace-nowrap">{{ $product->sku }}</td>
                                <td class="px-3 py-2 font-medium text-gray-800 max-w-[200px] truncate" title="{{ $product->name }}">{{ $product->name }}</td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium px-2 py-0.5 rounded-full text-white"
                                        style="background-color: {{ $product->category->icon_color ?? '#6b7280' }};">
                                        {{ $product->category->icon ?? '' }} {{ $product->category->name ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-gray-500 whitespace-nowrap">
                                    {{ $product->unit->abbreviation ?? '' }} ({{ $product->unit->name ?? '—' }})
                                </td>
                                <td class="px-3 py-2 text-right text-gray-600 whitespace-nowrap">₱{{ number_format($product->cost_price, 2) }}</td>
                                <td class="px-3 py-2 text-right text-gray-600 whitespace-nowrap">₱{{ number_format($product->selling_price, 2) }}</td>
                                <td class="px-3 py-2 text-right font-medium text-gray-800 whitespace-nowrap">{{ $product->current_stock ?? 0 }}</td>
                                <td class="px-3 py-2 text-right text-gray-500 whitespace-nowrap">{{ $product->minimum_stock }}</td>
                                <td class="px-3 py-2 text-right text-gray-500 whitespace-nowrap">{{ $product->reorder_point }}</td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    @if($product->expiry_track)
                                        <span class="inline-flex items-center gap-1 text-green-600 font-medium">
                                            <i data-lucide="check-circle" class="w-3 h-3"></i> Yes
                                        </span>
                                    @else
                                        <span class="text-gray-400">No</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <span class="text-xs font-medium px-2 py-0.5 rounded-full
                                        {{ $product->status === 'Active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                        {{ ($product->status) }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-0.5">
                                        <button @click="openView(@js($product))"
                                                title="View product"
                                                class="text-gray-400 hover:text-blue-700 p-1 rounded-md hover:bg-gray-100">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </button>

                                        <button @click="openEdit(@js($product))"
                                                title="Edit product"
                                                class="text-gray-400 hover:text-green-700 p-1 rounded-md hover:bg-gray-100">
                                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                        </button>

                                        @if($product->status === 'Active')
                                            <button @click="openArchive({{ $product->id }}, '{{ addslashes($product->name) }}')"
                                                    title="Archive product"
                                                    class="text-gray-400 hover:text-red-600 p-1 rounded-md hover:bg-gray-100">
                                                <i data-lucide="archive" class="w-3.5 h-3.5"></i>
                                            </button>
                                        @else
                                            <span title="Already archived" class="text-gray-200 p-1 cursor-not-allowed inline-flex">
                                                <i data-lucide="archive" class="w-3.5 h-3.5"></i>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="px-5 py-14 text-center">
                                    <i data-lucide="package" class="w-8 h-8 text-gray-300 mx-auto mb-2"></i>
                                    <p class="text-gray-500 text-sm">No products found.</p>
                                    <p class="text-gray-400 text-xs mt-1">Try a different search, or add your first product.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($products->hasPages())
            <div class="mt-6">
                {{ $products->links() }}
            </div>
        @endif

        <!-- Edit Product Modal -->
        <div x-show="showEditModal"
            x-transition:enter="transition-opacity ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
            style="display: none;"
            x-cloak>
            <div @click.outside="closeEdit()"
                x-show="showEditModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                class="bg-white rounded-2xl shadow-2xl w-full max-w-xl overflow-hidden max-h-[90vh] flex flex-col">

                <template x-if="editForm">
                    <form method="POST" :action="'/products/' + editForm.id" class="flex flex-col overflow-hidden">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" :value="editForm.id">

                        <!-- Header -->
                        <div class="flex items-start justify-between px-4 sm:px-6 pt-6 pb-5 shrink-0">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-full bg-green-50 text-green-600 flex items-center justify-center shrink-0">
                                    <i data-lucide="pencil" class="w-4.5 h-4.5"></i>
                                </div>
                                <div class="min-w-0">
                                    <h2 class="font-semibold text-gray-800 text-base leading-tight">Edit product</h2>
                                    <p class="text-xs text-gray-400 mt-0.5">Update this product's details</p>
                                </div>
                            </div>
                            <button type="button" @click="closeEdit()"
                                    class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg p-1.5 -mt-1 -mr-1 transition-colors shrink-0">
                                <i data-lucide="x" class="w-4.5 h-4.5"></i>
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="px-4 sm:px-6 pb-6 space-y-4 overflow-y-auto">

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Product Name</label>
                                    <input type="text" name="name" x-model="editForm.name"
                                        class="w-full border rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 transition-colors"
                                        :class="editErrors.name ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500'">
                                    <template x-if="editErrors.name">
                                        <p class="text-xs text-red-600 mt-1.5 flex items-center gap-1">
                                            <i data-lucide="circle-alert" class="w-3 h-3"></i> <span x-text="editErrors.name?.[0]"></span>
                                        </p>
                                    </template>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">SKU</label>
                                    <input type="text" name="sku" x-model="editForm.sku"
                                        class="w-full border rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 transition-colors"
                                        :class="editErrors.sku ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500'">
                                    <template x-if="editErrors.sku">
                                        <p class="text-xs text-red-600 mt-1.5 flex items-center gap-1">
                                            <i data-lucide="circle-alert" class="w-3 h-3"></i> <span x-text="editErrors.sku?.[0]"></span>
                                        </p>
                                    </template>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Category</label>
                                    <select name="category_id" x-model.number="editForm.category_id"
                                            class="w-full border rounded-lg px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 transition-colors"
                                            :class="editErrors.category_id ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500'">
                                        @foreach($categories ?? [] as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    <template x-if="editErrors.category_id">
                                        <p class="text-xs text-red-600 mt-1.5 flex items-center gap-1">
                                            <i data-lucide="circle-alert" class="w-3 h-3"></i> <span x-text="editErrors.category_id?.[0]"></span>
                                        </p>
                                    </template>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Unit</label>
                                    <select name="unit_id" x-model.number="editForm.unit_id"
                                            class="w-full border rounded-lg px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 transition-colors"
                                            :class="editErrors.unit_id ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500'">
                                        @foreach($units ?? [] as $unit)
                                            <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->abbreviation }})</option>
                                        @endforeach
                                    </select>
                                    <template x-if="editErrors.unit_id">
                                        <p class="text-xs text-red-600 mt-1.5 flex items-center gap-1">
                                            <i data-lucide="circle-alert" class="w-3 h-3"></i> <span x-text="editErrors.unit_id?.[0]"></span>
                                        </p>
                                    </template>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Cost Price</label>
                                    <input type="number" step="0.01" name="cost_price" x-model="editForm.cost_price"
                                        class="w-full border rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 transition-colors"
                                        :class="editErrors.cost_price ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500'">
                                    <template x-if="editErrors.cost_price">
                                        <p class="text-xs text-red-600 mt-1.5 flex items-center gap-1">
                                            <i data-lucide="circle-alert" class="w-3 h-3"></i> <span x-text="editErrors.cost_price?.[0]"></span>
                                        </p>
                                    </template>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Selling Price</label>
                                    <input type="number" step="0.01" name="selling_price" x-model="editForm.selling_price"
                                        class="w-full border rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 transition-colors"
                                        :class="editErrors.selling_price ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500'">
                                    <template x-if="editErrors.selling_price">
                                        <p class="text-xs text-red-600 mt-1.5 flex items-center gap-1">
                                            <i data-lucide="circle-alert" class="w-3 h-3"></i> <span x-text="editErrors.selling_price?.[0]"></span>
                                        </p>
                                    </template>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Minimum Stock</label>
                                    <input type="number" name="minimum_stock" x-model="editForm.minimum_stock"
                                        class="w-full border rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 transition-colors"
                                        :class="editErrors.minimum_stock ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500'">
                                    <template x-if="editErrors.minimum_stock">
                                        <p class="text-xs text-red-600 mt-1.5 flex items-center gap-1">
                                            <i data-lucide="circle-alert" class="w-3 h-3"></i> <span x-text="editErrors.minimum_stock?.[0]"></span>
                                        </p>
                                    </template>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Reorder Point</label>
                                    <input type="number" name="reorder_point" x-model="editForm.reorder_point"
                                        class="w-full border rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 transition-colors"
                                        :class="editErrors.reorder_point ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500'">
                                    <template x-if="editErrors.reorder_point">
                                        <p class="text-xs text-red-600 mt-1.5 flex items-center gap-1">
                                            <i data-lucide="circle-alert" class="w-3 h-3"></i> <span x-text="editErrors.reorder_point?.[0]"></span>
                                        </p>
                                    </template>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                                <textarea name="description" x-model="editForm.description" rows="3"
                                    class="w-full border rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 transition-colors resize-none"
                                    :class="editErrors.description ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500'"></textarea>
                                <template x-if="editErrors.description">
                                    <p class="text-xs text-red-600 mt-1.5 flex items-center gap-1">
                                        <i data-lucide="circle-alert" class="w-3 h-3"></i> <span x-text="editErrors.description?.[0]"></span>
                                    </p>
                                </template>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-3">
                                <label class="flex-1 flex items-center gap-2.5 bg-gray-50 border border-gray-200 rounded-lg p-3.5 cursor-pointer">
                                    <input type="checkbox" name="expiry_track" value="1" x-model="editForm.expiry_track"
                                        class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500/40">
                                    <span class="text-sm font-medium text-gray-700">Track expiry dates</span>
                                </label>

                                <div class="sm:w-40">
                                    <select name="status" x-model="editForm.status"
                                            class="w-full h-full border rounded-lg px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 transition-colors"
                                            :class="editErrors.status ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500'">
                                        <option value="Active">Active</option>
                                        <option value="Archived">Archived</option>
                                    </select>
                                    <template x-if="editErrors.status">
                                        <p class="text-xs text-red-600 mt-1.5 flex items-center gap-1">
                                            <i data-lucide="circle-alert" class="w-3 h-3"></i> <span x-text="editErrors.status?.[0]"></span>
                                        </p>
                                    </template>
                                </div>
                            </div>

                        </div>

                        <!-- Footer -->
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-2.5 px-4 sm:px-6 py-4 bg-gray-50 border-t border-gray-100 shrink-0">
                            <button type="button" @click="closeEdit()"
                                    class="text-gray-600 hover:bg-gray-200/70 px-4 py-2 rounded-lg text-sm font-medium transition-colors order-2 sm:order-1">
                                Cancel
                            </button>
                            <button type="submit"
                                    :disabled="!hasChanges()"
                                    :class="hasChanges() ? 'bg-green-600 hover:bg-green-700 cursor-pointer' : 'bg-gray-300 cursor-not-allowed'"
                                    class="text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm order-1 sm:order-2">
                                Save changes
                            </button>
                        </div>
                    </form>
                </template>
            </div>
        </div>

        <!-- Archive Confirmation Modal -->
        <div x-show="showArchiveModal"
            x-transition:enter="transition-opacity ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
            style="display: none;">
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
                    <h2 class="font-semibold text-gray-800 text-base mb-1.5">Archive product?</h2>
                    <p class="text-sm text-gray-500 leading-relaxed">
                        <span class="font-medium text-gray-700" x-text="archiveTarget.name"></span> will be moved to Archived and hidden from active use.
                    </p>
                </div>

                <form method="POST" :action="`/products/${archiveTarget.id}`"
                    class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-2.5 px-6 py-4 bg-gray-50 border-t border-gray-100">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="showArchiveModal = false"
                            class="text-gray-600 hover:bg-gray-200/70 px-4 py-2 rounded-lg text-sm font-medium transition-colors order-2 sm:order-1">
                        Cancel
                    </button>
                    <button type="submit"
                            class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm order-1 sm:order-2">
                        Archive
                    </button>
                </form>
            </div>
        </div>

        <!-- Success Modal -->
        <div x-show="showSuccessModal"
            x-transition:enter="transition-opacity ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
            style="display: none;">
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

        <!-- Quick View Modal -->
        <div x-show="showViewModal"
            x-transition:enter="transition-opacity ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
            style="display: none;">
            <div @click.outside="showViewModal = false"
                x-show="showViewModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden max-h-[90vh] flex flex-col"
                x-cloak>

                <template x-if="viewTarget">
                    <div class="flex flex-col overflow-hidden">

                        <!-- Header -->
                        <div class="flex items-start justify-between px-4 sm:px-6 pt-6 pb-5 shrink-0">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                    <i data-lucide="package" class="w-4.5 h-4.5"></i>
                                </div>
                                <div class="min-w-0">
                                    <h2 class="font-semibold text-gray-800 text-base leading-tight truncate" x-text="viewTarget.name"></h2>
                                    <p class="text-xs text-gray-400 mt-0.5" x-text="'SKU: ' + (viewTarget.sku || '—')"></p>
                                </div>
                            </div>
                            <button type="button" @click="showViewModal = false"
                                    class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg p-1.5 -mt-1 -mr-1 transition-colors shrink-0">
                                <i data-lucide="x" class="w-4.5 h-4.5"></i>
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="px-4 sm:px-6 pb-6 overflow-y-auto">
                            <div class="grid grid-cols-2 sm:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-gray-400 mb-1">Category</p>
                                    <p class="text-sm font-medium text-gray-800" x-text="viewTarget.category?.name || '—'"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 mb-1">Unit</p>
                                    <p class="text-sm font-medium text-gray-800" x-text="(viewTarget.unit?.abbreviation || '') + ' (' + (viewTarget.unit?.name || '—') + ')'"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 mb-1">Cost Price</p>
                                    <p class="text-sm font-medium text-gray-800" x-text="'₱' + Number(viewTarget.cost_price).toFixed(2)"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 mb-1">Selling Price</p>
                                    <p class="text-sm font-medium text-gray-800" x-text="'₱' + Number(viewTarget.selling_price).toFixed(2)"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 mb-1">Minimum Stock</p>
                                    <p class="text-sm font-medium text-gray-800" x-text="viewTarget.minimum_stock"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 mb-1">Reorder Point</p>
                                    <p class="text-sm font-medium text-gray-800" x-text="viewTarget.reorder_point"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 mb-1">Expiry Track</p>
                                    <p class="text-sm font-medium text-gray-800" x-text="viewTarget.expiry_track ? 'Yes' : 'No'"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 mb-1">Status</p>
                                    <span class="inline-block text-xs font-medium px-2 py-0.5 rounded-full"
                                        :class="viewTarget.status === 'Active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                                        x-text="viewTarget.status"></span>
                                </div>
                            </div>

                            <template x-if="viewTarget.description">
                                <div class="mt-4">
                                    <p class="text-xs text-gray-400 mb-1">Description</p>
                                    <p class="text-sm text-gray-600" x-text="viewTarget.description"></p>
                                </div>
                            </template>
                        </div>

                        <!-- Footer -->
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-2.5 px-4 sm:px-6 py-4 bg-gray-50 border-t border-gray-100 shrink-0">
                            <button type="button" @click="showViewModal = false"
                                    class="text-gray-600 hover:bg-gray-200/70 px-4 py-2 rounded-lg text-sm font-medium transition-colors order-2 sm:order-1">
                                Close
                            </button>
                            <button type="button"
                                    @click="showViewModal = false; openEdit(viewTarget)"
                                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm order-1 sm:order-2">
                                Edit Product
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

    </div>
</x-app-layout>
