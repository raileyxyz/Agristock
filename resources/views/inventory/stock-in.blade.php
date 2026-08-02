<x-app-layout>
    <div x-data="{
            products: @json($products->map(fn($p) => ['id' => $p->id, 'sku' => $p->sku, 'name' => $p->name, 'expiry_track' => (bool) $p->expiry_track])),
            form: { product_id: '' },

            get selectedProduct() {
                return this.products.find(p => p.id == this.form.product_id) || null;
            },

            get showExpiry() {
                return this.selectedProduct?.expiry_track ?? false;
            }
         }"
         class="max-w-2xl">

        <h1 class="text-2xl font-bold text-gray-900">Stock In</h1>
        <p class="text-gray-400 text-sm mt-1">Record incoming inventory — deliveries, transfers, or opening stock.</p>

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

            <div class="flex items-center gap-2.5 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-3 mb-6">
                <i data-lucide="arrow-down-to-line" class="w-4 h-4 shrink-0"></i>
                Stock In increases quantity in current stock.
            </div>

            <form method="POST" action="{{ route('inventory.stock-in.store') }}" class="space-y-5">
                @csrf

                <!-- Product -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Product <span class="text-red-500">*</span>
                    </label>
                    <select name="product_id" x-model="form.product_id" required
                            class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 transition-colors">
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
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Quantity <span class="text-red-500">*</span>
                        </label>
                        <input type="number" step="0.01" name="quantity" placeholder="0"
                               class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 transition-colors">
                        @error('quantity')
                            <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Batch / Lot Number <span class="text-gray-400 font-normal">(optional)</span>
                        </label>
                        <input type="text" name="batch_number" placeholder="Auto-generated"
                               class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 transition-colors">
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
                    <input type="date" name="expiry_date"
                           class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 transition-colors">
                    @error('expiry_date')
                        <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Storage Location + Supplier -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Storage Location <span class="text-red-500">*</span>
                        </label>
                        <select name="location" required
                                class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 transition-colors">
                            <option value="">Select location...</option>
                            <option value="Main Warehouse">Main Warehouse</option>
                            <option value="Storage Room A">Storage Room A</option>
                            <option value="Storage Room B">Storage Room B</option>
                            <option value="Field Storage">Field Storage</option>
                        </select>
                        @error('location')
                            <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Supplier</label>
                        <select name="supplier_id"
                                class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 transition-colors">
                            <option value="">Select supplier...</option>
                            @foreach($suppliers ?? [] as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Note -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Note</label>
                    <input type="text" name="notes" placeholder="Optional note..."
                        class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 transition-colors">
                </div>

                <div class="flex items-center gap-3 pt-2">
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
    </div>
</x-app-layout>
