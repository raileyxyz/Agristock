<x-app-layout>
    <div x-data="{ trackExpiry: false, status: 'Active' }" class="max-w-3xl">

        <h1 class="text-2xl font-bold text-gray-800">Add New Product</h1>
        <p class="text-gray-400 text-sm mt-1">Note: Stock quantity is managed in Inventory Management, not here.</p>

        <form method="POST" action="{{ route('products.store') }}" class="mt-6">
            @csrf

            <div class="bg-white border border-gray-200 rounded-xl p-6 space-y-5">

                <!-- Product Name + SKU -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Product Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" placeholder="e.g. Hybrid Maize Seeds DK-8031"
                               class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 transition-colors">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            SKU <span class="text-gray-400 font-normal">(optional)</span>
                        </label>
                        <input type="text" name="sku" placeholder="Auto-generated"
                               class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 transition-colors">
                    </div>
                </div>

                <!-- Category + Unit -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Category <span class="text-red-500">*</span>
                        </label>
                        <select name="category_id"
                                class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 transition-colors">
                            <option value="">Select category...</option>
                            @foreach($categories ?? [] as $category)
                                <option value="{{ $category->id }}">{{ $category->icon }} {{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Unit of Measurement <span class="text-red-500">*</span>
                        </label>
                        <select name="unit_id"
                                class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 transition-colors">
                            <option value="">Select unit...</option>
                            @foreach($units ?? [] as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->abbreviation }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                    <textarea name="description" rows="3" placeholder="Brief description of the product..."
                              class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 transition-colors resize-none"></textarea>
                </div>

                <!-- Cost Price + Selling Price -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Cost Price <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₱</span>
                            <input type="number" step="0.01" name="cost_price" placeholder="0.00"
                                   class="w-full border border-gray-300 rounded-lg pl-8 pr-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 transition-colors">
                        </div>
                        <p class="text-xs text-gray-400 mt-1.5">What you pay the supplier</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Selling Price <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₱</span>
                            <input type="number" step="0.01" name="selling_price" placeholder="0.00"
                                   class="w-full border border-gray-300 rounded-lg pl-8 pr-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 transition-colors">
                        </div>
                        <p class="text-xs text-gray-400 mt-1.5">What customers pay</p>
                    </div>
                </div>

                <!-- Minimum Stock + Reorder Point -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Minimum Stock Level <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="minimum_stock" placeholder="e.g. 50"
                               class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 transition-colors">
                        <p class="text-xs text-gray-400 mt-1.5">Triggers a low stock alert when reached</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Reorder Point <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="reorder_point" placeholder="e.g. 70"
                               class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 transition-colors">
                        <p class="text-xs text-gray-400 mt-1.5">Suggested quantity to trigger reorder</p>
                    </div>
                </div>

                <!-- Track expiry + Status -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <label class="flex-1 flex items-start gap-3 bg-gray-50 border border-gray-200 rounded-lg p-4 cursor-pointer">
                        <input type="checkbox" name="expiry_track" value="1" x-model="trackExpiry"
                               class="mt-0.5 w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500/40">
                        <span>
                            <span class="block text-sm font-medium text-gray-700">Track expiry dates for this product</span>
                            <span class="block text-xs text-gray-400 mt-0.5">Enable for seeds, pesticides, biologicals, and feeds</span>
                        </span>
                    </label>

                    <div class="sm:w-48 bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <span class="block text-sm font-medium text-gray-700 mb-2">Status</span>
                        <div class="flex items-center gap-1.5 bg-white border border-gray-200 p-1 rounded-lg">
                            <button type="button" @click="status = 'Active'"
                                    :class="status === 'Active' ? 'bg-green-600 text-white' : 'text-gray-500 hover:text-gray-700'"
                                    class="flex-1 px-3 py-1.5 rounded-md text-xs font-medium transition-colors">
                                Active
                            </button>
                            <button type="button" @click="status = 'Inactive'"
                                    :class="status === 'Inactive' ? 'bg-gray-600 text-white' : 'text-gray-500 hover:text-gray-700'"
                                    class="flex-1 px-3 py-1.5 rounded-md text-xs font-medium transition-colors">
                                Inactive
                            </button>
                        </div>
                        <input type="hidden" name="status" :value="status">
                    </div>
                </div>

            </div>

            <!-- Actions -->
            <div class="flex items-center gap-3 mt-5">
                <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition-colors">
                    Save Product
                </button>
                <a href="{{ route('products.index') }}"
                   class="border border-gray-300 hover:bg-gray-50 text-gray-700 px-5 py-2.5 rounded-lg text-sm font-medium transition-colors">
                    Cancel
                </a>
            </div>
        </form>

        <p class="text-center text-xs text-gray-400 mt-6">
            To add opening stock, go to <span class="font-medium text-green-700">Inventory → Stock In</span> after saving the product.
        </p>

    </div>
</x-app-layout>
