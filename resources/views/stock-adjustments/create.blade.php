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

        <!-- Header Section -->
        <div class="pb-4 sm:pb-5 border-b border-gray-200 dark:border-[#27332C] transition-colors duration-200 ease-in-out">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-gray-100 tracking-tight">Stock Adjustment</h1>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5 sm:mt-1">Correct recorded stock levels after a physical count, discrepancy, or loss.</p>
        </div>

        <!-- Form Layout -->
        <form method="POST" action="{{ route('stock-adjustments.store') }}" class="mt-4 sm:mt-6">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 items-start">

                <!-- KALIWANG COLUMN: Main Inputs (2 Columns Wide sa Desktop) -->
                <div class="lg:col-span-2 space-y-4 sm:space-y-6">

                    <!-- Card 1: Product & Batch Selection -->
                    <div class="bg-white dark:bg-[#111713] border border-gray-200 dark:border-[#27332C] rounded-xl p-4 sm:p-6 space-y-4 sm:space-y-5 shadow-sm transition-colors duration-200 ease-in-out">
                        <h2 class="text-sm sm:text-base font-semibold text-gray-800 dark:text-gray-200 border-b border-gray-100 dark:border-[#1F2B23] pb-2.5 sm:pb-3">Target Batch Selection</h2>

                        <!-- Product Selection -->
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-200 mb-1 sm:mb-1.5">
                                Product
                            </label>
                            <select name="product_id" x-model="form.product_id"
                                    @change="form.location = ''; form.inventory_id = ''; form.actual_quantity = ''"
                                    class="w-full border rounded-lg px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm bg-white dark:bg-[#0B0F0D] dark:text-gray-100 dark:[color-scheme:dark] focus:outline-none focus:ring-2 transition-colors
                                    {{ $errors->has('product_id') ? 'border-red-300 dark:border-red-500/60 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 dark:border-[#27332C] focus:ring-orange-500/40 focus:border-orange-500' }}">
                                <option value="">Select product...</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">P{{ str_pad($product->id, 3, '0', STR_PAD_LEFT) }} — {{ $product->name }}</option>
                                @endforeach
                            </select>
                            @error('product_id')
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1 sm:mt-1.5 flex items-center gap-1">
                                    <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Location Selection -->
                        <div x-show="form.product_id" x-collapse>
                            <div class="pt-1">
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-200 mb-1 sm:mb-1.5">
                                    Location
                                </label>
                                <select x-model="form.location" @change="form.inventory_id = ''; form.actual_quantity = ''"
                                        class="w-full border border-gray-300 dark:border-[#27332C] rounded-lg px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm bg-white dark:bg-[#0B0F0D] dark:text-gray-100 dark:[color-scheme:dark] focus:outline-none focus:ring-2 focus:ring-orange-500/40 focus:border-orange-500 transition-colors">
                                    <option value="">Select location...</option>
                                    <template x-for="loc in availableLocations" :key="loc">
                                        <option :value="loc" x-text="loc"></option>
                                    </template>
                                </select>
                                <template x-if="form.product_id && availableLocations.length === 0">
                                    <p class="text-xs text-amber-600 dark:text-amber-400 mt-1.5 flex items-center gap-1">
                                        <i data-lucide="alert-triangle" class="w-3.5 h-3.5 shrink-0"></i> No stock recorded for this product in any location.
                                    </p>
                                </template>
                            </div>
                        </div>

                        <!-- Batch Selection -->
                        <div x-show="form.location" x-collapse>
                            <div class="pt-1">
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-200 mb-1 sm:mb-1.5">
                                    Target Batch
                                </label>
                                <select name="inventory_id" x-model="form.inventory_id" @change="form.actual_quantity = ''"
                                        class="w-full border rounded-lg px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm bg-white dark:bg-[#0B0F0D] dark:text-gray-100 dark:[color-scheme:dark] focus:outline-none focus:ring-2 transition-colors
                                        {{ $errors->has('inventory_id') ? 'border-red-300 dark:border-red-500/60 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 dark:border-[#27332C] focus:ring-orange-500/40 focus:border-orange-500' }}">
                                    <option value="">Select batch...</option>
                                    <template x-for="batch in availableBatches" :key="batch.id">
                                        <option :value="batch.id" x-text="batch.batch_number + ' (' + batch.remaining_quantity + ' ' + (selectedProduct?.unit_abbr ?? '') + ')'"></option>
                                    </template>
                                </select>
                                @error('inventory_id')
                                    <p class="text-xs text-red-600 dark:text-red-400 mt-1 sm:mt-1.5 flex items-center gap-1">
                                        <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <!-- System Quantity Indicator -->
                        <div x-show="selectedBatch" x-collapse>
                            <div class="flex items-center gap-2 bg-gray-50 dark:bg-[#0B0F0D] border border-gray-200 dark:border-[#27332C] rounded-lg px-3 sm:px-4 py-2.5 sm:py-3 text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                                <span>Current system count: <span class="font-semibold text-gray-900 dark:text-gray-100" x-text="selectedBatch ? selectedBatch.remaining_quantity + ' ' + (selectedProduct?.unit_abbr ?? '') : ''"></span></span>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Actual Quantity & Reason -->
                    <div class="bg-white dark:bg-[#111713] border border-gray-200 dark:border-[#27332C] rounded-xl p-4 sm:p-6 space-y-4 sm:space-y-5 shadow-sm transition-colors duration-200 ease-in-out">
                        <h2 class="text-sm sm:text-base font-semibold text-gray-800 dark:text-gray-200 border-b border-gray-100 dark:border-[#1F2B23] pb-2.5 sm:pb-3">Correction Details</h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                            <!-- Actual Quantity -->
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-200 mb-1 sm:mb-1.5">
                                    Actual Physical Count <span class="text-[11px] sm:text-xs text-gray-400 dark:text-gray-500 font-normal" x-text="selectedProduct?.unit_abbr ? '(' + selectedProduct.unit_abbr + ')' : ''"></span>
                                </label>
                                <input type="number" step="0.01" name="actual_quantity" x-model="form.actual_quantity" placeholder="0"
                                       class="w-full border rounded-lg px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm dark:bg-[#0B0F0D] dark:text-gray-100 dark:placeholder:text-gray-500 dark:[color-scheme:dark] focus:outline-none focus:ring-2 transition-colors
                                       {{ $errors->has('actual_quantity') ? 'border-red-300 dark:border-red-500/60 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 dark:border-[#27332C] focus:ring-orange-500/40 focus:border-orange-500' }}">

                                @error('actual_quantity')
                                    <p class="text-xs text-red-600 dark:text-red-400 mt-1 sm:mt-1.5 flex items-center gap-1">
                                        <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                                    </p>
                                @enderror

                                <!-- Live Variance Difference Indicator -->
                                <template x-if="difference !== null">
                                    <div class="mt-2 text-xs font-semibold flex items-center gap-1.5 px-2.5 py-1 rounded-md border w-fit"
                                         :class="{
                                            'bg-green-50 text-green-700 border-green-200 dark:bg-green-950/40 dark:text-green-400 dark:border-green-900/40': difference > 0,
                                            'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/40 dark:text-red-400 dark:border-red-900/40': difference < 0,
                                            'bg-gray-50 text-gray-500 border-gray-200 dark:bg-[#0B0F0D] dark:text-gray-400 dark:border-[#27332C]': difference == 0
                                         }">
                                        <i data-lucide="trending-up" class="w-3.5 h-3.5 shrink-0" x-show="difference > 0"></i>
                                        <i data-lucide="trending-down" class="w-3.5 h-3.5 shrink-0" x-show="difference < 0"></i>
                                        <i data-lucide="minus-circle" class="w-3.5 h-3.5 shrink-0" x-show="difference == 0"></i>

                                        <span>
                                            <span x-text="difference > 0 ? '+' + difference : difference"></span>
                                            <span x-text="selectedProduct?.unit_abbr ? ' ' + selectedProduct.unit_abbr : ''"></span>
                                            <span x-text="difference > 0 ? ' (Surplus)' : (difference < 0 ? ' (Shortage)' : ' (No change)')"></span>
                                        </span>
                                    </div>
                                </template>
                            </div>

                            <!-- Reason -->
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-200 mb-1 sm:mb-1.5">
                                    Reason
                                </label>
                                <select name="reason"
                                        class="w-full border rounded-lg px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm bg-white dark:bg-[#0B0F0D] dark:text-gray-100 dark:[color-scheme:dark] focus:outline-none focus:ring-2 transition-colors
                                        {{ $errors->has('reason') ? 'border-red-300 dark:border-red-500/60 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 dark:border-[#27332C] focus:ring-orange-500/40 focus:border-orange-500' }}">
                                    <option value="">Select reason...</option>
                                    @foreach(\App\Enums\StockAdjustmentReason::values() as $reason)
                                        <option value="{{ $reason }}" {{ old('reason') === $reason ? 'selected' : '' }}>{{ $reason }}</option>
                                    @endforeach
                                </select>
                                @error('reason')
                                    <p class="text-xs text-red-600 dark:text-red-400 mt-1 sm:mt-1.5 flex items-center gap-1">
                                        <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-200 mb-1 sm:mb-1.5">Notes <span class="text-gray-400 dark:text-gray-500 font-normal">(optional)</span></label>
                            <textarea name="notes" rows="3" placeholder="Additional details regarding physical count or variance..."
                                      class="w-full border border-gray-300 dark:border-[#27332C] dark:bg-[#0B0F0D] dark:text-gray-100 dark:placeholder:text-gray-500 rounded-lg px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/40 focus:border-orange-500 transition-colors resize-none">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                </div>

                <!-- KANANG COLUMN: Notice Panel & Actions -->
                <div class="space-y-4 sm:space-y-6">

                    <!-- Adjustment Info Card -->
                    <div class="bg-white dark:bg-[#111713] border border-gray-200 dark:border-[#27332C] rounded-xl p-4 sm:p-6 shadow-sm space-y-3 sm:space-y-4 transition-colors duration-200 ease-in-out">
                        <h2 class="text-sm sm:text-base font-semibold text-gray-800 dark:text-gray-200 border-b border-gray-100 dark:border-[#1F2B23] pb-2.5 sm:pb-3">Adjustment Policy</h2>

                        <div class="flex items-start gap-2.5 sm:gap-3 bg-orange-50/80 dark:bg-orange-950/40 border border-orange-200/60 dark:border-orange-900/40 text-orange-800 dark:text-orange-300 rounded-lg p-3 sm:p-3.5">
                            <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-orange-100 dark:bg-orange-900/40 text-orange-700 dark:text-orange-400 flex items-center justify-center shrink-0 mt-0.5">
                                <i data-lucide="sliders-horizontal" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                            </div>
                            <p class="text-[11px] sm:text-xs leading-relaxed">
                                <span class="font-semibold">Stock Level Sync:</span> Submitting this adjustment directly overwrites the recorded stock count for the selected batch to reflect actual physical tally.
                            </p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="bg-white dark:bg-[#111713] border border-gray-200 dark:border-[#27332C] rounded-xl p-4 sm:p-6 shadow-sm space-y-2.5 sm:space-y-3 transition-colors duration-200 ease-in-out">
                        <button type="submit"
                                class="w-full bg-orange-600 hover:bg-orange-700 text-white px-5 py-2.5 rounded-lg text-xs sm:text-sm font-medium transition-colors shadow-sm text-center active:scale-[0.99]">
                            Record Adjustment
                        </button>
                        <button type="reset"
                                class="w-full bg-white dark:bg-[#111713] hover:bg-gray-50 dark:hover:bg-[#1C2621] text-gray-600 dark:text-gray-400 dark:hover:text-gray-100 border border-gray-200 dark:border-[#27332C] px-5 py-2.5 rounded-lg text-xs sm:text-sm font-medium transition-colors text-center">
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
             class="fixed inset-0 bg-black/50 dark:bg-[#0B0F0D]/80 backdrop-blur-sm z-50 flex items-center justify-center p-4"
             style="display: none;" x-cloak>
            <div @click.outside="showSuccessModal = false"
                 x-show="showSuccessModal"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 class="bg-white dark:bg-[#161D19] border border-transparent dark:border-[#27332C] rounded-2xl shadow-2xl w-full max-w-xs sm:max-w-sm overflow-hidden">
                <div class="px-5 sm:px-6 pt-5 sm:pt-6 pb-4 sm:pb-5 text-center">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-green-50 dark:bg-green-950/40 text-green-600 dark:text-green-400 flex items-center justify-center mb-3 sm:mb-4 mx-auto">
                        <i data-lucide="check" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                    </div>
                    <h2 class="font-semibold text-gray-800 dark:text-gray-100 text-sm sm:text-base mb-1 sm:mb-1.5">Adjustment recorded</h2>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400" x-text="successMessage || 'Stock adjustment saved successfully.'"></p>
                </div>
                <div class="flex items-center justify-center px-5 sm:px-6 py-3.5 sm:py-4 bg-gray-50 dark:bg-[#111713] border-t border-gray-100 dark:border-[#27332C]">
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
             class="fixed inset-0 bg-black/50 dark:bg-[#0B0F0D]/80 backdrop-blur-sm z-50 flex items-center justify-center p-4"
             style="display: none;" x-cloak>
            <div @click.outside="showErrorModal = false"
                 x-show="showErrorModal"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 class="bg-white dark:bg-[#161D19] border border-transparent dark:border-[#27332C] rounded-2xl shadow-2xl w-full max-w-xs sm:max-w-sm overflow-hidden">
                <div class="px-5 sm:px-6 pt-5 sm:pt-6 pb-4 sm:pb-5 text-center">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 flex items-center justify-center mb-3 sm:mb-4 mx-auto">
                        <i data-lucide="x" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                    </div>
                    <h2 class="font-semibold text-gray-800 dark:text-gray-100 text-sm sm:text-base mb-1 sm:mb-1.5">Cannot record adjustment</h2>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400" x-text="errorMessage"></p>
                </div>
                <div class="flex items-center justify-center px-5 sm:px-6 py-3.5 sm:py-4 bg-gray-50 dark:bg-[#111713] border-t border-gray-100 dark:border-[#27332C]">
                    <button @click="showErrorModal = false"
                            class="w-full sm:w-auto bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg text-xs sm:text-sm font-medium transition-colors shadow-sm">
                        Got it
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
