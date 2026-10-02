<x-app-layout>
    @php
        $productsForJs = $products->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'unit_abbr' => $p->unit->abbreviation ?? '',
        ]);
    @endphp

    <div
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

        <!-- Header Section -->
        <div class="pb-4 sm:pb-5 border-b border-gray-200">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800 tracking-tight">Stock Out</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5 sm:mt-1">Record inventory usage, field application, loss, or transfer.</p>
        </div>

        <!-- Form Layout -->
        <form method="POST" action="{{ route('stock-outs.store') }}" class="mt-4 sm:mt-6">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 items-start">

                <!-- KALIWANG COLUMN: Main Inputs (2 Columns Wide sa Desktop) -->
                <div class="lg:col-span-2 space-y-4 sm:space-y-6">

                    <!-- Card 1: Product & Source Location -->
                    <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-6 space-y-4 sm:space-y-5 shadow-sm">
                        <h2 class="text-sm sm:text-base font-semibold text-gray-800 border-b border-gray-100 pb-2.5 sm:pb-3">Item & Source Location</h2>

                        <!-- Product Selection -->
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">
                                Product
                            </label>
                            <select name="product_id" x-model="form.product_id"
                                    class="w-full border rounded-lg px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm bg-white focus:outline-none focus:ring-2 transition-colors
                                    {{ $errors->has('product_id') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-red-500/40 focus:border-red-500' }}">
                                <option value="">Select product...</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">P{{ str_pad($product->id, 3, '0', STR_PAD_LEFT) }} — {{ $product->name }}</option>
                                @endforeach
                            </select>
                            @error('product_id')
                                <p class="text-xs text-red-600 mt-1 sm:mt-1.5 flex items-center gap-1">
                                    <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Current Location -->
                        <div x-show="form.product_id" x-collapse>
                            <div class="pt-1">
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">
                                    Current Location
                                </label>
                                <select name="location" x-model="form.location"
                                        class="w-full border rounded-lg px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm bg-white focus:outline-none focus:ring-2 transition-colors
                                        {{ $errors->has('location') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-red-500/40 focus:border-red-500' }}">
                                    <option value="">Select location...</option>
                                    <template x-for="loc in availableLocations" :key="loc">
                                        <option :value="loc" x-text="loc"></option>
                                    </template>
                                </select>
                                <template x-if="form.product_id && availableLocations.length === 0">
                                    <p class="text-xs text-amber-600 mt-1.5 flex items-center gap-1">
                                        <i data-lucide="alert-triangle" class="w-3.5 h-3.5 shrink-0"></i> No stock available for this product in any location.
                                    </p>
                                </template>
                                @error('location')
                                    <p class="text-xs text-red-600 mt-1 sm:mt-1.5 flex items-center gap-1">
                                        <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <!-- Available Stock Indicator -->
                        <div x-show="currentStock" x-collapse>
                            <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3 sm:px-4 py-2.5 sm:py-3 text-xs sm:text-sm text-gray-600">
                                <span>Available stock: <span class="font-semibold text-gray-900" x-text="currentStock ? currentStock.available + ' ' + (selectedProduct?.unit_abbr ?? '') : ''"></span></span>
                                <span class="text-gray-300">·</span>
                                <span class="text-gray-400 font-mono text-[11px] sm:text-xs" x-text="currentStock?.batch"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Removal Details & Reason -->
                    <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-6 space-y-4 sm:space-y-5 shadow-sm">
                        <h2 class="text-sm sm:text-base font-semibold text-gray-800 border-b border-gray-100 pb-2.5 sm:pb-3">Removal & Reason Details</h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                            <!-- Quantity -->
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">
                                    Quantity to Remove <span class="text-[11px] sm:text-xs text-gray-400 font-normal" x-text="selectedProduct?.unit_abbr ? '(' + selectedProduct.unit_abbr + ')' : ''"></span>
                                </label>
                                <input type="number" step="0.01" name="quantity" value="{{ old('quantity') }}" placeholder="0"
                                       class="w-full border rounded-lg px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm focus:outline-none focus:ring-2 transition-colors
                                       {{ $errors->has('quantity') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-red-500/40 focus:border-red-500' }}">
                                @error('quantity')
                                    <p class="text-xs text-red-600 mt-1 sm:mt-1.5 flex items-center gap-1">
                                        <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Reason -->
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">
                                    Reason
                                </label>
                                <select name="reason" x-model="form.reason"
                                        class="w-full border rounded-lg px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm bg-white focus:outline-none focus:ring-2 transition-colors
                                        {{ $errors->has('reason') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-red-500/40 focus:border-red-500' }}">
                                    <option value="">Select reason...</option>
                                    @foreach(\App\Enums\StockOutReason::values() as $reason)
                                        <option value="{{ $reason }}" {{ old('reason') === $reason ? 'selected' : '' }}>{{ $reason }}</option>
                                    @endforeach
                                </select>
                                @error('reason')
                                    <p class="text-xs text-red-600 mt-1 sm:mt-1.5 flex items-center gap-1">
                                        <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <!-- Transfer To Destination (Conditional) -->
                        <div x-show="showTransferTo" x-collapse>
                            <div class="pt-1">
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">
                                    Transfer To Destination
                                </label>
                                <select name="transfer_to"
                                        class="w-full border rounded-lg px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm bg-white focus:outline-none focus:ring-2 transition-colors
                                        {{ $errors->has('transfer_to') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-red-500/40 focus:border-red-500' }}">
                                    <option value="">Select destination...</option>
                                    <template x-for="loc in transferDestinations" :key="loc">
                                        <option :value="loc" x-text="loc"></option>
                                    </template>
                                </select>
                                @error('transfer_to')
                                    <p class="text-xs text-red-600 mt-1 sm:mt-1.5 flex items-center gap-1">
                                        <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">Notes <span class="text-gray-400 font-normal">(optional)</span></label>
                            <textarea name="notes" rows="3" placeholder="e.g. Basal application — Field B, Block 3"
                                      class="w-full border border-gray-300 rounded-lg px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-red-500/40 focus:border-red-500 transition-colors resize-none">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                </div>

                <!-- KANANG COLUMN: Notice Panel & Actions -->
                <div class="space-y-4 sm:space-y-6">

                    <!-- Context Alert Card -->
                    <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-6 shadow-sm space-y-3 sm:space-y-4">
                        <h2 class="text-sm sm:text-base font-semibold text-gray-800 border-b border-gray-100 pb-2.5 sm:pb-3">Stock Deduction Notice</h2>

                        <div class="flex items-start gap-2.5 sm:gap-3 bg-red-50/80 border border-red-200/60 text-red-800 rounded-lg p-3 sm:p-3.5">
                            <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-red-100 text-red-700 flex items-center justify-center shrink-0 mt-0.5">
                                <i data-lucide="arrow-up-from-line" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                            </div>
                            <p class="text-[11px] sm:text-xs leading-relaxed">
                                <span class="font-semibold">Inventory Deduction:</span> Recording a stock out will permanently reduce the recorded balance for the selected location.
                            </p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-6 shadow-sm space-y-2.5 sm:space-y-3">
                        <button type="submit"
                                class="w-full bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-lg text-xs sm:text-sm font-medium transition-colors shadow-sm text-center active:scale-[0.99]">
                            Record Stock Out
                        </button>
                        <button type="reset"
                                class="w-full bg-white hover:bg-gray-50 text-gray-600 border border-gray-200 px-5 py-2.5 rounded-lg text-xs sm:text-sm font-medium transition-colors text-center">
                            Clear Form
                        </button>
                    </div>

                </div>

            </div>
        </form>

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
                 class="bg-white rounded-2xl shadow-2xl w-full max-w-xs sm:max-w-sm overflow-hidden">
                <div class="px-5 sm:px-6 pt-5 sm:pt-6 pb-4 sm:pb-5 text-center">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-green-50 text-green-600 flex items-center justify-center mb-3 sm:mb-4 mx-auto">
                        <i data-lucide="check" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                    </div>
                    <h2 class="font-semibold text-gray-800 text-sm sm:text-base mb-1 sm:mb-1.5">Stock out recorded</h2>
                    <p class="text-xs sm:text-sm text-gray-500" x-text="successMessage || 'Stock removed successfully.'"></p>
                </div>
                <div class="flex items-center justify-center px-5 sm:px-6 py-3.5 sm:py-4 bg-gray-50 border-t border-gray-100">
                    <button @click="showSuccessModal = false"
                            class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg text-xs sm:text-sm font-medium transition-colors shadow-sm">
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
                 class="bg-white rounded-2xl shadow-2xl w-full max-w-xs sm:max-w-sm overflow-hidden">
                <div class="px-5 sm:px-6 pt-5 sm:pt-6 pb-4 sm:pb-5 text-center">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-red-50 text-red-600 flex items-center justify-center mb-3 sm:mb-4 mx-auto">
                        <i data-lucide="x" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                    </div>
                    <h2 class="font-semibold text-gray-800 text-sm sm:text-base mb-1 sm:mb-1.5">Cannot record stock out</h2>
                    <p class="text-xs sm:text-sm text-gray-500" x-text="errorMessage"></p>
                </div>
                <div class="flex items-center justify-center px-5 sm:px-6 py-3.5 sm:py-4 bg-gray-50 border-t border-gray-100">
                    <button @click="showErrorModal = false"
                            class="w-full sm:w-auto bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg text-xs sm:text-sm font-medium transition-colors shadow-sm">
                        Got it
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
