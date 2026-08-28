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

        <div class="flex items-center justify-between mb-1">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">All Users</h1>
                <p class="mt-1 text-xs sm:text-sm text-gray-500 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                    <span>{{ $statistics['total'] }} Users</span>

                    <span class="text-gray-300">•</span>

                    <span class="text-green-600 font-medium">
                        {{ $statistics['active'] }} Active
                    </span>

                    <span class="text-gray-300">•</span>

                    <span>
                        {{ $statistics['archived'] }} Archived
                    </span>
                </p>
            </div>
            @can('users.create')
            <a href="{{ route('users.create') }}"
               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-lg text-sm font-medium flex items-center gap-2 transition-colors">
                <i data-lucide="plus" class="w-4 h-4"></i>
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
              class="flex flex-col sm:flex-row gap-3 mt-6">

            <div class="relative flex-1">
                <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="search" x-model="search" placeholder="Search by name or email..."
                       class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>

            <div class="relative">
                <select name="role" onchange="this.form.submit()"
                        class="appearance-none border border-gray-300 rounded-lg pl-3.5 pr-9 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <option value="">All Roles</option>
                    @foreach($roles as $roleOption)
                        <option value="{{ $roleOption }}" {{ request('role') === $roleOption ? 'selected' : '' }}>
                            {{ $roleOption }}
                        </option>
                    @endforeach
                </select>
                <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
            </div>
        </form>

        <!-- Users table -->
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden mt-4">
            <div class="overflow-x-auto">
                @php
                    $canManageUnits = Auth::user()->can('suppliers.update') || Auth::user()->can('suppliers.delete');
                @endphp
                <table class="w-full text-sm min-w-[850px]">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-left text-gray-500">
                            <th class="px-4 py-3 font-medium whitespace-nowrap">User</th>
                            <th class="px-4 py-3 font-medium whitespace-nowrap">Email</th>
                            <th class="px-4 py-3 font-medium whitespace-nowrap">Role</th>
                            <th class="px-4 py-3 font-medium whitespace-nowrap">Last Login</th>
                            <th class="px-4 py-3 font-medium whitespace-nowrap">Status</th>
                            @if($canManageUnits)
                                <th class="px-4 py-3 font-medium text-right whitespace-nowrap">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($users as $user)
                            @php
                                $roleBadge = match($user->role) {
                                    'Admin' => 'bg-purple-100 text-purple-700',
                                    'Manager' => 'bg-blue-100 text-blue-700',
                                    default => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center gap-2.5">
                                        <div class="relative shrink-0">
                                            <div class="w-8 h-8 rounded-full bg-green-600 text-white flex items-center justify-center text-xs font-semibold">
                                                {{ strtoupper(substr($user->name, 0, 2)) }}
                                            </div>
                                            @if($user->id === auth()->id())
                                                <div class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-white border border-gray-200 flex items-center justify-center">
                                                    <i data-lucide="check" class="w-3 h-3 text-red-600 font-bold"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <span class="font-medium text-gray-800">{{ $user->name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $user->email }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $roleBadge }}">{{ $user->role }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-400 text-xs whitespace-nowrap">
                                    {{ $user->last_login_at?->format('Y-m-d H:i') ?? 'Never' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="text-xs font-medium px-2.5 py-1 rounded-full
                                        {{ $user->status === 'Active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                        {{ ($user->status) }}
                                    </span>
                                </td>
                                @if($canManageUnits)
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1">
                                            @if($user->id !== auth()->id())
                                                @can('users.update')
                                                    <button @click="openEdit(@js([
                                                                'id' => $user->id,
                                                                'name' => $user->name,
                                                                'email' => $user->email,
                                                                'role' => $user->role,
                                                                'status' => $user->status,
                                                            ]))"
                                                            title="Edit user"
                                                            class="text-gray-400 hover:text-green-700 p-1.5 rounded-md hover:bg-gray-100">
                                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                                    </button>
                                                @endcan
                                            @endif
                                            @if($user->id !== auth()->id() && $user->status === 'Active')
                                                @can('suppliers.delete')
                                                    <button @click="openArchive({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                                            title="Archive user"
                                                            class="text-gray-400 hover:text-red-600 p-1.5 rounded-md hover:bg-gray-100">
                                                        <i data-lucide="archive" class="w-4 h-4"></i>
                                                    </button>
                                                @endcan
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-14 text-center">
                                    <i data-lucide="users" class="w-8 h-8 text-gray-300 mx-auto mb-2"></i>
                                    <p class="text-gray-500 text-sm">No users found.</p>
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

        <!-- Edit User Modal -->
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
                 class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden max-h-[90vh] flex flex-col">

                <template x-if="editForm">
                    <form method="POST" :action="'/users/' + editForm.id" class="flex flex-col overflow-hidden">
                        @csrf
                        @method('PUT')

                        <!-- Header -->
                        <div class="flex items-start justify-between px-6 pt-6 pb-5 shrink-0">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-green-50 text-green-600 flex items-center justify-center shrink-0">
                                    <i data-lucide="pencil" class="w-4.5 h-4.5"></i>
                                </div>
                                <div>
                                    <h2 class="font-semibold text-gray-800 text-base leading-tight">Edit user</h2>
                                    <p class="text-xs text-gray-400 mt-0.5">Update this user's account details</p>
                                </div>
                            </div>
                            <button type="button" @click="closeEdit()"
                                    class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg p-1.5 -mt-1 -mr-1 transition-colors">
                                <i data-lucide="x" class="w-4.5 h-4.5"></i>
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="px-6 pb-6 space-y-4 overflow-y-auto">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Full Name</label>
                                <input type="text" name="name" x-model="editForm.name"
                                    class="w-full border rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 transition-colors"
                                    :class="editErrors.name ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500'">
                                <template x-if="editErrors.name">
                                    <p class="text-xs text-red-600 mt-1.5" x-text="editErrors.name?.[0]"></p>
                                </template>
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
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Role</label>
                                <select name="role" x-model="editForm.role"
                                        class="w-full border rounded-lg px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 transition-colors"
                                        :class="editErrors.role ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500'">
                                    @foreach($roles as $roleOption)
                                        <option value="{{ $roleOption }}">{{ $roleOption }}</option>
                                    @endforeach
                                </select>
                                <template x-if="editErrors.role">
                                    <p class="text-xs text-red-600 mt-1.5" x-text="editErrors.role?.[0]"></p>
                                </template>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    New Password <span class="text-gray-400 font-normal">(leave blank to keep current)</span>
                                </label>
                                <input type="password" name="password" x-model="editForm.password" placeholder="Min 8 characters"
                                    class="w-full border rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 transition-colors"
                                    :class="editErrors.password ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500'">
                                <template x-if="editErrors.password">
                                    <p class="text-xs text-red-600 mt-1.5" x-text="editErrors.password?.[0]"></p>
                                </template>
                            </div>

                            <template x-if="editForm.id !== {{ auth()->id() }}">
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
                            </template>
                        </div>

                        <!-- Footer -->
                        <div class="flex items-center justify-end gap-2.5 px-6 py-4 bg-gray-50 border-t border-gray-100 shrink-0">
                            <button type="button" @click="closeEdit()"
                                    class="text-gray-600 hover:bg-gray-200/70 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                    :disabled="!hasChanges()"
                                    :class="hasChanges() ? 'bg-green-600 hover:bg-green-700 cursor-pointer' : 'bg-gray-300 cursor-not-allowed'"
                                    class="text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
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
                    <h2 class="font-semibold text-gray-800 text-base mb-1.5">Archive user?</h2>
                    <p class="text-sm text-gray-500 leading-relaxed">
                        <span class="font-medium text-gray-700" x-text="archiveTarget.name"></span> will no longer be able to log in.
                    </p>
                </div>
                <form method="POST" :action="`/users/${archiveTarget.id}`"
                      class="flex items-center justify-end gap-2.5 px-6 py-4 bg-gray-50 border-t border-gray-100">
                    @csrf @method('DELETE')
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
