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
            showErrorModal: false,
            errorMessage: '{{ addslashes(session('error', '')) }}',
            formErrors: {},
            originalCategory: null,

            // Emoji picker
            showEmojiPicker: false,
            isDark: document.documentElement.classList.contains('dark'),
            quickPicks: ['🌱', '🌾', '🛠️', '💧', '🧪', '🚿', '🧴', '⚗️', '🐛', '🪲', '🌽', '🍅', '🥬', '🌿', '🚜', '📦'],

            editCategory(category) {
                this.showModal = true;
                this.editingId = category.id;
                this.selectedCategory = category;
                this.originalCategory = { ...category };
                this.formErrors = {};
                this.showEmojiPicker = false;
            },

            openCreate() {
                this.showModal = true;
                this.editingId = null;
                this.selectedCategory = { name: '', description: '', icon: '', icon_color: '#16a34a', status: 'Active' };
                this.originalCategory = null;
                this.formErrors = {};
                this.showEmojiPicker = false;
            },

            closeModal() {
                this.showEmojiPicker = false;
                this.showModal = false;
                this.editingId = null;
                this.selectedCategory = null;
                this.originalCategory = null;
                this.formErrors = {};
            },

            hasCategoryChanges() {
                if (!this.originalCategory) return true;
                return JSON.stringify(this.selectedCategory) !== JSON.stringify(this.originalCategory);
            },

            openArchive(id, name) {
                this.archiveTarget = { id, name };
                this.showArchiveModal = true;
            },

            toggleEmojiPicker() {
                // Kunin ang kasalukuyang theme tuwing bubuksan ang picker
                this.isDark = document.documentElement.classList.contains('dark');
                this.showEmojiPicker = !this.showEmojiPicker;
            },

            pickEmoji(emoji) {
                this.selectedCategory.icon = emoji;
                this.showEmojiPicker = false;
            },

            clearEmoji() {
                this.selectedCategory.icon = '';
            }
        }"
        x-init="
            @if(session('success'))
                showSuccessModal = true;
            @endif
            @if(session('error'))
                showErrorModal = true;
            @endif
            @if($errors->any() && old('id') !== null)
                selectedCategory = {
                    id: '{{ old('id') }}',
                    name: '{{ addslashes(old('name')) }}',
                    description: '{{ addslashes(old('description')) }}',
                    icon: '{{ addslashes(old('icon')) }}',
                    icon_color: '{{ old('icon_color', '#16a34a') }}'
                };
                editingId = '{{ old('id') }}';
                formErrors = @js($errors->messages());
                showModal = true;
            @elseif($errors->any())
                selectedCategory = {
                    id: null,
                    name: '{{ addslashes(old('name')) }}',
                    description: '{{ addslashes(old('description')) }}',
                    icon: '{{ addslashes(old('icon')) }}',
                    icon_color: '{{ old('icon_color', '#16a34a') }}'
                };
                editingId = null;
                formErrors = @js($errors->messages());
                showModal = true;
            @endif
        ">

        <div class="flex items-center justify-between gap-3 mb-1">
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-gray-100 transition-colors duration-200 ease-in-out">Manage Categories</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1 text-xs sm:text-sm truncate transition-colors duration-200 ease-in-out">{{ $categories->total() }} categories</p>
            </div>

            @can('products.create')
                <button
                    @click="openCreate()"
                    class="bg-green-600 hover:bg-green-700 text-white px-2.5 sm:px-3.5 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm font-medium flex items-center justify-center gap-1 sm:gap-1.5 transition-colors shrink-0">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    Add Category
                </button>
            @endcan
        </div>

        <!-- Search + status filter -->
        <form method="GET"
            x-data="{ search: '{{ addslashes(request('search')) }}' }"
            x-init="$watch('search', value => {
                clearTimeout(window._searchDebounce);
                window._searchDebounce = setTimeout(() => $el.submit(), 500);
            })"
            class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3 mt-6">

            <div class="relative flex-1 min-w-0 sm:min-w-[200px] sm:max-w-sm">
                <i data-lucide="search" class="w-4 h-4 text-gray-400 dark:text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="search" x-model="search" placeholder="Search by name or description"
                    class="w-full border border-gray-300 dark:border-[#27332C] bg-white dark:bg-[#111713] text-gray-800 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500 rounded-lg pl-9 pr-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400/50 focus:border-transparent transition-colors duration-200 ease-in-out">
            </div>

            <input type="hidden" name="status" value="{{ request('status', 'all') }}">

            <div class="inline-flex items-center rounded-lg border border-slate-200 dark:border-[#27332C] bg-white dark:bg-[#111713] p-1 shadow-sm overflow-x-auto w-full sm:w-auto transition-colors duration-200 ease-in-out">
                <a href="{{ request()->fullUrlWithQuery(['status' => 'all', 'page' => null]) }}"
                class="px-3 py-1.5 text-xs font-medium rounded-md whitespace-nowrap transition-colors flex-1 text-center {{ request('status', 'all') === 'all' ? 'bg-green-600 text-white' : 'text-slate-500 dark:text-gray-400 hover:text-slate-900 dark:hover:text-gray-100' }}">
                    All
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'Active', 'page' => null]) }}"
                class="px-3 py-1.5 text-xs font-medium rounded-md whitespace-nowrap transition-colors flex-1 text-center {{ request('status') === 'Active' ? 'bg-green-600 text-white' : 'text-slate-500 dark:text-gray-400 hover:text-slate-900 dark:hover:text-gray-100' }}">
                    Active
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'Archived', 'page' => null]) }}"
                class="px-3 py-1.5 text-xs font-medium rounded-md whitespace-nowrap transition-colors flex-1 text-center {{ request('status') === 'Archived' ? 'bg-green-600 text-white' : 'text-slate-500 dark:text-gray-400 hover:text-slate-900 dark:hover:text-gray-100' }}">
                    Archived
                </a>
            </div>
        </form>

        <!-- Category cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mt-4">
            @forelse($categories as $category)
                <div class="bg-white dark:bg-[#111713] border border-gray-200 dark:border-[#27332C] rounded-xl p-4 flex items-start gap-3 transition-colors duration-200 ease-in-out">
                    <div class="w-11 h-11 rounded-lg flex items-center justify-center text-xl shrink-0"
                        style="background-color: {{ $category->icon_color }}22;">
                        {{ $category->icon }}
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="font-semibold text-gray-800 dark:text-gray-100 truncate">{{ $category->name }}</p>
                            @if($category->status === \App\Enums\Status::ARCHIVED)
                                <span class="text-[10px] font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-[#1C2621] px-1.5 py-0.5 rounded shrink-0">
                                    Archived
                                </span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-400 dark:text-gray-400">{{ $category->products_count }} active products</p>
                    </div>

                    <div class="flex items-center gap-1 shrink-0">
                        @can('products.update')
                            <button
                                @click="editCategory(@js($category))"
                                title="Edit category"
                                class="text-gray-400 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 p-1.5 rounded-md hover:bg-green-50 dark:hover:bg-green-950/40 transition-colors">
                                <i data-lucide="pencil" class="w-4 h-4"></i>
                            </button>
                        @endcan

                        @can('products.delete')
                            @if($category->status === \App\Enums\Status::ACTIVE)
                                <button
                                    @click="openArchive({{ $category->id }}, '{{ addslashes($category->name) }}')"
                                    title="Archive category"
                                    class="text-gray-400 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 p-1.5 rounded-md hover:bg-red-50 dark:hover:bg-red-950/40 transition-colors">
                                    <i data-lucide="archive" class="w-4 h-4"></i>
                                </button>
                            @else
                                <span title="Already archived" class="text-gray-200 dark:text-gray-700 p-1.5 cursor-not-allowed">
                                    <i data-lucide="archive" class="w-4 h-4"></i>
                                </span>
                            @endif
                        @endcan
                    </div>
                </div>
            @empty
                <p class="text-gray-400 dark:text-gray-500 text-sm col-span-full text-center py-10">
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
            class="fixed inset-0 bg-black/50 dark:bg-[#0B0F0D]/80 backdrop-blur-sm z-50 flex items-center justify-center p-4"
            style="display: none;">
            <div @click.outside="closeModal()"
                x-show="showModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="bg-white dark:bg-[#161D19] border border-transparent dark:border-[#27332C] rounded-2xl shadow-2xl w-full max-w-xl overflow-hidden max-h-[90vh] flex flex-col">

                <form method="POST"
                    :action="editingId ? '/categories/' + editingId : '{{ route('categories.store') }}'"
                    class="flex flex-col overflow-hidden">
                    @csrf
                    <template x-if="editingId">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
                    <input type="hidden" name="id" :value="editingId">

                    <!-- Header -->
                    <div class="flex items-start justify-between px-4 sm:px-6 pt-6 pb-5 shrink-0">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-full bg-green-50 dark:bg-green-950/40 text-green-600 dark:text-green-400 flex items-center justify-center shrink-0">
                                <i data-lucide="tags" class="w-4.5 h-4.5"></i>
                            </div>
                            <div class="min-w-0">
                                <h2 class="font-semibold text-gray-800 dark:text-gray-100 text-base leading-tight" x-text="editingId ? 'Edit category' : 'New category'">New category</h2>
                                <p class="text-xs text-gray-400 dark:text-gray-400 mt-0.5" x-text="editingId ? 'Update this category\'s details' : 'Create a category for your products'"></p>
                            </div>
                        </div>
                        <button type="button" @click="closeModal()"
                                class="text-gray-400 dark:text-gray-400 hover:text-gray-600 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-[#1C2621] rounded-lg p-1.5 -mt-1 -mr-1 transition-colors shrink-0">
                            <i data-lucide="x" class="w-4.5 h-4.5"></i>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="px-4 sm:px-6 pb-6 space-y-4 overflow-y-auto">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">Category name</label>
                            <input
                                type="text"
                                name="name"
                                x-model="selectedCategory.name"
                                placeholder="Category name"
                                class="w-full border rounded-lg px-3.5 py-2.5 text-sm text-gray-800 dark:text-gray-100 dark:bg-[#0B0F0D] dark:placeholder:text-gray-500 focus:outline-none focus:ring-2 transition-colors"
                                :class="formErrors.name ? 'border-red-300 dark:border-red-500/60 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 dark:border-[#27332C] focus:ring-green-500/40 focus:border-green-500 dark:focus:border-green-400'">
                            <template x-if="formErrors.name">
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1.5 flex items-center gap-1">
                                    <i data-lucide="circle-alert" class="w-3 h-3"></i> <span x-text="formErrors.name?.[0]"></span>
                                </p>
                            </template>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">Description (optional)</label>
                            <textarea
                                name="description"
                                x-model="selectedCategory.description"
                                rows="3"
                                placeholder="Short description of this category"
                                class="w-full border rounded-lg px-3.5 py-2.5 text-sm text-gray-800 dark:text-gray-100 dark:bg-[#0B0F0D] dark:placeholder:text-gray-500 focus:outline-none focus:ring-2 transition-colors resize-none"
                                :class="formErrors.description ? 'border-red-300 dark:border-red-500/60 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 dark:border-[#27332C] focus:ring-green-500/40 focus:border-green-500 dark:focus:border-green-400'"></textarea>
                            <template x-if="formErrors.description">
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1.5 flex items-center gap-1">
                                    <i data-lucide="circle-alert" class="w-3 h-3"></i> <span x-text="formErrors.description?.[0]"></span>
                                </p>
                            </template>
                        </div>

                        <!-- Icon + Color -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">Icon (emoji)</label>

                                <div class="relative">
                                    <!-- Readonly: pinipili lang sa picker, walang typing -->
                                    <input
                                        type="text"
                                        name="icon"
                                        readonly
                                        :value="selectedCategory?.icon ?? ''"
                                        placeholder="Choose icon"
                                        aria-haspopup="true"
                                        :aria-expanded="showEmojiPicker.toString()"
                                        @click="toggleEmojiPicker()"
                                        @keydown.enter.prevent="toggleEmojiPicker()"
                                        @keydown.space.prevent="toggleEmojiPicker()"
                                        class="w-full border rounded-lg pl-3.5 pr-9 py-2 text-lg leading-6 text-center cursor-pointer select-none text-gray-800 dark:text-gray-100 dark:bg-[#0B0F0D] placeholder:text-sm placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-none focus:ring-2 transition-colors"
                                        :class="[
                                            showEmojiPicker ? 'ring-2 ring-green-500/40 border-green-500 dark:border-green-400' : '',
                                            formErrors.icon ? 'border-red-300 dark:border-red-500/60 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 dark:border-[#27332C] focus:ring-green-500/40 focus:border-green-500 dark:focus:border-green-400'
                                        ]">

                                    <!-- Clear button (lalabas lang kapag may napiling emoji) -->
                                    <button type="button"
                                            x-show="selectedCategory?.icon"
                                            @click.stop="clearEmoji()"
                                            title="Clear icon"
                                            class="absolute right-2 top-1/2 -translate-y-1/2 p-1 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-[#1C2621] transition-colors">
                                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>

                                <template x-if="formErrors.icon">
                                    <p class="text-xs text-red-600 dark:text-red-400 mt-1.5 flex items-center gap-1">
                                        <i data-lucide="circle-alert" class="w-3 h-3"></i> <span x-text="formErrors.icon?.[0]"></span>
                                    </p>
                                </template>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">Color</label>
                                <input
                                    type="color"
                                    name="icon_color"
                                    x-model="selectedCategory.icon_color"
                                    class="w-full h-[42px] border rounded-lg cursor-pointer dark:bg-[#0B0F0D]"
                                    :class="formErrors.icon_color ? 'border-red-300 dark:border-red-500/60' : 'border-gray-300 dark:border-[#27332C]'">
                                <template x-if="formErrors.icon_color">
                                    <p class="text-xs text-red-600 dark:text-red-400 mt-1.5 flex items-center gap-1">
                                        <i data-lucide="circle-alert" class="w-3 h-3"></i> <span x-text="formErrors.icon_color?.[0]"></span>
                                    </p>
                                </template>
                            </div>
                        </div>

                        <!-- Live preview (kapareho ng itsura sa category cards) -->
                        <div class="flex items-center gap-3 rounded-xl border border-dashed border-gray-200 dark:border-[#27332C] bg-gray-50/60 dark:bg-[#111713] px-3.5 py-3">
                            <div class="w-11 h-11 rounded-lg flex items-center justify-center text-xl shrink-0 transition-colors"
                                 :style="{ backgroundColor: (selectedCategory?.icon_color || '#16a34a') + '22' }">
                                <span x-show="selectedCategory?.icon" x-text="selectedCategory?.icon"></span>
                                <i data-lucide="smile" x-show="!selectedCategory?.icon" class="w-5 h-5 text-gray-300 dark:text-gray-600"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="font-semibold text-sm truncate"
                                   :class="selectedCategory?.name ? 'text-gray-800 dark:text-gray-100' : 'text-gray-400 dark:text-gray-500'"
                                   x-text="selectedCategory?.name || 'Category name'"></p>
                                <p class="text-[11px] text-gray-400 dark:text-gray-500">Preview</p>
                            </div>
                        </div>

                        <!-- Inline emoji picker panel -->
                        <div x-show="showEmojiPicker" style="display: none;">
                            <div class="rounded-xl border border-gray-200 dark:border-[#27332C] bg-gray-50 dark:bg-[#111713] overflow-hidden">

                                <!-- Quick picks -->
                                <div class="px-3 pt-3 pb-2.5 border-b border-gray-200 dark:border-[#27332C]">
                                    <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2">Quick picks</p>
                                    <div class="flex flex-wrap gap-1.5">
                                        <template x-for="emoji in quickPicks" :key="emoji">
                                            <button type="button"
                                                    @click="pickEmoji(emoji)"
                                                    class="w-9 h-9 flex items-center justify-center rounded-lg text-xl border transition-colors"
                                                    :class="selectedCategory?.icon === emoji
                                                        ? 'bg-green-50 dark:bg-green-950/40 border-green-500 dark:border-green-400'
                                                        : 'bg-white dark:bg-[#0B0F0D] border-gray-200 dark:border-[#27332C] hover:border-green-500 dark:hover:border-green-400'"
                                                    x-text="emoji"></button>
                                        </template>
                                    </div>
                                </div>

                                <!-- Full picker (may search at categories) -->
                                <emoji-picker
                                    :class="isDark ? 'dark' : 'light'"
                                    @emoji-click="pickEmoji($event.detail.unicode)"></emoji-picker>
                            </div>
                        </div>

                        <template x-if="editingId">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">Status</label>

                                @can('products.delete')
                                    <div class="flex items-center gap-1.5 bg-gray-50 dark:bg-[#111713] border border-gray-200 dark:border-[#27332C] p-1 rounded-lg w-fit">
                                        <button type="button" @click="selectedCategory.status = 'Active'"
                                                :class="selectedCategory.status === 'Active' ? 'bg-green-600 text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                                                class="px-4 py-1.5 rounded-md text-xs font-medium transition-colors">
                                            Active
                                        </button>
                                        <button type="button" @click="selectedCategory.status = 'Archived'"
                                                :class="selectedCategory.status === 'Archived' ? 'bg-gray-600 text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                                                class="px-4 py-1.5 rounded-md text-xs font-medium transition-colors">
                                            Archived
                                        </button>
                                    </div>
                                    <input type="hidden" name="status" :value="selectedCategory.status">
                                @else
                                    <div class="inline-flex items-center gap-2 bg-gray-50 dark:bg-[#111713] border border-gray-200 dark:border-[#27332C] rounded-lg px-3.5 py-2 text-sm text-gray-500 dark:text-gray-400 w-fit">
                                        <span class="w-2 h-2 rounded-full shrink-0" :class="selectedCategory.status === 'Active' ? 'bg-green-500 dark:bg-green-400' : 'bg-gray-400 dark:bg-gray-500'"></span>
                                        <span x-text="selectedCategory.status"></span>
                                    </div>
                                    <input type="hidden" name="status" :value="selectedCategory.status">
                                @endcan

                                <template x-if="formErrors.status">
                                    <p class="text-xs text-red-600 dark:text-red-400 mt-1.5 flex items-center gap-1">
                                        <i data-lucide="circle-alert" class="w-3 h-3"></i> <span x-text="formErrors.status?.[0]"></span>
                                    </p>
                                </template>
                            </div>
                        </template>

                        <template x-if="!editingId">
                            <input type="hidden" name="status" value="Active">
                        </template>
                    </div>

                    <!-- Footer -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-2.5 px-4 sm:px-6 py-4 bg-gray-50 dark:bg-[#111713] border-t border-gray-100 dark:border-[#27332C] shrink-0">
                        <button type="button" @click="closeModal()"
                                class="text-gray-600 dark:text-gray-400 hover:bg-gray-200/70 dark:hover:bg-[#1C2621] dark:hover:text-gray-100 px-4 py-2 rounded-lg text-sm font-medium transition-colors order-2 sm:order-1">
                            Cancel
                        </button>
                        <button type="submit"
                                :disabled="!hasCategoryChanges()"
                                :class="hasCategoryChanges()
                                    ? 'bg-green-600 hover:bg-green-700 cursor-pointer text-white'
                                    : 'bg-gray-300 dark:bg-[#1C2621] text-white dark:text-gray-500 cursor-not-allowed'"
                                class="px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm order-1 sm:order-2">
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
            class="fixed inset-0 bg-black/50 dark:bg-[#0B0F0D]/80 backdrop-blur-sm z-50 flex items-center justify-center p-4"
            style="display: none;">
            <div @click.outside="showArchiveModal = false"
                x-show="showArchiveModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="bg-white dark:bg-[#161D19] border border-transparent dark:border-[#27332C] rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">

                <div class="px-6 pt-6 pb-5">
                    <div class="w-11 h-11 rounded-full bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-4">
                        <i data-lucide="archive" class="w-5 h-5"></i>
                    </div>
                    <h2 class="font-semibold text-gray-800 dark:text-gray-100 text-base mb-1.5">Archive category?</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                        <span class="font-medium text-gray-700 dark:text-gray-100" x-text="archiveTarget.name"></span> will be moved to Archived and hidden from active use. You can restore it later.
                    </p>
                </div>

                <form method="POST" :action="`/categories/${archiveTarget.id}`"
                    class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-2.5 px-6 py-4 bg-gray-50 dark:bg-[#111713] border-t border-gray-100 dark:border-[#27332C]">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="showArchiveModal = false"
                            class="text-gray-600 dark:text-gray-400 hover:bg-gray-200/70 dark:hover:bg-[#1C2621] dark:hover:text-gray-100 px-4 py-2 rounded-lg text-sm font-medium transition-colors order-2 sm:order-1">
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
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/50 dark:bg-[#0B0F0D]/80 backdrop-blur-sm z-50 flex items-center justify-center p-4"
            style="display: none;">
            <div @click.outside="showSuccessModal = false"
                x-show="showSuccessModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                class="bg-white dark:bg-[#161D19] border border-transparent dark:border-[#27332C] rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">

                <div class="px-6 pt-6 pb-5 text-center">
                    <div class="w-12 h-12 rounded-full bg-green-50 dark:bg-green-950/40 text-green-600 dark:text-green-400 flex items-center justify-center mb-4 mx-auto">
                        <i data-lucide="check" class="w-6 h-6"></i>
                    </div>
                    <h2 class="font-semibold text-gray-800 dark:text-gray-100 text-base mb-1.5">Done</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400" x-text="successMessage || 'Category updated successfully.'"></p>
                </div>

                <div class="flex items-center justify-center px-6 py-4 bg-gray-50 dark:bg-[#111713] border-t border-gray-100 dark:border-[#27332C]">
                    <button @click="showSuccessModal = false"
                            class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                        Got it
                    </button>
                </div>
            </div>
        </div>

        <!-- Error Modal -->
        <div x-show="showErrorModal"
            x-transition:enter="transition-opacity ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/50 dark:bg-[#0B0F0D]/80 backdrop-blur-sm z-50 flex items-center justify-center p-4"
            style="display: none;">
            <div @click.outside="showErrorModal = false"
                x-show="showErrorModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                class="bg-white dark:bg-[#161D19] border border-transparent dark:border-[#27332C] rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">

                <div class="px-6 pt-6 pb-5 text-center">
                    <div class="w-12 h-12 rounded-full bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 flex items-center justify-center mb-4 mx-auto">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </div>
                    <h2 class="font-semibold text-gray-800 dark:text-gray-100 text-base mb-1.5">Cannot archive</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400" x-text="errorMessage"></p>
                </div>

                <div class="flex items-center justify-center px-6 py-4 bg-gray-50 dark:bg-[#111713] border-t border-gray-100 dark:border-[#27332C]">
                    <button @click="showErrorModal = false"
                            class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                        Got it
                    </button>
                </div>
            </div>
        </div>

    </div>

    <!-- Emoji picker: library + theme (light/dark) na tugma sa palette ng system -->
    <script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>
    <style>
        emoji-picker {
            width: 100%;
            height: 18rem;
            --num-columns: 8;
            --emoji-size: 1.4rem;
            --border-size: 0;
            --border-radius: 0;
            --outline-size: 2px;
            --indicator-height: 3px;
        }
        emoji-picker.light {
            --background: #f9fafb;
            --border-color: #e5e7eb;
            --input-border-color: #d1d5db;
            --input-font-color: #1f2937;
            --input-placeholder-color: #9ca3af;
            --category-font-color: #6b7280;
            --button-hover-background: #e5e7eb;
            --button-active-background: #d1d5db;
            --indicator-color: #16a34a;
            --outline-color: #16a34a;
        }
        emoji-picker.dark {
            --background: #111713;
            --border-color: #27332C;
            --input-border-color: #27332C;
            --input-font-color: #f3f4f6;
            --input-placeholder-color: #6b7280;
            --category-font-color: #9ca3af;
            --button-hover-background: #1C2621;
            --button-active-background: #27332C;
            --indicator-color: #4ade80;
            --outline-color: #4ade80;
        }
    </style>
</x-app-layout>
