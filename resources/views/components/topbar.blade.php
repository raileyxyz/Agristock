<header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 lg:px-8 gap-3">

    <div class="flex items-center gap-3 min-w-0">
        <button @click="sidebarOpen = true" class="text-gray-600 hover:text-gray-900 p-1.5 -ml-1.5 lg:hidden shrink-0">
            <i data-lucide="menu" class="w-6 h-6"></i>
        </button>

        <p class="text-xs text-gray-400 truncate">
            AgriStock / <span class="text-black font-bold">
                @php
                    $pageTitle = match(true) {
                        request()->routeIs('dashboard') => 'Dashboard',

                        request()->routeIs('products.*', 'categories.*', 'units.*') => 'Product Management',

                        request()->routeIs('inventories.*', 'stock-outs.*', 'stock-adjustments.*', 'inventory-history.*') => 'Inventory Management',

                        request()->routeIs('suppliers.*') => 'Suppliers',
                        request()->routeIs('purchase-orders.*') => 'Purchase Orders',
                        request()->routeIs('reports.*') => 'Reports',
                        request()->routeIs('users.*') => 'User Management',
                        request()->routeIs('profile.edit') => 'Settings',

                        default => 'Dashboard',
                    };
                @endphp
                {{ $pageTitle }}
            </span>
        </p>
    </div>

    <div class="flex items-center gap-3 lg:gap-3 shrink-0">
        <button class="hidden sm:flex px-4 py-2 border rounded-lg text-sm hover:bg-gray-100 items-center gap-2">
            <i data-lucide="refresh-cw" class="w-4 h-4"></i>
            Sync
        </button>

        <div class="relative" x-data="{ notifOpen: false, showAllModal: false }">
            <button @click="notifOpen = !notifOpen"
                    class="relative w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors">
                <i data-lucide="bell" class="w-5 h-5 text-gray-600"></i>
                @if($unreadNotificationCount > 0)
                    <span class="absolute top-1 right-1 bg-red-500 text-white text-[10px] leading-none rounded-full min-w-[16px] h-[16px] flex items-center justify-center px-0.5">
                        {{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}
                    </span>
                @endif
            </button>

            <!-- Notification dropdown -->
            <div x-show="notifOpen"
                @click.outside="notifOpen = false"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute top-full right-0 mt-2 w-80 bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden z-50"
                style="display: none;">

                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                    <p class="text-sm font-semibold text-gray-800">Notifications</p>
                    @if($unreadNotificationCount > 0)
                        <form method="POST" action="{{ route('notifications.read-all') }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="text-xs text-green-600 hover:text-green-700 font-medium">
                                Mark all read
                            </button>
                        </form>
                    @endif
                </div>

                <div class="max-h-80 overflow-y-auto divide-y divide-gray-100">
                    @forelse($topbarNotifications->take(8) as $notification)
                        <div class="flex items-start gap-3 px-4 py-3 {{ $notification->read_at ? '' : 'bg-green-50/40' }}">
                            <x-notification-icon :type="$notification->data['type']" />
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-800">{{ $notification->data['title'] }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $notification->data['body'] }}</p>
                                <p class="text-[11px] text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                            </div>
                            @if(! $notification->read_at)
                                <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" title="Mark as read" class="text-gray-300 hover:text-green-600 shrink-0">
                                        <i data-lucide="check" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <div class="px-4 py-8 text-center">
                            <i data-lucide="bell-off" class="w-6 h-6 text-gray-300 mx-auto mb-2"></i>
                            <p class="text-sm text-gray-400">No notifications yet.</p>
                        </div>
                    @endforelse
                </div>

                @if($topbarNotifications->count() > 0)
                    <button @click="notifOpen = false; showAllModal = true; $nextTick(() => lucide.createIcons())"
                            class="w-full text-center text-xs font-medium text-green-600 hover:text-green-700 hover:bg-gray-50 px-4 py-3 border-t border-gray-100 transition-colors">
                        View all notifications
                    </button>
                @endif

            </div>

            <!-- View All Notifications Modal -->
            <div x-show="showAllModal"
                x-transition:enter="transition-opacity ease-out duration-200"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-in duration-150"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
                style="display: none;" x-cloak>
                <div @click.outside="showAllModal = false"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden max-h-[85vh] flex flex-col">

                    <!-- Header -->
                    <div class="flex items-center justify-between px-6 pt-6 pb-4 shrink-0 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-green-50 text-green-600 flex items-center justify-center shrink-0">
                                <i data-lucide="bell" class="w-4.5 h-4.5"></i>
                            </div>
                            <div>
                                <h2 class="font-semibold text-gray-800 text-base leading-tight">All Notifications</h2>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $topbarNotifications->count() }} total · {{ $unreadNotificationCount }} unread</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($unreadNotificationCount > 0)
                                <form method="POST" action="{{ route('notifications.read-all') }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-xs text-green-600 hover:text-green-700 font-medium px-3 py-1.5 rounded-lg transition-colors">
                                        Mark all read
                                    </button>
                                </form>
                            @endif
                            <button type="button" @click="showAllModal = false"
                                    class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg p-1.5 transition-colors">
                                <i data-lucide="x" class="w-4.5 h-4.5"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="overflow-y-auto divide-y divide-gray-100">
                        @forelse($topbarNotifications as $notification)
                            <div class="flex items-start gap-3.5 px-6 py-4 {{ $notification->read_at ? '' : 'bg-green-50/40' }}">
                                <x-notification-icon :type="$notification->data['type']" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-800">{{ $notification->data['title'] }}</p>
                                    <p class="text-sm text-gray-500 mt-0.5">{{ $notification->data['body'] }}</p>
                                    <p class="text-xs text-gray-400 mt-1.5">{{ $notification->created_at->diffForHumans() }}</p>
                                </div>
                                @if(! $notification->read_at)
                                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit" title="Mark as read" class="text-gray-300 hover:text-green-600 shrink-0 mt-1">
                                            <i data-lucide="check" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @empty
                            <div class="px-6 py-16 text-center">
                                <i data-lucide="bell-off" class="w-8 h-8 text-gray-300 mx-auto mb-2"></i>
                                <p class="text-sm text-gray-400">No notifications yet.</p>
                            </div>
                        @endforelse
                    </div>

                </div>
            </div>
        </div>

        <div class="relative" x-data="{ userMenuOpen: false }">
            <button @click="userMenuOpen = !userMenuOpen"
                    class="rounded-full hover:ring-2 hover:ring-green-600 transition-all shrink-0">
                <x-avatar size="w-10 h-10" text-size="text-sm" />
            </button>

            <!-- Dropdown card -->
            <div x-show="userMenuOpen"
                @click.outside="userMenuOpen = false"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute top-full right-0 mt-2 w-64 bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden z-50"
                style="display: none;">

                <!-- Identity block -->
                <div class="flex items-center gap-3 p-4 bg-gray-50">
                    <x-avatar size="w-11 h-11" text-size="text-base" />
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-800 truncate">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                        @php
                            $roleBadge = match(Auth::user()->role->value) {
                                'Admin' => 'bg-purple-100 text-purple-700',
                                'Manager' => 'bg-blue-100 text-blue-700',
                                default => 'bg-gray-200 text-gray-700',
                            };
                        @endphp
                        <span class="inline-block mt-1 text-[11px] font-bold px-2 py-0.5 rounded-full {{ $roleBadge }}">
                            {{ Auth::user()->role->value }}
                        </span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="py-1.5">
                    <a href="{{ route('landing') }}"
                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <i data-lucide="home" class="w-4 h-4 text-gray-400"></i>
                        Landing Page
                    </a>
                    <a href="{{ route('profile.edit') }}"
                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <i data-lucide="pencil" class="w-4 h-4 text-gray-400"></i>
                        Edit Profile
                    </a>
                    <a href="{{ route('profile.edit') }}"
                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <i data-lucide="settings" class="w-4 h-4 text-gray-400"></i>
                        Settings
                    </a>
                </div>

                <div class="border-t border-gray-100 py-1.5">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                            Sign out
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

</header>
