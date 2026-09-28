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
        class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6"
        x-data='{
            products: @json($productsForJs),
            form: {
                product_id: "{{ old('product_id', request('product_id')) }}"
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

        <!-- Header Section -->
        <div class="pb-4 sm:pb-5 border-b border-gray-200">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800 tracking-tight">Stock In</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5 sm:mt-1">Record incoming inventory — deliveries, transfers, or opening stock.</p>
        </div>

        <!-- Form Layout -->
        <form method="POST" action="{{ route('inventories.store') }}" class="mt-4 sm:mt-6">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 items-start">

                <!-- LEFT COLUMN: Main Details (2 Columns wide on desktop) -->
                <div class="lg:col-span-2 space-y-4 sm:space-y-6">

                    <!-- Card 1: Product & Quantity -->
                    <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-6 space-y-4 sm:space-y-5 shadow-sm">
                        <h2 class="text-sm sm:text-base font-semibold text-gray-800 border-b border-gray-100 pb-2.5 sm:pb-3">Item & Quantity Details</h2>

                        <!-- Product Selection -->
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">
                                Product
                            </label>
                            <select name="product_id" x-model="form.product_id"
                                    class="w-full border rounded-lg px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm bg-white focus:outline-none focus:ring-2 transition-colors
                                    {{ $errors->has('product_id') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
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

                        <!-- Quantity + Batch/Lot Number -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">
                                    Quantity <span class="text-[11px] sm:text-xs text-gray-400 font-normal" x-text="selectedUnitAbbr ? '(' + selectedUnitAbbr + ')' : ''"></span>
                                </label>
                                <input type="number" step="0.01" name="quantity" value="{{ old('quantity') }}" placeholder="0"
                                       class="w-full border rounded-lg px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm focus:outline-none focus:ring-2 transition-colors
                                       {{ $errors->has('quantity') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                                @error('quantity')
                                    <p class="text-xs text-red-600 mt-1 sm:mt-1.5 flex items-center gap-1">
                                        <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">
                                    Batch / Lot Number <span class="text-gray-400 font-normal">(optional)</span>
                                </label>
                                <input type="text" name="batch_number" value="{{ old('batch_number') }}" placeholder="Auto-generated"
                                       class="w-full border rounded-lg px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm focus:outline-none focus:ring-2 transition-colors
                                       {{ $errors->has('batch_number') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                                @error('batch_number')
                                    <p class="text-xs text-red-600 mt-1 sm:mt-1.5 flex items-center gap-1">
                                        <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <!-- Expiry Date — conditional -->
                        <div x-show="showExpiry" x-collapse>
                            <div class="pt-1">
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">
                                    Expiry Date
                                </label>
                                <input type="date" name="expiry_date" value="{{ old('expiry_date') }}"
                                    class="w-full border rounded-lg px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm focus:outline-none focus:ring-2 transition-colors
                                    {{ $errors->has('expiry_date') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                                @error('expiry_date')
                                    <p class="text-xs text-red-600 mt-1 sm:mt-1.5 flex items-center gap-1">
                                        <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Source & Logistics -->
                    <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-6 space-y-4 sm:space-y-5 shadow-sm">
                        <h2 class="text-sm sm:text-base font-semibold text-gray-800 border-b border-gray-100 pb-2.5 sm:pb-3">Logistics & Source</h2>

                        <!-- Storage Location + Supplier -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">
                                    Storage Location
                                </label>
                                <select name="location"
                                        class="w-full border rounded-lg px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm bg-white focus:outline-none focus:ring-2 transition-colors
                                        {{ $errors->has('location') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                                    <option value="">Select location...</option>
                                    @foreach($locations as $loc)
                                        <option value="{{ $loc }}" {{ old('location') === $loc ? 'selected' : '' }}>{{ $loc }}</option>
                                    @endforeach
                                </select>
                                @error('location')
                                    <p class="text-xs text-red-600 mt-1 sm:mt-1.5 flex items-center gap-1">
                                        <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">Supplier</label>
                                <select name="supplier_id"
                                        class="w-full border rounded-lg px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm bg-white focus:outline-none focus:ring-2 transition-colors
                                        {{ $errors->has('supplier_id') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                                    <option value="">Select supplier...</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->company_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('supplier_id')
                                    <p class="text-xs text-red-600 mt-1 sm:mt-1.5 flex items-center gap-1">
                                        <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <!-- Note -->
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">Note <span class="text-gray-400 font-normal">(optional)</span></label>
                            <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Add details (e.g. Invoice #10294, Delivery Truck A)..."
                                class="w-full border border-gray-300 rounded-lg px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 transition-colors">
                        </div>
                    </div>

                </div>

                <!-- RIGHT COLUMN: Info Box & Actions -->
                <div class="space-y-4 sm:space-y-6">

                    <!-- Context Info Card -->
                    <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-6 shadow-sm space-y-3 sm:space-y-4">
                        <h2 class="text-sm sm:text-base font-semibold text-gray-800 border-b border-gray-100 pb-2.5 sm:pb-3">Stock Entry Notice</h2>

                        <div class="flex items-start gap-2.5 sm:gap-3 bg-green-50 border border-green-200 text-green-800 rounded-lg p-3 sm:p-3.5">
                            <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-green-100 text-green-700 flex items-center justify-center shrink-0 mt-0.5">
                                <i data-lucide="arrow-down-to-line" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                            </div>
                            <p class="text-[11px] sm:text-xs leading-relaxed">
                                <span class="font-semibold">Inventory Update:</span> Saving this form will immediately update the total available stock for the selected product.
                            </p>
                        </div>
                    </div>

                    <!-- Actions Panel -->
                    <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-6 shadow-sm space-y-2.5 sm:space-y-3">
                        <button type="submit"
                                class="w-full bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg text-xs sm:text-sm font-medium transition-colors shadow-sm text-center active:scale-[0.99]">
                            Record Stock In
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
                class="bg-white rounded-2xl shadow-2xl w-full max-w-xs sm:max-w-sm overflow-hidden">

                <div class="px-5 sm:px-6 pt-5 sm:pt-6 pb-4 sm:pb-5 text-center">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-green-50 text-green-600 flex items-center justify-center mb-3 sm:mb-4 mx-auto">
                        <i data-lucide="check" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                    </div>
                    <h2 class="font-semibold text-gray-800 text-sm sm:text-base mb-1 sm:mb-1.5">Stock recorded</h2>
                    <p class="text-xs sm:text-sm text-gray-500" x-text="successMessage || 'Stock added successfully.'"></p>
                </div>

                <div class="flex items-center justify-center px-5 sm:px-6 py-3.5 sm:py-4 bg-gray-50 border-t border-gray-100">
                    <button @click="showSuccessModal = false"
                            class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg text-xs sm:text-sm font-medium transition-colors shadow-sm">
                        Got it
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
