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
            form: {
                product_id: "{{ old('product_id') }}",
                location: "{{ old('location') }}",
                inventory_id: "{{ old('inventory_id') }}",
                actual_quantity: "{{ old('actual_quantity') }}"
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

            get availableBatches() {
                if (!this.form.product_id || !this.form.location) return [];
                return this.stockData[this.form.product_id]?.[this.form.location] ?? [];
            },

            get selectedBatch() {
                return this.availableBatches.find(b => b.id == this.form.inventory_id) || null;
            },

            get difference() {
                if (!this.selectedBatch || this.form.actual_quantity === "") return null;
                return (parseFloat(this.form.actual_quantity) - this.selectedBatch.remaining_quantity).toFixed(2);
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
                <h1 class="text-2xl font-bold text-gray-900">Stock Adjustment</h1>
                <p class="text-gray-400 text-sm mt-1">Correct stock levels after a physical count or loss.</p>
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

            <div class="flex items-center gap-2.5 bg-amber-50 border border-amber-200 text-amber-700 text-sm rounded-lg px-4 py-3 mb-6">
                <i data-lucide="sliders-horizontal" class="w-4 h-4 shrink-0"></i>
                Stock Adjustment corrects the recorded quantity to match your physical count.
            </div>

            <form method="POST" action="{{ route('stock-adjustments.store') }}" class="space-y-5">
                @csrf

                <!-- Product -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Product <span class="text-red-500">*</span>
                    </label>
                    <select name="product_id" x-model="form.product_id"
                            @change="form.location = ''; form.inventory_id = ''; form.actual_quantity = ''"
                            class="w-full border rounded-lg px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 transition-colors
                            {{ $errors->has('product_id') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                        <option value="">Select product...</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Location -->
                <div x-show="form.product_id" x-collapse>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Location <span class="text-red-500">*</span>
                    </label>
                    <select x-model="form.location" @change="form.inventory_id = ''; form.actual_quantity = ''"
                            class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 transition-colors">
                        <option value="">Select location...</option>
                        <template x-for="loc in availableLocations" :key="loc">
                            <option :value="loc" x-text="loc"></option>
                        </template>
                    </select>
                    <template x-if="form.product_id && availableLocations.length === 0">
                        <p class="text-xs text-amber-600 mt-1.5">No stock recorded for this product in any location.</p>
                    </template>
                </div>

                <!-- Batch -->
                <div x-show="form.location" x-collapse>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Batch <span class="text-red-500">*</span>
                    </label>
                    <select name="inventory_id" x-model="form.inventory_id" @change="form.actual_quantity = ''"
                            class="w-full border rounded-lg px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 transition-colors
                            {{ $errors->has('inventory_id') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                        <option value="">Select batch...</option>
                        <template x-for="batch in availableBatches" :key="batch.id">
                            <option :value="batch.id" x-text="batch.batch_number + ' (' + batch.remaining_quantity + ' ' + (selectedProduct?.unit_abbr ?? '') + ')'"></option>
                        </template>
                    </select>
                    @error('inventory_id')
                        <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- System Quantity (readonly) -->
                <div x-show="selectedBatch" x-collapse>
                    <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm text-gray-600">
                        <span>System quantity: <span class="font-semibold text-gray-900" x-text="selectedBatch ? selectedBatch.remaining_quantity + ' ' + (selectedProduct?.unit_abbr ?? '') : ''"></span></span>
                    </div>
                </div>

                <!-- Actual Quantity -->
                <div x-show="selectedBatch" x-collapse>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Actual Quantity <span class="text-xs text-gray-400" x-text="selectedProduct?.unit_abbr ? '(' + selectedProduct.unit_abbr + ')' : ''"></span>
                        <span class="text-red-500">*</span>
                    </label>
                    <input type="number" step="0.01" name="actual_quantity" x-model="form.actual_quantity" placeholder="0"
                           class="w-full border rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 transition-colors
                           {{ $errors->has('actual_quantity') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                    @error('actual_quantity')
                        <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                    @enderror

                    <template x-if="difference !== null">
                        <p class="text-xs mt-1.5 font-medium"
                           :class="difference > 0 ? 'text-green-600' : (difference < 0 ? 'text-red-600' : 'text-gray-400')">
                            <span x-text="difference > 0 ? '+' + difference : difference"></span>
                            <span x-text="difference > 0 ? ' surplus' : (difference < 0 ? ' shortage' : ' — no change')"></span>
                        </p>
                    </template>
                </div>

                <!-- Reason -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Reason <span class="text-red-500">*</span>
                    </label>
                    <select name="reason"
                            class="w-full border rounded-lg px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 transition-colors
                            {{ $errors->has('reason') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                        <option value="">Select reason...</option>
                        @foreach(['Physical Count', 'Damaged Goods', 'Theft/Loss', 'Expired Removal', 'Data Entry Error', 'Other'] as $reason)
                            <option value="{{ $reason }}" {{ old('reason') === $reason ? 'selected' : '' }}>{{ $reason }}</option>
                        @endforeach
                    </select>
                    @error('reason')
                        <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
                    <textarea name="notes" rows="3" placeholder="Optional details about this adjustment..."
                              class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 transition-colors resize-none">{{ old('notes') }}</textarea>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                            class="bg-amber-600 hover:bg-amber-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        Record Adjustment
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
                    <h2 class="font-semibold text-gray-800 text-base mb-1.5">Adjustment recorded</h2>
                    <p class="text-sm text-gray-500" x-text="successMessage || 'Stock adjustment saved successfully.'"></p>
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
                    <h2 class="font-semibold text-gray-800 text-base mb-1.5">Cannot record adjustment</h2>
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
