<x-app-layout>
    <div
        x-data="{
            showForm: false,
            editingId: null,
            selectedCategory: null,

            editCategory(category) {
                this.showForm = true;
                this.editingId = category.id;
                this.selectedCategory = category;
            }
        }">

        <div class="flex items-center justify-between mb-1">

            <div>
                <h1 class="text-2xl font-bold text-gray-800">Manage Categories</h1>
                <p class="text-gray-500 mt-1">{{ $categories->total() }} categories</p>
            </div>

            <button
                @click="
                    showForm = true;
                    editingId = null;
                    selectedCategory = null;
                "
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-lg text-sm font-medium flex items-center gap-2 transition-colors">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Add Category
            </button>

        </div>

        <!-- New / Edit Category form -->
        <div x-show="showForm" x-collapse class="mt-6">

            <form method="POST"
                :action="editingId ? '/categories/' + editingId : '{{ route('categories.store') }}'"
                class="bg-white border border-gray-200 rounded-xl p-6 max-w-xl">
                @csrf

                <template x-if="editingId">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <h2 class="font-semibold text-gray-800 mb-4" x-text="editingId ? 'Edit Category' : 'New Category'">Add New Category</h2>

                <div class="mb-4">
                    <label class="block text-sm text-gray-600 mb-1.5">Category name</label>
                    <input
                        type="text"
                        name="name"
                        x-model="selectedCategory ? selectedCategory.name : ''"
                        placeholder="Category name"
                        required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>

                <div class="mb-4">
                    <label class="block text-sm text-gray-600 mb-1.5">Description (optional)</label>
                    <textarea
                        name="description"
                        x-model="selectedCategory ? selectedCategory.description : ''"
                        rows="3"
                        placeholder="Short description of this category"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent resize-none"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1.5">Icon (emoji)</label>
                        <input
                            type="text"
                            name="icon"
                            x-model="selectedCategory ? selectedCategory.icon : ''"
                            maxlength="4"
                            required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1.5">Color</label>
                        <input
                            type="color"
                            name="icon_color"
                            x-model="selectedCategory ? selectedCategory.icon_color : '#16a34a'"
                            required
                            class="w-full h-[42px] border border-gray-300 rounded-lg cursor-pointer">
                    </div>
                </div>

                <input type="hidden" name="status" value="Active">

                <div class="flex items-center gap-3">

                    <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        Save
                    </button>

                    <button
                        type="button"
                        @click="
                        showForm = false;
                        editingId = null;
                        selectedCategory = null;
                        "
                        class="border border-gray-300 hover:bg-gray-50 text-gray-700 px-5 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        Cancel
                    </button>

                </div>
            </form>

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

            <!-- keep status as-is so it doesn't get lost when the debounced submit fires -->
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
                        {{-- @if($category->description)
                            <p class="text-sm text-gray-400 mt-0.5 truncate">{{ $category->description }}</p>
                        @endif --}}
                    </div>
                    <div class="flex items-center gap-1 shrink-0">

                        <button
                            @click="editCategory(@js($category))"
                            class="text-gray-400 hover:text-green-700 p-1.5 rounded-md hover:bg-gray-100">
                            <i data-lucide="pencil" class="w-4 h-4"></i>
                        </button>

                        <form method="POST" action="{{ route('categories.destroy', $category) }}"
                            onsubmit="return confirm('Archive this category?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-gray-400 hover:text-red-600 p-1.5 rounded-md hover:bg-gray-100">
                                <i data-lucide="archive" class="w-4 h-4"></i>
                            </button>
                        </form>

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

    </div>
</x-app-layout>
