<x-app-layout>
    <div
        x-data="{
            showModal: false,
            editingId: null,
            selectedCategory: null,
            showArchiveModal: false,
            archiveTarget: { id: null, name: '' },
            showSuccessModal: false,
            successMessage: '{{ addslashes(session('success', '')) }}',

            editCategory(category) {
                this.showModal = true;
                this.editingId = category.id;
                this.selectedCategory = category;
            },

            openCreate() {
                this.showModal = true;
                this.editingId = null;
                this.selectedCategory = null;
            },

            closeModal() {
                this.showModal = false;
                this.editingId = null;
                this.selectedCategory = null;
            },

            openArchive(id, name) {
                this.archiveTarget = { id, name };
                this.showArchiveModal = true;
            }
        }"
        x-init="
            @if(session('success'))
                showSuccessModal = true;
            @endif
        ">

        <div class="flex items-center justify-between mb-1">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Manage Categories</h1>
                <p class="text-gray-500 mt-1">{{ $categories->total() }} categories</p>
            </div>

            <button
                @click="openCreate()"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-lg text-sm font-medium flex items-center gap-2 transition-colors">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Add Category
            </button>
        </div>

        <!-- Search + status filter -->
        <form method="GET"
            x-data="{ search: '{{ addslashes(request('search')) }}' }"
            x-init="$watch('search', value => {
                clearTimeout(window._searchDebounce);
                window._searchDebounce = setTimeout(() => $el.submit(), 500);
            })"
            class="flex flex-col sm:flex-row sm:items-center gap-3 mt-6">

            <div class="relative flex-1 max-w-sm">
                <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="search" x-model="search" placeholder="Search by name or description"
                    class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>

            <input type="hidden" name="status" value="{{ request('status', 'all') }}">

            <div class="flex items-center gap-1.5 bg-gray-100 p-1 rounded-lg w-fit">
                <a href="{{ request()->fullUrlWithQuery(['status' => 'all', 'page' => null]) }}"
                class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors {{ request('status', 'all') === 'all' ? 'bg-white text-gray-800 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    All
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'Active', 'page' => null]) }}"
                class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors {{ request('status') === 'Active' ? 'bg-white text-gray-800 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    Active
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'Archived', 'page' => null]) }}"
                class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors {{ request('status') === 'Archived' ? 'bg-white text-gray-800 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    Archived
                </a>
            </div>
        </form>

        <!-- Category cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mt-4">
            @forelse($categories as $category)
                <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-start gap-3">
                    <div class="w-11 h-11 rounded-lg flex items-center justify-center text-xl shrink-0"
                        style="background-color: {{ $category->icon_color }}22;">
                        {{ $category->icon }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="font-semibold text-gray-800 truncate">{{ $category->name }}</p>
                            @if($category->status === 'Archived')
                                <span class="text-[10px] font-medium text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded shrink-0">
                                    Archived
                                </span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-400">{{ $category->products_count }} active products</p>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">

                        <button
                            @click="editCategory(@js($category))"
                            title="Edit category"
                            class="text-gray-400 hover:text-green-700 p-1.5 rounded-md hover:bg-gray-100">
                            <i data-lucide="pencil" class="w-4 h-4"></i>
                        </button>

                        <button
                            @click="openArchive({{ $category->id }}, '{{ addslashes($category->name) }}')"
                            title="Archive category"
                            class="text-gray-400 hover:text-red-600 p-1.5 rounded-md hover:bg-gray-100">
                            <i data-lucide="archive" class="w-4 h-4"></i>
                        </button>

                    </div>
                </div>
            @empty
                <p class="text-gray-400 text-sm col-span-full text-center py-10">
                    No categories match your search.
                </p>
            @endforelse
        </div>

        @if($categories->hasPages())
            <div class="mt-6">
                {{ $categories->links() }}
            </div>
        @endif

        <!-- Add / Edit Category Modal -->
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
                 class="bg-white rounded-2xl shadow-2xl w-full max-w-xl overflow-hidden max-h-[90vh] flex flex-col">

                <form method="POST"
                      :action="editingId ? '/categories/' + editingId : '{{ route('categories.store') }}'"
                      class="flex flex-col overflow-hidden">
                    @csrf
                    <template x-if="editingId">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <!-- Header -->
                    <div class="flex items-start justify-between px-6 pt-6 pb-5 shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-green-50 text-green-600 flex items-center justify-center shrink-0">
                                <i data-lucide="tags" class="w-4.5 h-4.5"></i>
                            </div>
                            <div>
                                <h2 class="font-semibold text-gray-800 text-base leading-tight" x-text="editingId ? 'Edit category' : 'New category'">New category</h2>
                                <p class="text-xs text-gray-400 mt-0.5" x-text="editingId ? 'Update this category\'s details' : 'Create a category for your products'"></p>
                            </div>
                        </div>
                        <button type="button" @click="closeModal()"
                                class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg p-1.5 -mt-1 -mr-1 transition-colors">
                            <i data-lucide="x" class="w-4.5 h-4.5"></i>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="px-6 pb-6 space-y-4 overflow-y-auto">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Category name</label>
                            <input
                                type="text"
                                name="name"
                                x-model="selectedCategory ? selectedCategory.name : ''"
                                placeholder="Category name"
                                required
                                class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 transition-colors">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Description (optional)</label>
                            <textarea
                                name="description"
                                x-model="selectedCategory ? selectedCategory.description : ''"
                                rows="3"
                                placeholder="Short description of this category"
                                class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 transition-colors resize-none"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Icon (emoji)</label>
                                <input
                                    type="text"
                                    name="icon"
                                    x-model="selectedCategory ? selectedCategory.icon : ''"
                                    maxlength="4"
                                    required
                                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm text-center text-gray-800 focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 transition-colors">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Color</label>
                                <input
                                    type="color"
                                    name="icon_color"
                                    x-model="selectedCategory ? selectedCategory.icon_color : '#16a34a'"
                                    required
                                    class="w-full h-[42px] border border-gray-300 rounded-lg cursor-pointer">
                            </div>
                        </div>

                        <input type="hidden" name="status" value="Active">
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-end gap-2.5 px-6 py-4 bg-gray-50 border-t border-gray-100 shrink-0">
                        <button type="button" @click="closeModal()"
                                class="text-gray-600 hover:bg-gray-200/70 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                            <span x-text="editingId ? 'Save changes' : 'Create category'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Archive Confirmation Modal -->
        <div x-show="showArchiveModal"
             x-transition:enter="transition-opacity ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
             style="display: none;">
            <div @click.outside="showArchiveModal = false"
                 x-show="showArchiveModal"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">

                <div class="px-6 pt-6 pb-5">
                    <div class="w-11 h-11 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center mb-4">
                        <i data-lucide="archive" class="w-5 h-5"></i>
                    </div>
                    <h2 class="font-semibold text-gray-800 text-base mb-1.5">Archive category?</h2>
                    <p class="text-sm text-gray-500 leading-relaxed">
                        <span class="font-medium text-gray-700" x-text="archiveTarget.name"></span> will be moved to Archived and hidden from active use. You can restore it later.
                    </p>
                </div>

                <form method="POST" :action="`/categories/${archiveTarget.id}`"
                      class="flex items-center justify-end gap-2.5 px-6 py-4 bg-gray-50 border-t border-gray-100">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="showArchiveModal = false"
                            class="text-gray-600 hover:bg-gray-200/70 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                        Archive
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
                    <p class="text-sm text-gray-500" x-text="successMessage || 'Category updated successfully.'"></p>
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
