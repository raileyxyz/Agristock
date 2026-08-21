<x-app-layout>
    @php
        $categoriesForJs = $categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'icon' => $c->icon]);
    @endphp

    <div class="max-w-2xl"
        x-data='{
            selected: [],
            showSuccessModal: false,
            successMessage: "{{ addslashes(session('success', '')) }}",
            toggle(id) {
                this.selected.includes(id)
                    ? this.selected = this.selected.filter(x => x !== id)
                    : this.selected.push(id);
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
                <h1 class="text-2xl font-bold text-gray-900">Add Supplier</h1>
                <p class="text-gray-400 text-sm mt-1">Register a new supplier in your network.</p>
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
            <form method="POST" action="{{ route('suppliers.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Company / Supplier Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="company_name" value="{{ old('company_name') }}" placeholder="e.g. Pioneer Seeds Philippines"
                           class="w-full border rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 transition-colors
                           {{ $errors->has('company_name') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                    @error('company_name')
                        <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Contact Person <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="contact_person" value="{{ old('contact_person') }}" placeholder="Full name"
                               class="w-full border rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 transition-colors
                               {{ $errors->has('contact_person') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                        @error('contact_person')
                            <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Phone Number <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+63 9XXXXXXXXX"
                               class="w-full border rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 transition-colors
                               {{ $errors->has('phone') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                        @error('phone')
                            <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="supplier@company.com"
                           class="w-full border rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 transition-colors
                           {{ $errors->has('email') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                    @error('email')
                        <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Address</label>
                    <textarea name="address" rows="3" placeholder="Complete business address"
                              class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 transition-colors resize-none">{{ old('address') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Supply Categories</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($categoriesForJs as $category)
                            <label class="cursor-pointer">
                                <input type="checkbox" name="supply_categories[]" value="{{ $category['id'] }}"
                                       x-on:change="toggle({{ $category['id'] }})" class="hidden peer">
                                <span class="inline-flex items-center gap-1.5 text-sm px-3.5 py-2 rounded-lg border transition-colors
                                             border-gray-200 bg-gray-100 text-gray-700 peer-checked:bg-green-600 peer-checked:text-white peer-checked:border-green-600">
                                    {{ $category['icon'] }} {{ $category['name'] }}
                                </span>
                            </label>
                        @endforeach
                            @error('supply_categories')
                            <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
                    <textarea name="notes" rows="2" placeholder="Optional notes about this supplier..."
                              class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 transition-colors resize-none">{{ old('notes') }}</textarea>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                            class="bg-green-700 hover:bg-green-800 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        Save Supplier
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
                    <h2 class="font-semibold text-gray-800 text-base mb-1.5">Supplier saved</h2>
                    <p class="text-sm text-gray-500" x-text="successMessage || 'Supplier added successfully.'"></p>
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
