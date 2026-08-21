<x-app-layout>
    <div
        x-data="{
            showEditModal: false,
            editForm: null,
            originalForm: null,
            editErrors: {},
            showArchiveModal: false,
            archiveTarget: { id: null, name: '' },
            showSuccessModal: false,
            successMessage: '{{ addslashes(session('success', '')) }}',

            openEdit(supplier) {
                this.editForm = { ...supplier };
                this.originalForm = { ...supplier, category_ids: [...supplier.category_ids] };
                this.editErrors = {};
                this.showEditModal = true;
                this.$nextTick(() => lucide.createIcons());
            },

            closeEdit() {
                this.showEditModal = false;
                this.editForm = null;
                this.originalForm = null;
                this.editErrors = {};
            },

            hasChanges() {
                if (!this.editForm || !this.originalForm) return false;
                return JSON.stringify(this.editForm) !== JSON.stringify(this.originalForm);
            },

            toggleCategory(id) {
                const idx = this.editForm.category_ids.indexOf(id);
                idx === -1 ? this.editForm.category_ids.push(id) : this.editForm.category_ids.splice(idx, 1);
            },

            openArchive(id, name) {
                this.archiveTarget = { id, name };
                this.showArchiveModal = true;
            }
        }"
        x-init="
            @if($errors->any() && old('editing_id'))
                editForm = {
                    id: {{ (int) old('editing_id') }},
                    company_name: @js(old('company_name')),
                    contact_person: @js(old('contact_person')),
                    phone: @js(old('phone')),
                    email: @js(old('email')),
                    address: @js(old('address')),
                    notes: @js(old('notes')),
                    status: @js(old('status', 'Active')),
                    category_ids: @js(collect(old('supply_categories', []))->map(fn($id) => (int) $id)->values()),
                };
                originalForm = { ...editForm, category_ids: [...editForm.category_ids] };
                editErrors = @js($errors->toArray());
                showEditModal = true;
                $nextTick(() => lucide.createIcons());
            @elseif(session('success'))
                showSuccessModal = true;
            @endif
        ">

        <div class="flex items-center justify-between gap-3 mb-1">
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900">All Suppliers</h1>
                <p class="mt-1 text-xs sm:text-sm text-gray-500 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                    <span>{{ $statistics['total'] }} Suppliers</span>

                    <span class="text-gray-300">•</span>

                    <span class="text-green-600 font-medium">
                        {{ $statistics['active'] }} Active
                    </span>

                    <span class="text-gray-300">•</span>

                    <span>
                        {{ $statistics['archived'] }} Inactive
                    </span>
                </p>
            </div>
            <a href="{{ route('suppliers.create') }}"
               class="bg-green-700 hover:bg-green-800 text-white px-2.5 sm:px-3.5 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm font-medium flex items-center justify-center gap-1 sm:gap-1.5 transition-colors shrink-0">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                Add Supplier
            </a>
        </div>

        <!-- Search + Status filter -->
        <form method="GET"
              x-data="{ search: '{{ addslashes(request('search')) }}' }"
              x-init="$watch('search', value => {
                  clearTimeout(window._supplierSearchDebounce);
                  window._supplierSearchDebounce = setTimeout(() => $el.submit(), 500);
              })"
              class="flex flex-col sm:flex-row sm:flex-wrap gap-3 mt-6">

            <div class="relative flex-1 min-w-0 sm:min-w-[200px]">
                <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="search" x-model="search" placeholder="Search suppliers..."
                       class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>

            <div class="relative w-full sm:w-auto">
                <select name="category_id" onchange="this.form.submit()"
                        class="w-full sm:w-auto appearance-none border border-gray-300 rounded-lg pl-3.5 pr-9 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
            </div>

            <div class="relative w-full sm:w-auto">
                <select name="status" onchange="this.form.submit()"
                        class="w-full sm:w-auto appearance-none border border-gray-300 rounded-lg pl-3.5 pr-9 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <option value="Active" {{ request('status', 'Active') === 'Active' ? 'selected' : '' }}>Active</option>
                    <option value="Archived" {{ request('status') === 'Archived' ? 'selected' : '' }}>Archived</option>
                    <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All</option>
                </select>
                <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
            </div>
        </form>

        <!-- Supplier cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
            @forelse($suppliers as $supplier)
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <div class="flex items-center gap-2 min-w-0">
                            <p class="text-md font-semibold text-gray-800 truncate">
                                {{ $supplier->company_name }}
                            </p>

                            <span class="text-[10px] font-medium px-2 py-0.5 rounded-full shrink-0
                                {{ $supplier->status === 'Active'
                                    ? 'bg-green-100 text-green-800'
                                    : 'bg-gray-100 text-gray-600' }}">
                                {{ $supplier->status }}
                            </span>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2 shrink-0">

                            <!-- Edit -->
                            <button
                                @click="openEdit(@js([
                                    'id' => $supplier->id,
                                    'company_name' => $supplier->company_name,
                                    'contact_person' => $supplier->contact_person,
                                    'phone' => $supplier->phone,
                                    'email' => $supplier->email,
                                    'address' => $supplier->address,
                                    'notes' => $supplier->notes,
                                    'status' => $supplier->status,
                                    'category_ids' => $supplier->categories->pluck('id'),
                                ]))"
                                title="Edit supplier"
                                class="text-gray-400 hover:text-green-700 transition-colors">
                                <i data-lucide="pencil" class="w-4 h-4"></i>
                            </button>

                            <!-- Archive -->
                            <button
                                @if($supplier->status === 'Active')
                                    @click="openArchive(
                                        {{ $supplier->id }},
                                        '{{ addslashes($supplier->company_name) }}'
                                    )"
                                @endif
                                title="{{ $supplier->status === 'Active' ? 'Archive supplier' : 'Supplier already archived' }}"
                                class="shrink-0 transition-colors
                                    {{ $supplier->status === 'Active'
                                        ? 'text-gray-400 hover:text-red-600 cursor-pointer'
                                        : 'text-gray-200 cursor-not-allowed'
                                    }}"
                                {{ $supplier->status === 'Archived' ? 'disabled' : '' }}
                            >
                                <i data-lucide="archive" class="w-4 h-4"></i>
                            </button>

                        </div>
                    </div>

                    <p class="text-sm text-gray-500 mb-2">Contact: <span class="font-medium text-gray-700">{{ $supplier->contact_person }}</span></p>

                    <div class="space-y-1.5 text-xs text-gray-500">
                        @if($supplier->email)
                            <p class="flex items-center gap-2 truncate">
                                <i data-lucide="mail" class="w-3.5 h-3.5 text-gray-400 shrink-0"></i> {{ $supplier->email }}
                            </p>
                        @endif
                        <p class="flex items-center gap-2">
                            <i data-lucide="phone" class="w-3.5 h-3.5 text-gray-400 shrink-0"></i> {{ $supplier->phone }}
                        </p>
                        @if($supplier->address)
                            <p class="flex items-center gap-2">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-gray-400 shrink-0"></i> {{ $supplier->address }}
                            </p>
                        @endif
                    </div>

                    <div class="flex flex-wrap gap-1.5 mt-3 pt-3 border-t border-gray-100">
                        @forelse($supplier->categories as $category)
                            <span class="inline-flex items-center gap-1 text-[11px] font-medium px-2 py-0.5 rounded-full text-white"
                                  style="background-color: {{ $category->icon_color }};">
                                {{ $category->icon }} {{ $category->name }}
                            </span>
                        @empty
                            <span class="text-xs text-gray-300">No categories assigned</span>
                        @endforelse

                    </div>
                </div>
            @empty
                <p class="text-gray-400 text-sm col-span-full text-center py-10">No suppliers found.</p>
            @endforelse
        </div>

        @if($suppliers->hasPages())
            <div class="mt-6">{{ $suppliers->links() }}</div>
        @endif

        <!-- Edit Supplier Modal -->
        <div x-show="showEditModal"
             x-transition:enter="transition-opacity ease-out duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-150"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
             style="display: none;" x-cloak>
            <div @click.outside="closeEdit()"
                 x-show="showEditModal"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 class="bg-white rounded-2xl shadow-2xl w-full max-w-xl overflow-hidden max-h-[90vh] flex flex-col">

                <template x-if="editForm">
                    <form method="POST" :action="'/suppliers/' + editForm.id" class="flex flex-col overflow-hidden">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="editing_id" :value="editForm.id">

                        <!-- Header -->
                        <div class="flex items-start justify-between px-4 sm:px-6 pt-6 pb-5 shrink-0">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-full bg-green-50 text-green-600 flex items-center justify-center shrink-0">
                                    <i data-lucide="truck" class="w-4.5 h-4.5"></i>
                                </div>
                                <div class="min-w-0">
                                    <h2 class="font-semibold text-gray-800 text-base leading-tight">Edit supplier</h2>
                                    <p class="text-xs text-gray-400 mt-0.5">Update this supplier's details</p>
                                </div>
                            </div>
                            <button type="button" @click="closeEdit()"
                                    class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg p-1.5 -mt-1 -mr-1 transition-colors shrink-0">
                                <i data-lucide="x" class="w-4.5 h-4.5"></i>
                            </button>
                        </div>

                        <!-- Validation summary (shown after a failed submit) -->
                        <template x-if="Object.keys(editErrors).length">
                            <div class="mx-4 sm:mx-6 mb-4 bg-red-50 border border-red-200 rounded-xl p-3 flex items-center gap-3 shrink-0">
                                <div class="w-7 h-7 rounded-full bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                                    <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                                </div>
                                <p class="text-xs font-medium text-red-700 leading-relaxed">
                                    Please fix the highlighted field<span x-text="Object.keys(editErrors).length > 1 ? 's' : ''"></span> below.
                                </p>
                            </div>
                        </template>

                        <!-- Body -->
                        <div class="px-4 sm:px-6 pb-6 space-y-4 overflow-y-auto">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Company / Supplier Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="company_name" x-model="editForm.company_name"
                                    class="w-full border rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 transition-colors"
                                    :class="editErrors.company_name ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500'">
                                <template x-if="editErrors.company_name">
                                    <p class="text-xs text-red-600 mt-1.5" x-text="editErrors.company_name?.[0]"></p>
                                </template>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                        Contact Person <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="contact_person" x-model="editForm.contact_person"
                                        class="w-full border rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 transition-colors"
                                        :class="editErrors.contact_person ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500'">
                                    <template x-if="editErrors.contact_person">
                                        <p class="text-xs text-red-600 mt-1.5" x-text="editErrors.contact_person?.[0]"></p>
                                    </template>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                        Phone Number <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="phone" x-model="editForm.phone"
                                        class="w-full border rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 transition-colors"
                                        :class="editErrors.phone ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500'">
                                    <template x-if="editErrors.phone">
                                        <p class="text-xs text-red-600 mt-1.5" x-text="editErrors.phone?.[0]"></p>
                                    </template>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Email Address</label>
                                <input type="email" name="email" x-model="editForm.email"
                                    class="w-full border rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 transition-colors"
                                    :class="editErrors.email ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500'">
                                <template x-if="editErrors.email">
                                    <p class="text-xs text-red-600 mt-1.5" x-text="editErrors.email?.[0]"></p>
                                </template>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Address</label>
                                <textarea name="address" x-model="editForm.address" rows="2"
                                    class="w-full border rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 transition-colors resize-none"
                                    :class="editErrors.address ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500'"></textarea>
                                <template x-if="editErrors.address">
                                    <p class="text-xs text-red-600 mt-1.5" x-text="editErrors.address?.[0]"></p>
                                </template>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Supply Categories</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($categories as $category)
                                        <button type="button" @click="toggleCategory({{ $category->id }})"
                                                :class="editForm.category_ids.includes({{ $category->id }}) ? 'bg-green-600 text-white border-green-600' : 'bg-gray-100 text-gray-700 border-gray-200'"
                                                class="inline-flex items-center gap-1.5 text-sm px-3.5 py-2 rounded-lg border transition-colors">
                                            {{ $category->icon }} {{ $category->name }}
                                        </button>
                                        <template x-if="editForm.category_ids.includes({{ $category->id }})">
                                            <input type="hidden" name="supply_categories[]" value="{{ $category->id }}">
                                        </template>
                                    @endforeach
                                </div>
                                <template x-if="editErrors.supply_categories">
                                    <p class="text-xs text-red-600 mt-1.5" x-text="editErrors.supply_categories?.[0]"></p>
                                </template>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
                                <textarea name="notes" x-model="editForm.notes" rows="2"
                                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 transition-colors resize-none"></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                                <div class="flex items-center gap-1.5 bg-gray-50 border border-gray-200 p-1 rounded-lg w-fit">
                                    <button type="button" @click="editForm.status = 'Active'"
                                            :class="editForm.status === 'Active' ? 'bg-green-600 text-white' : 'text-gray-500 hover:text-gray-700'"
                                            class="px-4 py-1.5 rounded-md text-xs font-medium transition-colors">
                                        Active
                                    </button>
                                    <button type="button" @click="editForm.status = 'Archived'"
                                            :class="editForm.status === 'Archived' ? 'bg-gray-600 text-white' : 'text-gray-500 hover:text-gray-700'"
                                            class="px-4 py-1.5 rounded-md text-xs font-medium transition-colors">
                                        Archived
                                    </button>
                                </div>
                                <input type="hidden" name="status" :value="editForm.status">
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-2.5 px-4 sm:px-6 py-4 bg-gray-50 border-t border-gray-100 shrink-0">
                            <button type="button" @click="closeEdit()"
                                    class="text-gray-600 hover:bg-gray-200/70 px-4 py-2 rounded-lg text-sm font-medium transition-colors order-2 sm:order-1">
                                Cancel
                            </button>
                            <button type="submit"
                                    :disabled="!hasChanges()"
                                    :class="hasChanges() ? 'bg-green-700 hover:bg-green-800 cursor-pointer' : 'bg-gray-300 cursor-not-allowed'"
                                    class="text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm order-1 sm:order-2">
                                Save changes
                            </button>
                        </div>
                    </form>
                </template>
            </div>
        </div>

        <!-- Archive Confirmation Modal -->
        <div x-show="showArchiveModal"
             x-transition:enter="transition-opacity ease-out duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-150"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
             style="display: none;" x-cloak>
            <div @click.outside="showArchiveModal = false"
                 x-show="showArchiveModal"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
                <div class="px-6 pt-6 pb-5">
                    <div class="w-11 h-11 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center mb-4">
                        <i data-lucide="archive" class="w-5 h-5"></i>
                    </div>
                    <h2 class="font-semibold text-gray-800 text-base mb-1.5">Archive supplier?</h2>
                    <p class="text-sm text-gray-500 leading-relaxed">
                        <span class="font-medium text-gray-700" x-text="archiveTarget.name"></span> will be moved to Archived.
                    </p>
                </div>
                <form method="POST" :action="`/suppliers/${archiveTarget.id}`"
                      class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-2.5 px-6 py-4 bg-gray-50 border-t border-gray-100">
                    @csrf @method('DELETE')
                    <button type="button" @click="showArchiveModal = false"
                            class="text-gray-600 hover:bg-gray-200/70 px-4 py-2 rounded-lg text-sm font-medium transition-colors order-2 sm:order-1">
                        Cancel
                    </button>
                    <button type="submit"
                            class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm order-1 sm:order-2">
                        Archive
                    </button>
                </form>
            </div>
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
                    <h2 class="font-semibold text-gray-800 text-base mb-1.5">Done</h2>
                    <p class="text-sm text-gray-500" x-text="successMessage"></p>
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
