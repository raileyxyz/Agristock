<x-app-layout>
    <div x-data="{
            trackExpiry: {{ old('expiry_track') ? 'true' : 'false' }},
            status: '{{ old('status', 'Active') }}',
            showSuccessModal: false,
            successMessage: '{{ addslashes(session('success', '')) }}'
        }"
        x-init="
            @if(session('success'))
                showSuccessModal = true;
            @endif
        "
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6"
    >

        <!-- Header Section -->
        <div class="pb-4 sm:pb-5 border-b border-gray-200">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800 tracking-tight">Add New Product</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5 sm:mt-1">Create a new item in your product catalog.</p>
        </div>

        <!-- Form Layout -->
        <form method="POST" action="{{ route('products.store') }}" class="mt-4 sm:mt-6">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 items-start">

                <!-- LEFT / MAIN COLUMN -->
                <div class="lg:col-span-2 space-y-4 sm:space-y-6">

                    <!-- Card 1: Product Information -->
                    <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-6 space-y-4 sm:space-y-5 shadow-sm">
                        <h2 class="text-sm sm:text-base font-semibold text-gray-800 border-b border-gray-100 pb-2.5 sm:pb-3">Product Details</h2>

                        <!-- Product Name -->
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">
                                Product Name
                            </label>
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Hybrid Maize Seeds DK-8031"
                                class="w-full border rounded-lg px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm focus:outline-none focus:ring-2 transition-colors
                                {{ $errors->has('name') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                            @error('name')
                                <p class="text-xs text-red-600 mt-1 sm:mt-1.5 flex items-center gap-1">
                                    <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Category + Unit -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">
                                    Category
                                </label>
                                <select name="category_id"
                                        class="w-full border rounded-lg px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm bg-white focus:outline-none focus:ring-2 transition-colors
                                        {{ $errors->has('category_id') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                                    <option value="">Select category...</option>
                                    @foreach($categories ?? [] as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->icon }} {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <p class="text-xs text-red-600 mt-1 sm:mt-1.5 flex items-center gap-1">
                                        <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">
                                    Unit of Measurement
                                </label>
                                <select name="unit_id"
                                        class="w-full border rounded-lg px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm bg-white focus:outline-none focus:ring-2 transition-colors
                                        {{ $errors->has('unit_id') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                                    <option value="">Select unit...</option>
                                    @foreach($units ?? [] as $unit)
                                        <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->name }} ({{ $unit->abbreviation }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('unit_id')
                                    <p class="text-xs text-red-600 mt-1 sm:mt-1.5 flex items-center gap-1">
                                        <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">Description</label>
                            <textarea name="description" rows="3" placeholder="Brief description of the product..."
                                class="w-full border rounded-lg px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm focus:outline-none focus:ring-2 transition-colors resize-none
                                {{ $errors->has('description') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="text-xs text-red-600 mt-1 sm:mt-1.5 flex items-center gap-1">
                                    <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    <!-- Card 2: Pricing & Stock Limits -->
                    <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-6 space-y-4 sm:space-y-5 shadow-sm">
                        <h2 class="text-sm sm:text-base font-semibold text-gray-800 border-b border-gray-100 pb-2.5 sm:pb-3">Pricing & Inventory Alert Thresholds</h2>

                        <!-- Cost Price + Selling Price -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">
                                    Cost Price
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs sm:text-sm">₱</span>
                                    <input type="number" step="0.01" name="cost_price" value="{{ old('cost_price') }}" placeholder="0.00"
                                        class="w-full border rounded-lg pl-8 pr-3.5 py-2 sm:py-2.5 text-xs sm:text-sm focus:outline-none focus:ring-2 transition-colors
                                        {{ $errors->has('cost_price') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                                </div>
                                @error('cost_price')
                                    <p class="text-xs text-red-600 mt-1 sm:mt-1.5 flex items-center gap-1">
                                        <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                                    </p>
                                @else
                                    <p class="text-[11px] sm:text-xs text-gray-400 mt-1">What you pay the supplier</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">
                                    Selling Price
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs sm:text-sm">₱</span>
                                    <input type="number" step="0.01" name="selling_price" value="{{ old('selling_price') }}" placeholder="0.00"
                                        class="w-full border rounded-lg pl-8 pr-3.5 py-2 sm:py-2.5 text-xs sm:text-sm focus:outline-none focus:ring-2 transition-colors
                                        {{ $errors->has('selling_price') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                                </div>
                                @error('selling_price')
                                    <p class="text-xs text-red-600 mt-1 sm:mt-1.5 flex items-center gap-1">
                                        <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                                    </p>
                                @else
                                    <p class="text-[11px] sm:text-xs text-gray-400 mt-1">What customers pay</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Minimum Stock + Reorder Point -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">
                                    Minimum Stock Level
                                </label>
                                <input type="number" name="minimum_stock" value="{{ old('minimum_stock') }}" placeholder="e.g. 50"
                                    class="w-full border rounded-lg px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm focus:outline-none focus:ring-2 transition-colors
                                    {{ $errors->has('minimum_stock') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                                @error('minimum_stock')
                                    <p class="text-xs text-red-600 mt-1 sm:mt-1.5 flex items-center gap-1">
                                        <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                                    </p>
                                @else
                                    <p class="text-[11px] sm:text-xs text-gray-400 mt-1">Triggers a low stock alert when reached</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">
                                    Reorder Point
                                </label>
                                <input type="number" name="reorder_point" value="{{ old('reorder_point') }}" placeholder="e.g. 70"
                                    class="w-full border rounded-lg px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm focus:outline-none focus:ring-2 transition-colors
                                    {{ $errors->has('reorder_point') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                                @error('reorder_point')
                                    <p class="text-xs text-red-600 mt-1 sm:mt-1.5 flex items-center gap-1">
                                        <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                                    </p>
                                @else
                                    <p class="text-[11px] sm:text-xs text-gray-400 mt-1">Suggested quantity to trigger reorder</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                </div>

                <!-- RIGHT SIDEBAR: Organization, Note, and Submit Action -->
                <div class="space-y-4 sm:space-y-6">

                    <!-- Organization & Options Card -->
                    <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-6 space-y-4 sm:space-y-5 shadow-sm">
                        <h2 class="text-sm sm:text-base font-semibold text-gray-800 border-b border-gray-100 pb-2.5 sm:pb-3">Organization</h2>

                        <!-- SKU -->
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">
                                SKU <span class="text-gray-400 font-normal">(optional)</span>
                            </label>
                            <input type="text" name="sku" value="{{ old('sku') }}" placeholder="Auto-generated"
                                class="w-full border rounded-lg px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm focus:outline-none focus:ring-2 transition-colors
                                {{ $errors->has('sku') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                            @error('sku')
                                <p class="text-xs text-red-600 mt-1 sm:mt-1.5 flex items-center gap-1">
                                    <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Status Switcher -->
                        <div>
                            <span class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">Status</span>
                            <div class="flex items-center gap-1.5 bg-gray-50 border border-gray-200 p-1 sm:p-1.5 rounded-lg">
                                <button type="button" @click="status = 'Active'"
                                        :class="status === 'Active' ? 'bg-green-600 text-white font-medium shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                                        class="flex-1 px-3 py-2 sm:py-1.5 rounded-md text-xs transition-colors">
                                    Active
                                </button>
                                <button type="button" @click="status = 'Inactive'"
                                        :class="status === 'Inactive' ? 'bg-gray-600 text-white font-medium shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                                        class="flex-1 px-3 py-2 sm:py-1.5 rounded-md text-xs transition-colors">
                                    Inactive
                                </button>
                            </div>
                            <input type="hidden" name="status" :value="status">
                        </div>

                        <!-- Track Expiry Checkbox Card -->
                        <label class="flex items-start gap-3 bg-gray-50 border border-gray-200 rounded-lg p-3 sm:p-3.5 cursor-pointer hover:bg-gray-100/60 transition-colors">
                            <input type="checkbox" name="expiry_track" value="1" x-model="trackExpiry"
                                class="mt-0.5 w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500/40">
                            <span>
                                <span class="block text-xs sm:text-sm font-medium text-gray-700">Track expiry dates</span>
                                <span class="block text-[11px] sm:text-xs text-gray-400 mt-0.5 leading-snug">Enable for seeds, pesticides, biologicals, and feeds</span>
                            </span>
                        </label>
                    </div>

                    <!-- Note on Stock Card -->
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-3.5 sm:p-4">
                        <p class="text-xs text-gray-500 leading-relaxed">
                            <span class="font-medium text-gray-700">Note:</span> Stock quantity is managed in Inventory Management. To add opening stock, go to <span class="font-medium text-green-600">Inventory → Stock In</span> after saving the product.
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="pt-1 sm:pt-2 flex flex-col gap-2.5">
                        <button type="submit"
                                class="w-full bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg text-xs sm:text-sm font-medium transition-colors shadow-sm text-center active:scale-[0.99]">
                            Save Product
                        </button>
                        <a href="{{ route('products.index') }}"
                           class="w-full bg-white hover:bg-gray-50 text-gray-600 border border-gray-200 px-5 py-2.5 rounded-lg text-xs sm:text-sm font-medium transition-colors text-center">
                            Cancel
                        </a>
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
            style="display: none;">
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
                    <h2 class="font-semibold text-gray-800 text-sm sm:text-base mb-1">Product saved</h2>
                    <p class="text-xs sm:text-sm text-gray-500" x-text="successMessage || 'Product created successfully.'"></p>
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
