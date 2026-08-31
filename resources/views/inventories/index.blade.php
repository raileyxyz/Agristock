<x-app-layout>
    @php
        $productsForJs = $products->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'expiry_track' => (bool) $p->expiry_track,
                'unit_abbr' => $p->unit->abbreviation ?? '',
            ];
        });
    @endphp

    <div
        x-data='{
            products: @json($productsForJs),
            showEditModal: false,
            showErrorModal: false,
            showSuccessModal: false,
            errorMessage: "{{ addslashes(session('error', '')) }}",
            successMessage: "{{ addslashes(session('success', '')) }}",
            editForm: null,
            editErrors: {},

            get selectedProduct() {
                return this.editForm
                    ? this.products.find(p => p.id == this.editForm.product_id) || null
                    : null;
            },

            get showExpiry() {
                return this.selectedProduct?.expiry_track ?? false;
            },

            openEdit(inventory) {
                this.editForm = {
                    id: inventory.id,
                    product_id: inventory.product_id,
                    quantity: parseFloat(inventory.remaining_quantity).toFixed(2),
                    batch_number: inventory.batch_number ?? "",
                    expiry_date: inventory.expiry_date ?? "",
                    location: inventory.location ?? "",
                    supplier_id: inventory.supplier_id ?? "",
                    notes: inventory.notes ?? "",
                    has_movement: inventory.has_movement
                };
                this.originalForm = { ...this.editForm };
                this.editErrors = {};
                this.showEditModal = true;
                this.$nextTick(() => lucide.createIcons());
            },

            closeEdit() {
                this.showEditModal = false;
                this.editForm = null;
                this.originalForm = null;
                this.editErrors = {};
            },

            hasChanges() {
                if (!this.editForm || !this.originalForm) return false;
                return JSON.stringify(this.editForm) !== JSON.stringify(this.originalForm);
            }
        }'
        x-init="
            @if(session('error'))
                showErrorModal = true;
            @endif
            @if(session('success'))
                showSuccessModal = true;
                $nextTick(() => lucide.createIcons());
            @endif
            @if($errors->any() && old('id'))
                editForm = {
                    id: '{{ old('id') }}',
                    product_id: '{{ old('product_id') }}',
                    quantity: '{{ old('quantity') }}',
                    batch_number: @js(old('batch_number', '')),
                    expiry_date: '{{ old('expiry_date') }}',
                    location: @js(old('location', '')),
                    supplier_id: '{{ old('supplier_id', '') }}',
                    notes: @js(old('notes', '')),
                    has_movement: false
                };
                editErrors = @js($errors->messages());
                showEditModal = true;
                $nextTick(() => lucide.createIcons());
            @endif
        "
    >

        <div class="flex items-center justify-between gap-3 mb-1">
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Current Stock</h1>
                <p class="text-gray-400 text-xs sm:text-sm mt-1 truncate">
                    {{ $summary['total_items'] }} items tracked across {{ $summary['total_locations'] }} locations
                </p>
            </div>
            @can('inventory.stock-in')
            <a href="{{ route('inventories.create') }}"
               class="bg-green-600 hover:bg-green-700 text-white px-2.5 sm:px-3.5 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm font-medium flex items-center justify-center gap-1 sm:gap-1.5 transition-colors shrink-0">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                Stock In
            </a>
            @endcan
        </div>

        <!-- Search + Category filter -->
        <form method="GET"
              x-data="{ search: '{{ addslashes(request('search')) }}' }"
              x-init="$watch('search', value => {
                  clearTimeout(window._inventorySearchDebounce);
                  window._inventorySearchDebounce = setTimeout(() => $el.submit(), 500);
              })"
              class="flex flex-col sm:flex-row sm:flex-wrap gap-3 mt-6">

            <div class="relative flex-1 min-w-0 sm:min-w-[200px]">
                <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="search" x-model="search" placeholder="Search products..."
                    class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>

            <div class="relative w-full sm:w-auto">
                <select name="category_id" onchange="this.form.submit()"
                        class="w-full sm:w-auto appearance-none border border-gray-300 rounded-lg pl-3.5 pr-9 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
            </div>

            <div class="relative w-full sm:w-auto">
                <select name="location" onchange="this.form.submit()"
                        class="w-full sm:w-auto appearance-none border border-gray-300 rounded-lg pl-3.5 pr-9 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <option value="">All Locations</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc }}" {{ request('location') == $loc ? 'selected' : '' }}>
                            {{ $loc }}
                        </option>
                    @endforeach
                </select>
                <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
            </div>

            <div class="relative w-full sm:w-auto">
                <select name="supplier_id" onchange="this.form.submit()"
                        class="w-full sm:w-auto appearance-none border border-gray-300 rounded-lg pl-3.5 pr-9 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->company_name }}
                        </option>
                    @endforeach
                </select>
                <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
            </div>
        </form>

        <!-- Inventory table -->
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden mt-4">
            <div class="overflow-x-auto">
                @php
                    $canManageUnits = Auth::user()->can('inventory.update') || Auth::user()->can('inventory.delete');
                @endphp
                <table class="w-full text-sm min-w-[950px]">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-left text-gray-500">
                            <th class="px-4 py-3 font-medium whitespace-nowrap">Product</th>
                            <th class="px-4 py-3 font-medium whitespace-nowrap">Category</th>
                            <th class="px-4 py-3 font-medium whitespace-nowrap">Qty</th>
                            <th class="px-4 py-3 font-medium whitespace-nowrap">Batch No.</th>
                            <th class="px-4 py-3 font-medium whitespace-nowrap">Location</th>
                            <th class="px-4 py-3 font-medium whitespace-nowrap">Expiry</th>
                            <th class="px-4 py-3 font-medium whitespace-nowrap">Status</th>
                            @if($canManageUnits)
                                <th class="px-4 py-3 font-medium text-right whitespace-nowrap">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($inventories as $inventory)
                            <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 max-w-[220px]">
                                <p class="font-medium text-gray-800 truncate" title="{{ $inventory->product->name }}">
                                    {{ $inventory->product->name }}
                                </p>
                                <p class="text-xs text-gray-400 truncate mt-0.2">
                                    {{ $inventory->supplier->company_name ?? 'No supplier' }}
                                </p>
                            </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium px-2 py-0.5 rounded-full text-white"
                                        style="background-color: {{ $inventory->product->category->icon_color ?? '#6b7280' }};">
                                        {{ $inventory->product->category->icon ?? '' }} {{ $inventory->product->category->name ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-semibold text-gray-900 whitespace-nowrap">
                                    {{ $inventory->formatted_quantity }}
                                    <span class="text-gray-400 font-normal">{{ $inventory->product->unit->abbreviation ?? '' }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-400 font-mono whitespace-nowrap">{{ $inventory->batch_number }}</td>
                                <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $inventory->location }}</td>
                                <td class="px-4 py-3 whitespace-nowrap {{ $inventory->expiry_display['class'] }}">
                                    {{ $inventory->expiry_display['label'] }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $inventory->stock_status['class'] }}">
                                        {{ $inventory->stock_status['label'] }}
                                    </span>
                                </td>
                                @if($canManageUnits)
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-0.5">
                                            @can('inventory.manage')
                                                <button @click="openEdit(@js([
                                                            'id' => $inventory->id,
                                                            'product_id' => $inventory->product_id,
                                                            'quantity' => $inventory->quantity,
                                                            'remaining_quantity' => $inventory->remaining_quantity,
                                                            'batch_number' => $inventory->batch_number,
                                                            'expiry_date' => $inventory->expiry_date?->format('Y-m-d'),
                                                            'location' => $inventory->location,
                                                            'supplier_id' => $inventory->supplier_id,
                                                            'notes' => $inventory->notes,
                                                            'has_movement' => $inventory->has_movement,
                                                        ]))"
                                                        title="Edit stock entry"
                                                        class="text-gray-400 hover:text-green-700 p-1 rounded-md hover:bg-gray-100">
                                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-14 text-center">
                                    <i data-lucide="package" class="w-8 h-8 text-gray-300 mx-auto mb-2"></i>
                                    <p class="text-gray-500 text-sm">No stock records found.</p>
                                    <p class="text-gray-400 text-xs mt-1">Record your first Stock In to get started.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($inventories->hasPages())
            <div class="mt-6">
                {{ $inventories->links() }}
            </div>
        @endif

        <!-- Edit Stock Modal -->
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
                 class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden max-h-[90vh] flex flex-col">

                <template x-if="editForm">
                    <form method="POST" :action="'/inventories/' + editForm.id" class="flex flex-col overflow-hidden">
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
                                    <h2 class="font-semibold text-gray-800 text-base leading-tight">Edit stock entry</h2>
                                    <p class="text-xs text-gray-400 mt-0.5">Update batch details or correct an entry error</p>
                                </div>
                            </div>
                            <button type="button" @click="closeEdit()"
                                    class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg p-1.5 -mt-1 -mr-1 transition-colors shrink-0">
                                <i data-lucide="x" class="w-4.5 h-4.5"></i>
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="px-4 sm:px-6 pb-6 space-y-4 overflow-y-auto">

                            <template x-if="editForm.has_movement">
                                <div class="flex items-center gap-2.5 bg-amber-50 border border-amber-200 text-amber-700 text-sm rounded-lg px-4 py-3 mb-4">
                                    <i data-lucide="lock" class="w-4 h-4 shrink-0"></i>
                                    This batch already has stock movements. Product, Quantity, and Location can no longer be changed.
                                </div>
                            </template>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Product</label>
                                <select name="product_id" x-model.number="editForm.product_id" :disabled="editForm.has_movement"
                                        class="w-full border rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 transition-colors"
                                        :class="editForm.has_movement ? 'bg-gray-50 text-gray-400 cursor-not-allowed border-gray-200' : (editErrors.product_id ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'bg-white border-gray-300 focus:ring-green-500/40 focus:border-green-500')">
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                                <template x-if="editForm.has_movement">
                                    <input type="hidden" name="product_id" :value="editForm.product_id">
                                </template>

                                <template x-if="editForm.has_movement">
                                    <p class="text-xs text-gray-400 mt-1.5 flex items-center gap-1">
                                        <i data-lucide="lock" class="w-3 h-3"></i> Product can't be changed — this batch already has stock movements.
                                    </p>
                                </template>
                                <template x-if="editErrors.product_id">
                                    <p class="text-xs text-red-600 mt-1.5"><span x-text="editErrors.product_id?.[0]"></span></p>
                                </template>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                        Quantity <span class="text-xs text-gray-400" x-text="selectedProduct?.unit_abbr ? '(' + selectedProduct.unit_abbr + ')' : ''"></span>
                                    </label>
                                    <input type="number" step="0.01" x-model="editForm.quantity" disabled
                                        class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm bg-gray-50 text-gray-400 cursor-not-allowed">
                                    <p class="text-xs text-gray-400 mt-1.5 flex items-center gap-1">
                                        <i data-lucide="info" class="w-3 h-3"></i> Use Stock Adjustment to change quantity.
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                        Batch / Lot Number <span class="text-gray-400 font-normal">(optional)</span>
                                    </label>
                                    <input type="text" name="batch_number" x-model="editForm.batch_number"
                                        class="w-full border rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 transition-colors"
                                        :class="editErrors.batch_number ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500'">
                                    <template x-if="editErrors.batch_number">
                                        <p class="text-xs text-red-600 mt-1.5"><span x-text="editErrors.batch_number?.[0]"></span></p>
                                    </template>
                                </div>
                            </div>

                            <div x-show="showExpiry" x-collapse>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Expiry Date</label>
                                <input type="date" name="expiry_date" x-model="editForm.expiry_date"
                                    class="w-full border rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 transition-colors"
                                    :class="editErrors.expiry_date ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500'">
                                <template x-if="editErrors.expiry_date">
                                    <p class="text-xs text-red-600 mt-1.5"><span x-text="editErrors.expiry_date?.[0]"></span></p>
                                </template>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Storage Location</label>
                                <select name="location" x-model="editForm.location" :disabled="editForm.has_movement"
                                        class="w-full border rounded-lg px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 transition-colors"
                                        :class="editForm.has_movement ? 'bg-gray-50 text-gray-400 cursor-not-allowed border-gray-200' : (editErrors.location ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'bg-white border-gray-300 focus:ring-green-500/40 focus:border-green-500')">
                                    @foreach($locations as $loc)
                                        <option value="{{ $loc }}">{{ $loc }}</option>
                                    @endforeach
                                </select>
                                <template x-if="editForm.has_movement">
                                    <input type="hidden" name="location" :value="editForm.location">
                                </template>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Supplier</label>
                                <select name="supplier_id" x-model="editForm.supplier_id"
                                        class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 transition-colors">
                                    <option value="">Select supplier...</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->company_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Note</label>
                                <input type="text" name="notes" x-model="editForm.notes"
                                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 transition-colors">
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

        <!-- Success Modal -->
        <div x-show="showSuccessModal"
            x-transition:enter="transition-opacity ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
            style="display: none;"
            x-cloak>
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
                    <h2 class="font-semibold text-gray-800 text-base mb-1.5">Changes saved</h2>
                    <p class="text-sm text-gray-500" x-text="successMessage || 'Stock entry has been updated successfully.'"></p>
                </div>

                <div class="flex items-center justify-center px-6 py-4 bg-gray-50 border-t border-gray-100">
                    <button @click="showSuccessModal = false"
                            class="bg-green-700 hover:bg-green-800 text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                        Got it
                    </button>
                </div>
            </div>
        </div>

        <!-- Error Modal -->
        <div x-show="showErrorModal"
            x-transition:enter="transition-opacity ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
            style="display: none;"
            x-cloak>
            <div @click.outside="showErrorModal = false"
                x-show="showErrorModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">

                <div class="px-6 pt-6 pb-5 text-center">
                    <div class="w-12 h-12 rounded-full bg-red-50 text-red-600 flex items-center justify-center mb-4 mx-auto">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </div>
                    <h2 class="font-semibold text-gray-800 text-base mb-1.5">Cannot update</h2>
                    <p class="text-sm text-gray-500" x-text="errorMessage"></p>
                </div>

                <div class="flex items-center justify-center px-6 py-4 bg-gray-50 border-t border-gray-100">
                    <button @click="showErrorModal = false"
                            class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                        Got it
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
