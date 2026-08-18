<x-app-layout>
    @php
        $productsForJs = $products->map(function ($p) {
            return [
                'id' => $p->id,
                'sku' => $p->sku,
                'name' => $p->name,
                'expiry_track' => (bool) $p->expiry_track,
                'unit_abbr' => $p->unit->abbreviation ?? '',
            ];
        });
    @endphp

    <div
        class="max-w-2xl"
        x-data='{
            products: @json($productsForJs),
            form: {
                product_id: "{{ old('product_id') }}"
            },
            showSuccessModal: false,
            successMessage: "{{ addslashes(session('success', '')) }}",

            get selectedProduct() {
                return this.products.find(
                    p => p.id == this.form.product_id
                ) || null;
            },

            get showExpiry() {
                return this.selectedProduct?.expiry_track ?? false;
            },

            get selectedUnitAbbr() {
                return this.selectedProduct?.unit_abbr ?? "";
            }

        }'
        x-init="
            @if(session('success'))
                showSuccessModal = true;
            @endif
        "
    >

        <div class="flex items-center justify-between mb-1">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Stock In</h1>
                <p class="text-gray-400 text-sm mt-1">Record incoming inventory — deliveries, transfers, or opening stock.</p>
            </div>
        </div>

        @if($errors->any())
            <div class="mt-5 bg-red-50 border border-red-200 rounded-xl p-4 flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-red-700">
                        {{ $errors->count() === 1 ? 'There is 1 problem with this form' : "There are {$errors->count()} problems with this form" }}
                    </p>
                    <ul class="text-sm text-red-600 mt-1 space-y-0.5 list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-6 mt-6">

            <div class="flex items-center gap-2.5 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-3 mb-6">
                <i data-lucide="arrow-down-to-line" class="w-4 h-4 shrink-0"></i>
                Stock In increases quantity in current stock.
            </div>

            <form method="POST" action="{{ route('inventories.store') }}" class="space-y-5">
                @csrf

                <!-- Product -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Product <span class="text-red-500">*</span>
                    </label>
                    <select name="product_id" x-model="form.product_id"
                            class="w-full border rounded-lg px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 transition-colors
                            {{ $errors->has('product_id') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                        <option value="">Select product...</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">P{{ str_pad($product->id, 3, '0', STR_PAD_LEFT) }} — {{ $product->name }}</option>
                        @endforeach
                    </select>
                    @error('product_id')
                        <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Quantity + Batch/Lot Number -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Quantity <span class="text-xs text-gray-400" x-text="selectedUnitAbbr ? '(' + selectedUnitAbbr + ')' : ''"></span>
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="number" step="0.01" name="quantity" value="{{ old('quantity') }}" placeholder="0"
                               class="w-full border rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 transition-colors
                               {{ $errors->has('quantity') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                        @error('quantity')
                            <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Batch / Lot Number <span class="text-gray-400 font-normal">(optional)</span>
                        </label>
                        <input type="text" name="batch_number" value="{{ old('batch_number') }}" placeholder="Auto-generated"
                               class="w-full border rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 transition-colors
                               {{ $errors->has('batch_number') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                        @error('batch_number')
                            <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Expiry Date — conditional -->
                <div x-show="showExpiry" x-collapse>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Expiry Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="expiry_date" value="{{ old('expiry_date') }}"
                        class="w-full border rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 transition-colors
                        {{ $errors->has('expiry_date') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                    @error('expiry_date')
                        <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Storage Location + Supplier -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Storage Location <span class="text-red-500">*</span>
                        </label>
                        <select name="location"
                                class="w-full border rounded-lg px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 transition-colors
                                {{ $errors->has('location') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                            <option value="">Select location...</option>
                            @foreach(['Main Warehouse', 'Storage Room A', 'Storage Room B', 'Field Storage'] as $loc)
                                <option value="{{ $loc }}" {{ old('location') === $loc ? 'selected' : '' }}>{{ $loc }}</option>
                            @endforeach
                        </select>
                        @error('location')
                            <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Supplier</label>
                        <select name="supplier_id" disabled
                                class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm bg-gray-50 text-gray-400 cursor-not-allowed">
                            <option value="">No suppliers yet</option>
                        </select>
                        <p class="text-xs text-gray-400 mt-1.5">Supplier management is coming soon.</p>
                    </div>
                </div>

                <!-- Note -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Note</label>
                    <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Optional note..."
                        class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 transition-colors">
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-2">
                    <button type="submit"
                            class="bg-green-700 hover:bg-green-800 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        Record Stock In
                    </button>
                    <button type="reset"
                            class="border border-gray-300 hover:bg-gray-50 text-gray-700 px-5 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        Clear
                    </button>
                </div>

            </form>
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
                    <h2 class="font-semibold text-gray-800 text-base mb-1.5">Stock recorded</h2>
                    <p class="text-sm text-gray-500" x-text="successMessage || 'Stock added successfully.'"></p>
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
