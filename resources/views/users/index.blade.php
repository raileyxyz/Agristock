<x-app-layout>
    <div class="flex items-center justify-between mb-1">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">All Users</h1>
            <p class="text-gray-400 text-sm mt-1">
                {{ $users->where('status', 'Active')->count() }} active · {{ $users->where('status', 'Archived')->count() }} inactive
            </p>
        </div>
        <a href="{{ route('users.create') }}"
           class="bg-green-700 hover:bg-green-800 text-white px-4 py-2.5 rounded-lg text-sm font-medium flex items-center gap-2 transition-colors">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Add User
        </a>
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
            <table class="w-full text-sm min-w-[800px]">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-left text-gray-500">
                        <th class="px-4 py-3 font-medium whitespace-nowrap">User</th>
                        <th class="px-4 py-3 font-medium whitespace-nowrap">Email</th>
                        <th class="px-4 py-3 font-medium whitespace-nowrap">Role</th>
                        <th class="px-4 py-3 font-medium whitespace-nowrap">Last Login</th>
                        <th class="px-4 py-3 font-medium whitespace-nowrap">Status</th>
                        <th class="px-4 py-3 font-medium text-right whitespace-nowrap">Actions</th>
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
                                    <div class="w-8 h-8 rounded-full bg-green-600 text-white flex items-center justify-center text-xs font-semibold shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <span class="font-medium text-gray-800">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $user->email }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $roleBadge }}">{{ $user->role }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-400 text-xs whitespace-nowrap">
                                {{ $user->last_login_at?->format('Y-m-d H:i') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="text-xs font-medium px-2.5 py-1 rounded-full
                                    {{ ($user->status ?? 'Active') === 'Active' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ strtolower($user->status ?? 'Active') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('users.edit', $user) }}" title="Edit user"
                                   class="text-gray-400 hover:text-green-700 inline-flex">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </a>
                            </td>
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

</x-app-layout>
