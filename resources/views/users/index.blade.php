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
            showViewModal: false,
            viewTarget: {},

            openView(user) {
                this.viewTarget = user;
                this.showViewModal = true;
                this.$nextTick(() => lucide.createIcons());
            },

            openEdit(user) {
                this.editForm = { ...user, password: '' };
                this.originalForm = { ...user, password: '' };
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

        <!-- Header -->
        <div class="flex items-center justify-between gap-3 mb-1">
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-gray-100 transition-colors duration-200 ease-in-out">All Users</h1>
                <p class="mt-1 text-xs sm:text-sm text-gray-500 dark:text-gray-400 flex flex-wrap items-center gap-x-2 gap-y-0.5 transition-colors duration-200 ease-in-out">
                    <span>{{ $statistics['total'] }} Users</span>

                    <span class="text-gray-300 dark:text-[#27332C]">•</span>

                    <span class="text-green-600 dark:text-green-400 font-medium">
                        {{ $statistics['active'] }} Active
                    </span>

                    <span class="text-gray-300 dark:text-[#27332C]">•</span>

                    <span>
                        {{ $statistics['archived'] }} Archived
                    </span>
                </p>
            </div>
            @can('create', \App\Models\User::class)
                <a href="{{ route('users.create') }}"
                   class="bg-green-600 hover:bg-green-700 text-white px-2.5 sm:px-3.5 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm font-medium flex items-center justify-center gap-1 sm:gap-1.5 transition-colors shrink-0">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    Add User
                </a>
            @endcan
        </div>

        <!-- Search + Role filter -->
        <form method="GET"
              x-data="{ search: '{{ addslashes(request('search')) }}' }"
              x-init="$watch('search', value => {
                  clearTimeout(window._userSearchDebounce);
                  window._userSearchDebounce = setTimeout(() => $el.submit(), 500);
              })"
              class="flex flex-col sm:flex-row sm:flex-wrap gap-3 mt-6">

            <div class="relative flex-1 min-w-0 sm:min-w-[200px]">
                <i data-lucide="search" class="w-4 h-4 text-gray-400 dark:text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="search" x-model="search" placeholder="Search by name or email..."
                       class="w-full border border-gray-300 dark:border-[#27332C] bg-white dark:bg-[#111713] text-gray-800 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500 rounded-lg pl-9 pr-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400/50 focus:border-transparent transition-colors duration-200 ease-in-out">
            </div>

            <div class="relative w-full sm:w-auto">
                <select name="role" onchange="this.form.submit()"
                        class="w-full sm:w-auto appearance-none border border-gray-300 dark:border-[#27332C] rounded-lg pl-3.5 pr-9 py-2.5 text-sm bg-white dark:bg-[#111713] text-gray-800 dark:text-gray-100 dark:[color-scheme:dark] focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400/50 focus:border-transparent transition-colors duration-200 ease-in-out">
                    <option value="">All Roles</option>
                    @foreach($roles as $roleOption)
                        <option value="{{ $roleOption }}" {{ request('role') === $roleOption ? 'selected' : '' }}>
                            {{ $roleOption }}
                        </option>
                    @endforeach
                </select>
                <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 dark:text-gray-500 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
            </div>
        </form>

        <!-- Users table -->
        <div class="bg-white dark:bg-[#111713] border border-gray-200 dark:border-[#27332C] rounded-xl overflow-hidden mt-4 transition-colors duration-200 ease-in-out">
            <div class="overflow-x-auto">
                @php
                    $canManageUnits = Auth::user()->can('suppliers.update') || Auth::user()->can('suppliers.delete');
                @endphp
                <table class="w-full text-sm min-w-[850px]">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-[#161F1A] border-b border-gray-200 dark:border-[#1F2B23] text-left text-gray-500 dark:text-gray-400 transition-colors duration-200 ease-in-out">
                            <th class="px-3 py-2.5 font-medium whitespace-nowrap">User</th>
                            <th class="px-3 py-2.5 font-medium whitespace-nowrap">Email</th>
                            <th class="px-3 py-2.5 font-medium whitespace-nowrap">Role</th>
                            <th class="px-3 py-2.5 font-medium whitespace-nowrap">Last Login</th>
                            <th class="px-3 py-2.5 font-medium whitespace-nowrap">Status</th>
                            <th class="px-3 py-2.5 font-medium text-right whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-[#1F2B23]">
                        @forelse($users as $user)
                            @php
                                $roleBadge = match($user->role->value) {
                                    'Admin' => 'bg-purple-100 text-purple-700 dark:bg-purple-950/50 dark:text-purple-400',
                                    'Manager' => 'bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:text-blue-400',
                                    default => 'bg-gray-100 text-gray-600 dark:bg-gray-800/60 dark:text-gray-400',
                                };
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-[#161F1A]/60 transition-colors duration-200 ease-in-out">
                                <td class="px-3 py-2.5 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="relative shrink-0">
                                            <x-avatar :user="$user" size="w-9 h-9" text-size="text-xs font-semibold" />
                                            @if($user->id === auth()->id())
                                                <div class="absolute -bottom-0.5 -right-0.5 w-4.5 h-4.5 rounded-full bg-green-600 dark:bg-green-500 border-2 border-white dark:border-[#111713] flex items-center justify-center shadow-sm" title="You">
                                                    <i data-lucide="check" class="w-2.5 h-2.5 text-white stroke-[3]"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <span class="font-medium text-gray-900 dark:text-gray-100 max-w-[200px] truncate" title="{{ $user->name }}">{{ $user->name }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-2.5 text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $user->email }}</td>
                                <td class="px-3 py-2.5 whitespace-nowrap">
                                    <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $roleBadge }}">{{ $user->role->value }}</span>
                                </td>
                                <td class="px-3 py-2.5 text-gray-400 dark:text-gray-500 text-xs whitespace-nowrap">
                                    {{ $user->last_login_at?->format('Y-m-d H:i') ?? 'Never' }}
                                </td>
                                <td class="px-3 py-2.5 whitespace-nowrap">
                                    <span class="text-xs font-medium px-2.5 py-1 rounded-full
                                        {{ $user->status === \App\Enums\Status::ACTIVE
                                            ? 'bg-green-100 text-green-700 dark:bg-green-950/40 dark:text-green-400'
                                            : 'bg-gray-100 text-gray-500 dark:bg-[#1C2621] dark:text-gray-400' }}">
                                        {{ $user->status->value }}
                                    </span>
                                </td>

                                <!-- Actions Dropdown Column -->
                                <td class="px-3 py-2.5 whitespace-nowrap text-right">
                                    <div x-data="{ open: false }" class="relative inline-block text-left">
                                        <button @click="open = !open"
                                                @click.outside="open = false"
                                                title="Options"
                                                class="text-gray-400 dark:text-gray-400 hover:text-gray-600 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-[#1C2621] p-1.5 rounded-lg transition-colors">
                                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                                        </button>

                                        <div x-show="open"
                                            x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="transform opacity-0 scale-95"
                                            x-transition:enter-end="transform opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="transform opacity-100 scale-100"
                                            x-transition:leave-end="transform opacity-0 scale-95"
                                            class="absolute right-0 z-20 mt-1 w-36 origin-top-right rounded-xl bg-white dark:bg-[#161D19] p-1 shadow-lg ring-1 ring-black/5 dark:ring-0 dark:border dark:border-[#27332C] focus:outline-none"
                                            x-cloak>

                                            <!-- View -->
                                            <button @click="open = false; openView(@js([
                                                        'id' => $user->id,
                                                        'name' => $user->name,
                                                        'email' => $user->email,
                                                        'phone' => $user->phone,
                                                        'address' => $user->address,
                                                        'avatar' => $user->avatar ? \Illuminate\Support\Facades\Storage::url($user->avatar) : null,
                                                        'role' => $user->role->value,
                                                        'status' => $user->status->value,
                                                        'last_login_at' => $user->last_login_at?->format('M d, Y - h:i A'),
                                                        'created_at' => $user->created_at?->format('M d, Y'),
                                                    ]))"
                                                    class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-[#1C2621] hover:text-green-600 dark:hover:text-green-400 transition-colors">
                                                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                                View
                                            </button>

                                            <!-- Edit -->
                                            @if($user->id !== auth()->id())
                                                @can('update', $user)
                                                    <button @click="open = false; openEdit(@js([
                                                                'id' => $user->id,
                                                                'name' => $user->name,
                                                                'email' => $user->email,
                                                                'role' => $user->role->value,
                                                                'status' => $user->status->value,
                                                            ]))"
                                                            class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-[#1C2621] hover:text-green-600 dark:hover:text-green-400 transition-colors">
                                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                                        Edit
                                                    </button>
                                                @endcan
                                            @endif

                                            <!-- Archive -->
                                            @if($user->id !== auth()->id() && $user->status === \App\Enums\Status::ACTIVE)
                                                @can('delete', $user)
                                                    <button @click="open = false; openArchive({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                                            class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/40 transition-colors">
                                                        <i data-lucide="archive" class="w-3.5 h-3.5"></i>
                                                        Archive
                                                    </button>
                                                @endcan
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-14 text-center">
                                    <i data-lucide="users" class="w-8 h-8 text-gray-300 dark:text-[#27332C] mx-auto mb-2"></i>
                                    <p class="text-gray-500 dark:text-gray-400 text-sm">No users found.</p>
                                    <p class="text-gray-400 dark:text-gray-500 text-xs mt-1">Try a different search or role filter.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($users->hasPages())
            <div class="mt-6">{{ $users->links() }}</div>
        @endif

        <!-- View User Modal -->
        <div x-show="showViewModal"
            x-transition:enter="transition-opacity ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/50 dark:bg-[#0B0F0D]/80 backdrop-blur-sm z-50 flex items-center justify-center p-4"
            style="display: none;" x-cloak>

            <div @click.outside="showViewModal = false"
                x-show="showViewModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                class="bg-white dark:bg-[#161D19] rounded-2xl shadow-xl border border-gray-100 dark:border-[#27332C] w-full max-w-sm overflow-hidden max-h-[90vh] flex flex-col">

                <!-- Top Action Bar -->
                <div class="flex items-center justify-between gap-3 px-4 sm:px-6 pt-5 pb-4 shrink-0 border-b border-gray-100 dark:border-[#27332C]">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-full bg-green-50 dark:bg-green-950/40 text-green-600 dark:text-green-400 flex items-center justify-center shrink-0">
                            <i data-lucide="user" class="w-4.5 h-4.5"></i>
                        </div>
                        <div class="min-w-0">
                            <h2 class="font-semibold text-gray-900 dark:text-gray-100 text-base leading-tight">User Details</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">View user profile information</p>
                        </div>
                    </div>
                    <button type="button" @click="showViewModal = false"
                            class="text-gray-400 dark:text-gray-400 hover:text-gray-600 dark:hover:text-gray-100 hover:bg-gray-100/80 dark:hover:bg-[#1C2621] rounded-lg p-1.5 transition-colors shrink-0">
                        <i data-lucide="x" class="w-4.5 h-4.5"></i>
                    </button>
                </div>

                <!-- Scrollable content -->
                <div class="overflow-y-auto flex-1 min-h-0">

                    <!-- Profile Hero Section -->
                    <div class="px-4 sm:px-6 pb-5 pt-4 flex flex-col items-center text-center border-b border-gray-100 dark:border-[#27332C]">
                        <div class="relative mb-3">
                            <template x-if="viewTarget.avatar">
                                <img :src="viewTarget.avatar" class="w-16 h-16 rounded-full object-cover ring-2 ring-gray-100 dark:ring-[#27332C] shadow-sm">
                            </template>

                            <!-- Green Avatar Background (When avatar is null) -->
                            <template x-if="!viewTarget.avatar">
                                <div class="w-16 h-16 rounded-full bg-green-600 text-white flex items-center justify-center text-lg font-semibold ring-2 ring-emerald-100 dark:ring-[#27332C] shadow-sm"
                                    x-text="viewTarget.name ? viewTarget.name.substring(0, 2).toUpperCase() : ''"></div>
                            </template>
                        </div>

                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 text-base leading-snug max-w-full break-words" x-text="viewTarget.name"></h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 max-w-full break-all" x-text="viewTarget.email"></p>

                        <!-- Status & Role Badges -->
                        <div class="flex flex-wrap items-center justify-center gap-2 mt-3">
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset"
                                :class="viewTarget.status === 'Active'
                                    ? 'bg-green-50 text-green-600 ring-green-600/20 dark:bg-green-950/40 dark:text-green-400 dark:ring-green-400/20'
                                    : 'bg-gray-50 text-gray-600 ring-gray-500/10 dark:bg-[#1C2621] dark:text-gray-400 dark:ring-gray-500/20'">
                                <span class="h-1.5 w-1.5 rounded-full"
                                    :class="viewTarget.status === 'Active' ? 'bg-green-600 dark:bg-green-400' : 'bg-gray-400 dark:bg-gray-500'"></span>
                                <span x-text="viewTarget.status"></span>
                            </span>

                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset"
                                :class="viewTarget.role === 'Admin' ? 'bg-purple-100 text-purple-700 ring-purple-700/10 dark:bg-purple-950/50 dark:text-purple-400 dark:ring-purple-400/20' :
                                        (viewTarget.role === 'Manager' ? 'bg-blue-100 text-blue-700 ring-blue-700/10 dark:bg-blue-950/50 dark:text-blue-400 dark:ring-blue-400/20' : 'bg-gray-100 text-gray-600 ring-gray-500/10 dark:bg-gray-800/60 dark:text-gray-400 dark:ring-gray-500/20')"
                                x-text="viewTarget.role">
                            </span>
                        </div>
                    </div>

                    <!-- Details List -->
                    <div class="px-4 sm:px-6 py-5 bg-gray-50/50 dark:bg-[#111713] space-y-3.5">
                        <div class="flex items-center justify-between gap-3 text-xs">
                            <span class="text-gray-500 dark:text-gray-400 flex items-center gap-2 shrink-0">
                                <i data-lucide="phone" class="w-3.5 h-3.5 text-gray-400 dark:text-gray-500"></i>
                                Phone
                            </span>
                            <span class="text-gray-900 dark:text-gray-100 font-medium text-right min-w-0 truncate" x-text="viewTarget.phone || '—'"></span>
                        </div>

                        <div class="flex items-center justify-between gap-3 text-xs">
                            <span class="text-gray-500 dark:text-gray-400 flex items-center gap-2 shrink-0">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-gray-400 dark:text-gray-500"></i>
                                Address
                            </span>
                            <span class="text-gray-900 dark:text-gray-100 font-medium text-right min-w-0 truncate" x-text="viewTarget.address || '—'"></span>
                        </div>

                        <div class="flex items-center justify-between gap-3 text-xs">
                            <span class="text-gray-500 dark:text-gray-400 flex items-center gap-2 shrink-0">
                                <i data-lucide="clock" class="w-3.5 h-3.5 text-gray-400 dark:text-gray-500"></i>
                                Last Activity
                            </span>
                            <span class="text-gray-900 dark:text-gray-100 font-medium text-right min-w-0" x-text="viewTarget.last_login_at || 'Never'"></span>
                        </div>

                        <div class="flex items-center justify-between gap-3 text-xs">
                            <span class="text-gray-500 dark:text-gray-400 flex items-center gap-2 shrink-0">
                                <i data-lucide="calendar" class="w-3.5 h-3.5 text-gray-400 dark:text-gray-500"></i>
                                Member Since
                            </span>
                            <span class="text-gray-900 dark:text-gray-100 font-medium text-right min-w-0" x-text="viewTarget.created_at"></span>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-4 sm:px-6 py-4 bg-white dark:bg-[#161D19] border-t border-gray-100 dark:border-[#27332C] shrink-0">
                    <button type="button" @click="showViewModal = false"
                            class="w-full h-9 rounded-lg text-xs font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-[#1C2621] hover:bg-gray-200/80 dark:hover:bg-[#27332C] transition-colors">
                        Done
                    </button>
                </div>

            </div>
        </div>

        <!-- Edit User Modal -->
        <div x-show="showEditModal"
             x-transition:enter="transition-opacity ease-out duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-150"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/50 dark:bg-[#0B0F0D]/80 backdrop-blur-sm z-50 flex items-center justify-center p-4"
             style="display: none;" x-cloak>
            <div @click.outside="closeEdit()"
                 x-show="showEditModal"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 class="bg-white dark:bg-[#161D19] border border-transparent dark:border-[#27332C] rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden max-h-[90vh] flex flex-col">

                <template x-if="editForm">
                    <form method="POST" :action="'/users/' + editForm.id" class="flex flex-col overflow-hidden">
                        @csrf
                        @method('PUT')

                        <!-- Header -->
                        <div class="flex items-start justify-between gap-3 px-4 sm:px-6 pt-6 pb-5 shrink-0">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-full bg-green-50 dark:bg-green-950/40 text-green-600 dark:text-green-400 flex items-center justify-center shrink-0">
                                    <i data-lucide="pencil" class="w-4.5 h-4.5"></i>
                                </div>
                                <div class="min-w-0">
                                    <h2 class="font-semibold text-gray-800 dark:text-gray-100 text-base leading-tight">Edit user</h2>
                                    <p class="text-xs text-gray-400 dark:text-gray-400 mt-0.5">Update this user's account details</p>
                                </div>
                            </div>
                            <button type="button" @click="closeEdit()"
                                    class="text-gray-400 dark:text-gray-400 hover:text-gray-600 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-[#1C2621] rounded-lg p-1.5 -mt-1 -mr-1 transition-colors shrink-0">
                                <i data-lucide="x" class="w-4.5 h-4.5"></i>
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="px-4 sm:px-6 pb-6 space-y-4 overflow-y-auto">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">Full Name</label>
                                <input type="text" name="name" x-model="editForm.name"
                                    class="w-full border rounded-lg px-3.5 py-2.5 text-sm dark:bg-[#0B0F0D] dark:text-gray-100 focus:outline-none focus:ring-2 transition-colors"
                                    :class="editErrors.name ? 'border-red-300 dark:border-red-500/60 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 dark:border-[#27332C] focus:ring-green-500/40 focus:border-green-500 dark:focus:border-green-400'">
                                <template x-if="editErrors.name">
                                    <p class="text-xs text-red-600 dark:text-red-400 mt-1.5" x-text="editErrors.name?.[0]"></p>
                                </template>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">Email Address</label>
                                <input type="email" name="email" x-model="editForm.email"
                                    class="w-full border rounded-lg px-3.5 py-2.5 text-sm dark:bg-[#0B0F0D] dark:text-gray-100 focus:outline-none focus:ring-2 transition-colors"
                                    :class="editErrors.email ? 'border-red-300 dark:border-red-500/60 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 dark:border-[#27332C] focus:ring-green-500/40 focus:border-green-500 dark:focus:border-green-400'">
                                <template x-if="editErrors.email">
                                    <p class="text-xs text-red-600 dark:text-red-400 mt-1.5" x-text="editErrors.email?.[0]"></p>
                                </template>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">Role</label>
                                <select name="role" x-model="editForm.role"
                                        class="w-full border rounded-lg px-3.5 py-2.5 text-sm bg-white dark:bg-[#0B0F0D] dark:text-gray-100 dark:[color-scheme:dark] focus:outline-none focus:ring-2 transition-colors"
                                        :class="editErrors.role ? 'border-red-300 dark:border-red-500/60 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 dark:border-[#27332C] focus:ring-green-500/40 focus:border-green-500 dark:focus:border-green-400'">
                                    @foreach($roles as $roleOption)
                                        <option value="{{ $roleOption }}">{{ $roleOption }}</option>
                                    @endforeach
                                </select>
                                <template x-if="editErrors.role">
                                    <p class="text-xs text-red-600 dark:text-red-400 mt-1.5" x-text="editErrors.role?.[0]"></p>
                                </template>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">
                                    New Password <span class="text-gray-400 dark:text-gray-500 font-normal block sm:inline">(leave blank to keep current)</span>
                                </label>
                                <input type="password" name="password" x-model="editForm.password" placeholder="Min 8 characters"
                                    class="w-full border rounded-lg px-3.5 py-2.5 text-sm dark:bg-[#0B0F0D] dark:text-gray-100 dark:placeholder:text-gray-500 focus:outline-none focus:ring-2 transition-colors"
                                    :class="editErrors.password ? 'border-red-300 dark:border-red-500/60 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 dark:border-[#27332C] focus:ring-green-500/40 focus:border-green-500 dark:focus:border-green-400'">
                                <template x-if="editErrors.password">
                                    <p class="text-xs text-red-600 dark:text-red-400 mt-1.5" x-text="editErrors.password?.[0]"></p>
                                </template>
                            </div>

                            <template x-if="editForm.id !== {{ auth()->id() }}">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">Status</label>
                                    <div class="flex items-center gap-1.5 bg-gray-50 dark:bg-[#111713] border border-gray-200 dark:border-[#27332C] p-1 rounded-lg w-full sm:w-fit">
                                        <button type="button" @click="editForm.status = 'Active'"
                                                :class="editForm.status === 'Active' ? 'bg-green-600 text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                                                class="flex-1 sm:flex-none px-4 py-1.5 rounded-md text-xs font-medium transition-colors">
                                            Active
                                        </button>
                                        <button type="button" @click="editForm.status = 'Archived'"
                                                :class="editForm.status === 'Archived' ? 'bg-gray-600 text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                                                class="flex-1 sm:flex-none px-4 py-1.5 rounded-md text-xs font-medium transition-colors">
                                            Archived
                                        </button>
                                    </div>
                                    <input type="hidden" name="status" :value="editForm.status">
                                </div>
                            </template>
                        </div>

                        <!-- Footer -->
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-2.5 px-4 sm:px-6 py-4 bg-gray-50 dark:bg-[#111713] border-t border-gray-100 dark:border-[#27332C] shrink-0">
                            <button type="button" @click="closeEdit()"
                                    class="text-gray-600 dark:text-gray-400 hover:bg-gray-200/70 dark:hover:bg-[#1C2621] dark:hover:text-gray-100 px-4 py-2 rounded-lg text-sm font-medium transition-colors order-2 sm:order-1">
                                Cancel
                            </button>
                            <button type="submit"
                                    :disabled="!hasChanges()"
                                    :class="hasChanges()
                                        ? 'bg-green-600 hover:bg-green-700 cursor-pointer text-white'
                                        : 'bg-gray-300 dark:bg-[#1C2621] text-white dark:text-gray-500 cursor-not-allowed'"
                                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm order-1 sm:order-2">
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
             class="fixed inset-0 bg-black/50 dark:bg-[#0B0F0D]/80 backdrop-blur-sm z-50 flex items-center justify-center p-4"
             style="display: none;" x-cloak>
            <div @click.outside="showArchiveModal = false"
                 x-show="showArchiveModal"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 class="bg-white dark:bg-[#161D19] border border-transparent dark:border-[#27332C] rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
                <div class="px-6 pt-6 pb-5">
                    <div class="w-11 h-11 rounded-full bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-4">
                        <i data-lucide="archive" class="w-5 h-5"></i>
                    </div>
                    <h2 class="font-semibold text-gray-800 dark:text-gray-100 text-base mb-1.5">Archive user?</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                        <span class="font-medium text-gray-700 dark:text-gray-100 break-words" x-text="archiveTarget.name"></span> will no longer be able to log in.
                    </p>
                </div>
                <form method="POST" :action="`/users/${archiveTarget.id}`"
                      class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-2.5 px-6 py-4 bg-gray-50 dark:bg-[#111713] border-t border-gray-100 dark:border-[#27332C]">
                    @csrf @method('DELETE')
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
                 class="bg-white dark:bg-[#161D19] border border-transparent dark:border-[#27332C] rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
                <div class="px-6 pt-6 pb-5 text-center">
                    <div class="w-12 h-12 rounded-full bg-green-50 dark:bg-green-950/40 text-green-600 dark:text-green-400 flex items-center justify-center mb-4 mx-auto">
                        <i data-lucide="check" class="w-6 h-6"></i>
                    </div>
                    <h2 class="font-semibold text-gray-800 dark:text-gray-100 text-base mb-1.5">Done</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400" x-text="successMessage"></p>
                </div>
                <div class="flex items-center justify-center px-6 py-4 bg-gray-50 dark:bg-[#111713] border-t border-gray-100 dark:border-[#27332C]">
                    <button @click="showSuccessModal = false"
                            class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                        Got it
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
