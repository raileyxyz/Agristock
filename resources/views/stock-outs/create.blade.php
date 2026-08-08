<x-app-layout>
    @php
        $productsForJs = $products->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'unit_abbr' => $p->unit->abbreviation ?? '',
        ]);
    @endphp

    <div class="max-w-2xl"
        x-data='{
            products: @json($productsForJs),
            stockData: @json($stockData),
            locations: @json($locations),
            form: {
                product_id: "{{ old('product_id') }}",
                location: "{{ old('location') }}",
                reason: "{{ old('reason') }}"
            },
            showSuccessModal: false,
            successMessage: "{{ addslashes(session('success', '')) }}",
            showErrorModal: false,
            errorMessage: "{{ addslashes(session('error', '')) }}",

            get selectedProduct() {
                return this.products.find(p => p.id == this.form.product_id) || null;
            },

            get availableLocations() {
                if (!this.form.product_id || !this.stockData[this.form.product_id]) return [];
                return Object.keys(this.stockData[this.form.product_id]);
            },

            get currentStock() {
                if (!this.form.product_id || !this.form.location) return null;
                return this.stockData[this.form.product_id]?.[this.form.location] ?? null;
            },

            get showTransferTo() {
                return this.form.reason === "Transfer";
            },

            get transferDestinations() {
                return this.locations.filter(l => l !== this.form.location);
            }
        }'
        x-init="
            @if(session('success'))
                showSuccessModal = true;
            @endif
            @if(session('error'))
                showErrorModal = true;
            @endif
        "
    >

        <div class="flex items-center justify-between mb-1">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Stock Out</h1>
                <p class="text-gray-400 text-sm mt-1">Record inventory usage, field application, or distribution.</p>
            </div>
        </div>

        @if($errors->any())
            <div class="mt-5 bg-red-50 border border-red-200 rounded-xl p-4 flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                </div>
                <div>
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

        <div class="bg-white border border-gray-200 rounded-xl p-6 mt-6">

            <div class="flex items-center gap-2.5 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3 mb-6">
                <i data-lucide="arrow-up-from-line" class="w-4 h-4 shrink-0"></i>
                Stock Out decreases quantity in current stock.
            </div>

            <form method="POST" action="{{ route('stock-outs.store') }}" class="space-y-5">
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
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                    @error('product_id')
                        <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Location -->
                <div x-show="form.product_id" x-collapse>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Current Location <span class="text-red-500">*</span>
                    </label>
                    <select name="location" x-model="form.location"
                            class="w-full border rounded-lg px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 transition-colors
                            {{ $errors->has('location') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                        <option value="">Select location...</option>
                        <template x-for="loc in availableLocations" :key="loc">
                            <option :value="loc" x-text="loc"></option>
                        </template>
                    </select>
                    <template x-if="form.product_id && availableLocations.length === 0">
                        <p class="text-xs text-amber-600 mt-1.5">No stock available for this product in any location.</p>
                    </template>
                    @error('location')
                        <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Available Stock (readonly info) -->
                <div x-show="currentStock" x-collapse>
                    <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm text-gray-600">
                        <span>Available stock: <span class="font-semibold text-gray-900" x-text="currentStock ? currentStock.available + ' ' + (selectedProduct?.unit_abbr ?? '') : ''"></span></span>
                        <span class="text-gray-300">·</span>
                        <span class="text-gray-400 font-mono text-xs" x-text="currentStock?.batch"></span>
                    </div>
                </div>

                <!-- Quantity -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Quantity to Remove <span class="text-xs text-gray-400" x-text="selectedProduct?.unit_abbr ? '(' + selectedProduct.unit_abbr + ')' : ''"></span>
                        <span class="text-red-500">*</span>
                    </label>
                    <input type="number" step="0.01" name="quantity" value="{{ old('quantity') }}" placeholder="0"
                           class="w-full border rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 transition-colors
                           {{ $errors->has('quantity') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                    @error('quantity')
                        <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Reason -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Reason <span class="text-red-500">*</span>
                    </label>
                    <select name="reason" x-model="form.reason"
                            class="w-full border rounded-lg px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 transition-colors
                            {{ $errors->has('reason') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                        <option value="">Select reason...</option>
                        @foreach(['Sale', 'Damaged', 'Expired', 'Transfer', 'Adjustment', 'Return to Supplier', 'Other'] as $reason)
                            <option value="{{ $reason }}" {{ old('reason') === $reason ? 'selected' : '' }}>{{ $reason }}</option>
                        @endforeach
                    </select>
                    @error('reason')
                        <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Transfer To — conditional -->
                <div x-show="showTransferTo" x-collapse>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Transfer To <span class="text-red-500">*</span>
                    </label>
                    <select name="transfer_to"
                            class="w-full border rounded-lg px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 transition-colors
                            {{ $errors->has('transfer_to') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                        <option value="">Select destination...</option>
                        <template x-for="loc in transferDestinations" :key="loc">
                            <option :value="loc" x-text="loc"></option>
                        </template>
                    </select>
                    @error('transfer_to')
                        <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
                    <textarea name="notes" rows="3" placeholder="e.g. Basal application — Field B, Block 3"
                              class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 transition-colors resize-none">{{ old('notes') }}</textarea>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                            class="bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        Record Stock Out
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
                    <h2 class="font-semibold text-gray-800 text-base mb-1.5">Stock out recorded</h2>
                    <p class="text-sm text-gray-500" x-text="successMessage || 'Stock removed successfully.'"></p>
                </div>
                <div class="flex items-center justify-center px-6 py-4 bg-gray-50 border-t border-gray-100">
                    <button @click="showSuccessModal = false"
                            class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                        Got it
                    </button>
                </div>
            </div>
        </div>

        <!-- Error Modal -->
        <div x-show="showErrorModal"
             x-transition:enter="transition-opacity ease-out duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-150"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
             style="display: none;" x-cloak>
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
                    <h2 class="font-semibold text-gray-800 text-base mb-1.5">Cannot record stock out</h2>
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
