<x-app-layout>
    <div x-data="{
            showModal: false,
            editingId: null,
            showDeleteModal: false,
            showSuccessModal: false,
            successMessage: '{{ addslashes(session('success', '')) }}',
            search: '{{ addslashes(request('search')) }}',
            unitForm: { id: null, name: '', abbreviation: '' },
            deleteTarget: { id: null, name: '' },
            formErrors: {},
            originalUnitForm: null,

            openCreate() {
                this.unitForm = { id: null, name: '', abbreviation: '' };
                this.originalUnitForm = null;
                this.editingId = null;
                this.formErrors = {};
                this.showModal = true;
            },

            closeModal() {
                this.showModal = false;
                this.editingId = null;
                this.unitForm = { id: null, name: '', abbreviation: '' };
                this.originalUnitForm = null;
                this.formErrors = {};
            },

            openEdit(id, name, abbreviation) {
                this.unitForm = { id, name, abbreviation };
                this.originalUnitForm = { id, name, abbreviation };
                this.editingId = id;
                this.formErrors = {};
                this.showModal = true;
            },

            hasUnitChanges() {
                if (!this.originalUnitForm) return true;
                return JSON.stringify(this.unitForm) !== JSON.stringify(this.originalUnitForm);
            },

            openDelete(id, name) {
                this.deleteTarget = { id, name };
                this.showDeleteModal = true;
            }
        }"
        x-init="
            $watch('search', value => {
                clearTimeout(window._unitSearchDebounce);
                window._unitSearchDebounce = setTimeout(() => $el.closest('[data-page]').querySelector('form[data-search-form]').submit(), 500);
            });
            @if(session('success'))
                showSuccessModal = true;
            @endif
            @if($errors->any() && old('id') !== null)
                unitForm = { id: '{{ old('id') }}', name: '{{ addslashes(old('name')) }}', abbreviation: '{{ addslashes(old('abbreviation')) }}' };
                editingId = '{{ old('id') }}';
                formErrors = @js($errors->messages());
                showModal = true;
            @elseif($errors->any())
                unitForm = { id: null, name: '{{ addslashes(old('name')) }}', abbreviation: '{{ addslashes(old('abbreviation')) }}' };
                editingId = null;
                formErrors = @js($errors->messages());
                showModal = true;
            @endif
        "
        data-page>

        <div class="flex items-center justify-between mb-1">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Units of Measurement</h1>
                <p class="text-gray-500 mt-1">{{ $units->count() }} {{ Str::plural('unit', $units->count()) }}</p>
            </div>
            <button @click="openCreate()"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-lg text-sm font-medium flex items-center gap-2 transition-colors">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Add Unit
            </button>
        </div>

        <!-- Search -->
        <form method="GET" data-search-form class="relative max-w-sm mt-6">
            <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
            <input type="text" name="search" x-model="search" placeholder="Search units by name or abbreviation"
                class="w-full border border-gray-300 rounded-lg pl-9 pr-9 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
            <button type="button" x-show="search" @click="search = ''"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </form>

        <!-- Units table -->
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden mt-4">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[560px]">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-left text-gray-500">
                            <th class="px-5 py-3 font-medium">ID</th>
                            <th class="px-5 py-3 font-medium">Name</th>
                            <th class="px-5 py-3 font-medium">Abbreviation</th>
                            <th class="px-5 py-3 font-medium">Used in</th>
                            <th class="px-5 py-3 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($units as $unit)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-3.5 text-gray-400 text-xs">U{{ $unit->id }}</td>
                                <td class="px-5 py-3.5 font-semibold text-gray-800">{{ $unit->name }}</td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-block text-xs font-medium px-2.5 py-1 rounded-md bg-gray-100 text-gray-600">
                                        {{ $unit->abbreviation }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-gray-500">
                                    {{ $unit->products_count }} {{ Str::plural('product', $unit->products_count) }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-end gap-1">
                                        <button @click="openEdit({{ $unit->id }}, '{{ addslashes($unit->name) }}', '{{ addslashes($unit->abbreviation) }}')"
                                                title="Edit unit"
                                                class="text-gray-400 hover:text-green-700 p-1.5 rounded-md hover:bg-gray-100">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </button>
                                        <button @click="openDelete({{ $unit->id }}, '{{ addslashes($unit->name) }}')"
                                                title="Delete unit"
                                                class="text-gray-400 hover:text-red-600 p-1.5 rounded-md hover:bg-gray-100">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-14 text-center">
                                    <i data-lucide="ruler" class="w-8 h-8 text-gray-300 mx-auto mb-2"></i>
                                    <p class="text-gray-500 text-sm">No units found.</p>
                                    <p class="text-gray-400 text-xs mt-1">Try a different search, or add your first unit above.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add / Edit Unit Modal -->
        <div x-show="showModal"
            x-transition:enter="transition-opacity ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
            style="display: none;">
            <div @click.outside="closeModal()"
                x-show="showModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">

                <form method="POST"
                    :action="editingId ? `/units/${editingId}` : '{{ route('units.store') }}'">
                    @csrf
                    <template x-if="editingId">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
                    <input type="hidden" name="id" :value="editingId">

                    <!-- Header -->
                    <div class="flex items-start justify-between px-6 pt-6 pb-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-green-50 text-green-600 flex items-center justify-center shrink-0">
                                <i data-lucide="ruler" class="w-4.5 h-4.5"></i>
                            </div>
                            <div>
                                <h2 class="font-semibold text-gray-800 text-base leading-tight" x-text="editingId ? 'Edit unit' : 'New unit'">New unit</h2>
                                <p class="text-xs text-gray-400 mt-0.5" x-text="editingId ? 'Update the name or abbreviation' : 'Add a new unit of measurement'"></p>
                            </div>
                        </div>
                        <button type="button" @click="closeModal()"
                                class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg p-1.5 -mt-1 -mr-1 transition-colors">
                            <i data-lucide="x" class="w-4.5 h-4.5"></i>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="px-6 pb-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Unit name</label>
                            <input type="text" name="name" x-model="unitForm.name" placeholder="Full name (e.g. Kilogram)"
                                class="w-full border rounded-lg px-3.5 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 transition-colors"
                                :class="formErrors.name ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500'">
                            <template x-if="formErrors.name">
                                <p class="text-xs text-red-600 mt-1.5 flex items-center gap-1">
                                    <i data-lucide="circle-alert" class="w-3 h-3"></i> <span x-text="formErrors.name?.[0]"></span>
                                </p>
                            </template>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Abbreviation</label>
                            <input type="text" name="abbreviation" maxlength="10" x-model="unitForm.abbreviation" placeholder="Abbreviation (e.g. kg)"
                                class="w-full border rounded-lg px-3.5 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 transition-colors"
                                :class="formErrors.abbreviation ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500'">
                            <template x-if="formErrors.abbreviation">
                                <p class="text-xs text-red-600 mt-1.5 flex items-center gap-1">
                                    <i data-lucide="circle-alert" class="w-3 h-3"></i> <span x-text="formErrors.abbreviation?.[0]"></span>
                                </p>
                            </template>
                            <p class="text-xs text-gray-400 mt-1.5" x-show="!formErrors.abbreviation">Short form shown throughout the app, e.g. in product quantities.</p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-end gap-2.5 px-6 py-4 bg-gray-50 border-t border-gray-100">
                        <button type="button" @click="closeModal()"
                                class="text-gray-600 hover:bg-gray-200/70 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                :disabled="!hasUnitChanges()"
                                :class="hasUnitChanges() ? 'bg-green-600 hover:bg-green-700 cursor-pointer' : 'bg-gray-300 cursor-not-allowed'"
                                class="text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                            <span x-text="editingId ? 'Save changes' : 'Create unit'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div x-show="showDeleteModal"
            x-transition:enter="transition-opacity ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
            style="display: none;">
            <div @click.outside="showDeleteModal = false"
                x-show="showDeleteModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">

                <div class="px-6 pt-6 pb-5">
                    <div class="w-11 h-11 rounded-full bg-red-50 text-red-600 flex items-center justify-center mb-4">
                        <i data-lucide="trash-2" class="w-5 h-5"></i>
                    </div>
                    <h2 class="font-semibold text-gray-800 text-base mb-1.5">Delete unit?</h2>
                    <p class="text-sm text-gray-500 leading-relaxed">
                        Are you sure you want to delete <span class="font-medium text-gray-700" x-text="deleteTarget.name"></span>?
                        This cannot be undone.
                    </p>
                </div>

                <form method="POST" :action="`/units/${deleteTarget.id}`"
                    class="flex items-center justify-end gap-2.5 px-6 py-4 bg-gray-50 border-t border-gray-100">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="showDeleteModal = false"
                            class="text-gray-600 hover:bg-gray-200/70 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                        Delete
                    </button>
                </form>
            </div>
        </div>

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
                class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">

                <div class="px-6 pt-6 pb-5 text-center">
                    <div class="w-12 h-12 rounded-full bg-green-50 text-green-600 flex items-center justify-center mb-4 mx-auto">
                        <i data-lucide="check" class="w-6 h-6"></i>
                    </div>
                    <h2 class="font-semibold text-gray-800 text-base mb-1.5">Done</h2>
                    <p class="text-sm text-gray-500" x-text="successMessage || 'Unit saved successfully.'"></p>
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
