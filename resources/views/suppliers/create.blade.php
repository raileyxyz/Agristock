<x-app-layout>
    @php
        $categoriesForJs = $categories->map(fn($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'icon' => $c->icon
        ]);
        $oldCategories = array_map('intval', old('supply_categories', []));
    @endphp

    <div
        x-data='{
            selected: @json($oldCategories),
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

        <!-- Header Section -->
        <div class="pb-4 sm:pb-5 border-b border-gray-200 dark:border-[#27332C] transition-colors duration-200 ease-in-out">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-gray-100 tracking-tight">Add Supplier</h1>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5 sm:mt-1">Register a new vendor or product supplier in your network.</p>
        </div>

        <!-- Form Layout -->
        <form method="POST" action="{{ route('suppliers.store') }}" class="mt-4 sm:mt-6">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 items-start">

                <!-- KALIWANG COLUMN: Main Inputs (2 Columns Wide sa Desktop) -->
                <div class="lg:col-span-2 space-y-4 sm:space-y-6">

                    <!-- Card 1: Primary Contact Information -->
                    <div class="bg-white dark:bg-[#111713] border border-gray-200 dark:border-[#27332C] rounded-xl p-4 sm:p-6 space-y-4 sm:space-y-5 shadow-sm transition-colors duration-200 ease-in-out">
                        <h2 class="text-sm sm:text-base font-semibold text-gray-800 dark:text-gray-200 border-b border-gray-100 dark:border-[#1F2B23] pb-2.5 sm:pb-3">Company & Contact Info</h2>

                        <!-- Company Name -->
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-200 mb-1 sm:mb-1.5">
                                Company / Supplier Name
                            </label>
                            <input type="text" name="company_name" value="{{ old('company_name') }}" placeholder="e.g. Pioneer Seeds Philippines"
                                   class="w-full border rounded-lg px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm dark:bg-[#0B0F0D] dark:text-gray-100 dark:placeholder:text-gray-500 focus:outline-none focus:ring-2 transition-colors
                                   {{ $errors->has('company_name') ? 'border-red-300 dark:border-red-500/60 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 dark:border-[#27332C] focus:ring-green-500/40 focus:border-green-500 dark:focus:border-green-400' }}">
                            @error('company_name')
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1 sm:mt-1.5 flex items-center gap-1">
                                    <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Contact Person & Phone -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-200 mb-1 sm:mb-1.5">
                                    Contact Person
                                </label>
                                <input type="text" name="contact_person" value="{{ old('contact_person') }}" placeholder="Full name"
                                       class="w-full border rounded-lg px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm dark:bg-[#0B0F0D] dark:text-gray-100 dark:placeholder:text-gray-500 focus:outline-none focus:ring-2 transition-colors
                                       {{ $errors->has('contact_person') ? 'border-red-300 dark:border-red-500/60 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 dark:border-[#27332C] focus:ring-green-500/40 focus:border-green-500 dark:focus:border-green-400' }}">
                                @error('contact_person')
                                    <p class="text-xs text-red-600 dark:text-red-400 mt-1 sm:mt-1.5 flex items-center gap-1">
                                        <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-200 mb-1 sm:mb-1.5">
                                    Phone Number
                                </label>
                                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+63 917 234 5678"
                                       class="w-full border rounded-lg px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm dark:bg-[#0B0F0D] dark:text-gray-100 dark:placeholder:text-gray-500 focus:outline-none focus:ring-2 transition-colors
                                       {{ $errors->has('phone') ? 'border-red-300 dark:border-red-500/60 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 dark:border-[#27332C] focus:ring-green-500/40 focus:border-green-500 dark:focus:border-green-400' }}">
                                @error('phone')
                                    <p class="text-xs text-red-600 dark:text-red-400 mt-1 sm:mt-1.5 flex items-center gap-1">
                                        <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <!-- Email Address -->
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-200 mb-1 sm:mb-1.5">Email Address</label>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="supplier@company.com"
                                   class="w-full border rounded-lg px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm dark:bg-[#0B0F0D] dark:text-gray-100 dark:placeholder:text-gray-500 focus:outline-none focus:ring-2 transition-colors
                                   {{ $errors->has('email') ? 'border-red-300 dark:border-red-500/60 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 dark:border-[#27332C] focus:ring-green-500/40 focus:border-green-500 dark:focus:border-green-400' }}">
                            @error('email')
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1 sm:mt-1.5 flex items-center gap-1">
                                    <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Physical Address -->
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-200 mb-1 sm:mb-1.5">Business Address</label>
                            <textarea name="address" rows="3" placeholder="Complete street address, city, province"
                                      class="w-full border border-gray-300 dark:border-[#27332C] dark:bg-[#0B0F0D] dark:text-gray-100 dark:placeholder:text-gray-500 rounded-lg px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 dark:focus:border-green-400 transition-colors resize-none">{{ old('address') }}</textarea>
                            @error('address')
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1 sm:mt-1.5 flex items-center gap-1">
                                    <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    <!-- Card 2: Categories & Additional Notes -->
                    <div class="bg-white dark:bg-[#111713] border border-gray-200 dark:border-[#27332C] rounded-xl p-4 sm:p-6 space-y-4 sm:space-y-5 shadow-sm transition-colors duration-200 ease-in-out">
                        <h2 class="text-sm sm:text-base font-semibold text-gray-800 dark:text-gray-200 border-b border-gray-100 dark:border-[#1F2B23] pb-2.5 sm:pb-3">Supply Classification</h2>

                        <!-- Categories Choice -->
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5 sm:mb-2">Supply Categories</label>
                            <div class="flex flex-wrap gap-2 sm:gap-2.5">
                                @foreach($categoriesForJs as $category)
                                    <label class="cursor-pointer select-none">
                                        <input type="checkbox" name="supply_categories[]" value="{{ $category['id'] }}"
                                               x-on:change="toggle({{ $category['id'] }})"
                                               :checked="selected.includes({{ $category['id'] }})"
                                               class="hidden">
                                        <span class="inline-flex items-center gap-1.5 sm:gap-2 text-xs sm:text-sm px-3 sm:px-3.5 py-1.5 sm:py-2 rounded-lg border transition-all font-medium"
                                              :class="selected.includes({{ $category['id'] }})
                                                ? 'bg-green-600 border-green-600 text-white shadow-sm'
                                                : 'bg-gray-50 border-gray-200 text-gray-700 hover:bg-gray-100 dark:bg-[#0B0F0D] dark:border-[#27332C] dark:text-gray-200 dark:hover:bg-[#161F1A]'">
                                            <span>{{ $category['icon'] }}</span>
                                            <span>{{ $category['name'] }}</span>
                                            <i data-lucide="check" class="w-3.5 h-3.5 ml-0.5 shrink-0" x-show="selected.includes({{ $category['id'] }})"></i>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @error('supply_categories')
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1.5 sm:mt-2 flex items-center gap-1">
                                    <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-200 mb-1 sm:mb-1.5">Notes <span class="text-gray-400 dark:text-gray-500 font-normal">(optional)</span></label>
                            <textarea name="notes" rows="3" placeholder="Payment terms, delivery schedules, or special agreements..."
                                      class="w-full border border-gray-300 dark:border-[#27332C] dark:bg-[#0B0F0D] dark:text-gray-100 dark:placeholder:text-gray-500 rounded-lg px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 dark:focus:border-green-400 transition-colors resize-none">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                </div>

                <!-- KANANG COLUMN: Guidance & Form Actions -->
                <div class="space-y-4 sm:space-y-6">

                    <!-- Information Card -->
                    <div class="bg-white dark:bg-[#111713] border border-gray-200 dark:border-[#27332C] rounded-xl p-4 sm:p-6 shadow-sm space-y-3 sm:space-y-4 transition-colors duration-200 ease-in-out">
                        <h2 class="text-sm sm:text-base font-semibold text-gray-800 dark:text-gray-200 border-b border-gray-100 dark:border-[#1F2B23] pb-2.5 sm:pb-3">Supplier Guidelines</h2>

                        <div class="flex items-start gap-2.5 sm:gap-3 bg-green-50/80 dark:bg-green-950/40 border border-green-200/60 dark:border-green-900/40 text-green-800 dark:text-green-300 rounded-lg p-3 sm:p-3.5">
                            <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-400 flex items-center justify-center shrink-0 mt-0.5">
                                <i data-lucide="truck" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                            </div>
                            <p class="text-[11px] sm:text-xs leading-relaxed">
                                <span class="font-semibold">Procurement Link:</span> Tagging appropriate supply categories ensures this vendor is suggested when creating future stock purchase orders.
                            </p>
                        </div>
                    </div>

                    <!-- Action Buttons Card -->
                    <div class="bg-white dark:bg-[#111713] border border-gray-200 dark:border-[#27332C] rounded-xl p-4 sm:p-6 shadow-sm space-y-2.5 sm:space-y-3 transition-colors duration-200 ease-in-out">
                        <button type="submit"
                                class="w-full bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg text-xs sm:text-sm font-medium transition-colors shadow-sm text-center active:scale-[0.99]">
                            Save Supplier
                        </button>
                        <button type="reset" @click="selected = []"
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
                    <h2 class="font-semibold text-gray-800 dark:text-gray-100 text-sm sm:text-base mb-1 sm:mb-1.5">Supplier saved</h2>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400" x-text="successMessage || 'Supplier added successfully.'"></p>
                </div>
                <div class="flex items-center justify-center px-5 sm:px-6 py-3.5 sm:py-4 bg-gray-50 dark:bg-[#111713] border-t border-gray-100 dark:border-[#27332C]">
                    <button @click="showSuccessModal = false"
                            class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg text-xs sm:text-sm font-medium transition-colors shadow-sm">
                        Got it
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
