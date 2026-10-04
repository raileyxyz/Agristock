<header class="h-16 bg-white dark:bg-[#0B0F0D] border-b border-gray-200 dark:border-[#27332C] flex items-center justify-between px-4 lg:px-8 gap-3">

    <!-- Left side: Mobile Menu Button & Breadcrumb -->
    <div class="flex items-center gap-3 min-w-0">
        <button @click="sidebarOpen = !sidebarOpen" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 p-1.5 -ml-1.5 lg:hidden shrink-0 transition-colors">
            <i data-lucide="menu" class="w-6 h-6"></i>
        </button>

        <p class="text-xs text-gray-400 dark:text-gray-400 truncate">
            AgriStock / <span class="text-black dark:text-gray-100 font-bold">
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

    <!-- Right side: Actions & User Menu -->
    <div class="flex items-center gap-2 lg:gap-3 shrink-0">

        <!-- Dark Mode Toggle Button -->
        <button @click="
                document.documentElement.classList.add('[&_*]:!transition-none');
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('color-theme', 'light');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('color-theme', 'dark');
                }
                window.dispatchEvent(new CustomEvent('theme-changed'));
                setTimeout(() => {
                    document.documentElement.classList.remove('[&_*]:!transition-none');
                }, 50);
            "
            type="button"
            class="relative w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-[#161F1A] transition-colors">
            <i data-lucide="sun" class="w-5 h-5 hidden dark:block text-gray-600 dark:text-gray-400 dark:hover:text-gray-100"></i>
            <i data-lucide="moon" class="w-5 h-5 block dark:hidden text-gray-600 dark:text-gray-400 dark:hover:text-gray-100"></i>
        </button>

        <!-- Notification Bell Dropdown -->
        <div class="relative" x-data="{ notifOpen: false, showAllModal: false }">
            <button @click="notifOpen = !notifOpen"
                    class="relative w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-[#161F1A] transition-colors">
                <i data-lucide="bell" class="w-5 h-5 text-gray-600 dark:text-gray-400 dark:hover:text-gray-100"></i>
                @if(isset($unreadNotificationCount) && $unreadNotificationCount > 0)
                    <span class="absolute top-1 right-1 bg-red-500 text-white text-[10px] leading-none rounded-full min-w-[16px] h-[16px] flex items-center justify-center px-0.5">
                        {{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}
                    </span>
                @endif
            </button>

            <!-- Notification Dropdown -->
            <div x-show="notifOpen"
                @click.outside="notifOpen = false"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="fixed inset-x-3 top-[4.5rem] origin-top
                       sm:absolute sm:inset-x-auto sm:top-full sm:right-0 sm:mt-2 sm:w-80 sm:origin-top-right
                       bg-white dark:bg-[#161D19] border border-gray-200 dark:border-[#27332C] rounded-xl shadow-lg overflow-hidden z-50"
                style="display: none;">

                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-[#27332C]">
                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">Notifications</p>
                    @if(isset($unreadNotificationCount) && $unreadNotificationCount > 0)
                        <form method="POST" action="{{ route('notifications.read-all') }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="text-xs text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 font-medium">
                                Mark all read
                            </button>
                        </form>
                    @endif
                </div>

                <div class="max-h-[60vh] sm:max-h-80 overflow-y-auto divide-y divide-gray-100 dark:divide-[#27332C]">
                    @forelse($topbarNotifications ?? [] as $notification)
                        <div class="flex items-start gap-3 px-4 py-3 {{ $notification->read_at ? '' : 'bg-green-50/40 dark:bg-green-950/20' }}">
                            <x-notification-icon :type="$notification->data['type']" />
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ $notification->data['title'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $notification->data['body'] }}</p>
                                <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                            </div>
                            @if(! $notification->read_at)
                                <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" title="Mark as read" class="text-gray-300 dark:text-gray-600 hover:text-green-600 dark:hover:text-green-400 shrink-0">
                                        <i data-lucide="check" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <div class="px-4 py-8 text-center">
                            <i data-lucide="bell-off" class="w-6 h-6 text-gray-300 dark:text-gray-600 mx-auto mb-2"></i>
                            <p class="text-sm text-gray-400 dark:text-gray-400">No notifications yet.</p>
                        </div>
                    @endforelse
                </div>

                @if(isset($topbarNotifications) && $topbarNotifications->count() > 0)
                    <button @click="notifOpen = false; showAllModal = true; $nextTick(() => lucide.createIcons())"
                            class="w-full text-center text-xs font-medium text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 hover:bg-gray-50 dark:hover:bg-[#1C2621] px-4 py-3 border-t border-gray-100 dark:border-[#27332C] transition-colors">
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
                class="fixed inset-0 bg-black/50 dark:bg-[#0B0F0D]/80 backdrop-blur-sm z-50 flex items-center justify-center p-4"
                style="display: none;" x-cloak>
                <div @click.outside="showAllModal = false"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    class="bg-white dark:bg-[#161D19] rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden max-h-[85vh] flex flex-col border dark:border-[#27332C]">

                    <!-- Modal Header -->
                    <div class="flex items-center justify-between px-6 pt-6 pb-4 shrink-0 border-b border-gray-100 dark:border-[#27332C]">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-green-50 dark:bg-green-950/40 text-green-600 dark:text-green-400 flex items-center justify-center shrink-0">
                                <i data-lucide="bell" class="w-4.5 h-4.5"></i>
                            </div>
                            <div>
                                <h2 class="font-semibold text-gray-800 dark:text-gray-100 text-base leading-tight">All Notifications</h2>
                                <p class="text-xs text-gray-400 dark:text-gray-400 mt-0.5">{{ $topbarNotifications->count() ?? 0 }} total · {{ $unreadNotificationCount ?? 0 }} unread</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if(isset($unreadNotificationCount) && $unreadNotificationCount > 0)
                                <form method="POST" action="{{ route('notifications.read-all') }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-xs text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 font-medium px-3 py-1.5 rounded-lg transition-colors">
                                        Mark all read
                                    </button>
                                </form>
                            @endif
                            <button type="button" @click="showAllModal = false"
                                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-[#1C2621] rounded-lg p-1.5 transition-colors">
                                <i data-lucide="x" class="w-4.5 h-4.5"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="overflow-y-auto divide-y divide-gray-100 dark:divide-[#27332C]">
                        @forelse($topbarNotifications ?? [] as $notification)
                            <div class="flex items-start gap-3.5 px-6 py-4 {{ $notification->read_at ? '' : 'bg-green-50/40 dark:bg-green-950/20' }}">
                                <x-notification-icon :type="$notification->data['type']" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ $notification->data['title'] }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $notification->data['body'] }}</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1.5">{{ $notification->created_at->diffForHumans() }}</p>
                                </div>
                                @if(! $notification->read_at)
                                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit" title="Mark as read" class="text-gray-300 dark:text-gray-600 hover:text-green-600 dark:hover:text-green-400 shrink-0 mt-1">
                                            <i data-lucide="check" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @empty
                            <div class="px-6 py-16 text-center">
                                <i data-lucide="bell-off" class="w-8 h-8 text-gray-300 dark:text-gray-600 mx-auto mb-2"></i>
                                <p class="text-sm text-gray-400 dark:text-gray-400">No notifications yet.</p>
                            </div>
                        @endforelse
                    </div>

                </div>
            </div>
        </div>

        <!-- User Menu Dropdown -->
        <div class="relative" x-data="{ userMenuOpen: false }">
            <button @click="userMenuOpen = !userMenuOpen"
                    class="rounded-full hover:ring-2 hover:ring-green-600 dark:hover:ring-green-400 transition-all shrink-0">
                <x-avatar size="w-10 h-10" text-size="text-sm" />
            </button>

            <!-- Dropdown Card -->
            <div x-show="userMenuOpen"
                @click.outside="userMenuOpen = false"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute top-full right-0 mt-2 w-64 bg-white dark:bg-[#161D19] border border-gray-200 dark:border-[#27332C] rounded-xl shadow-lg overflow-hidden z-50"
                style="display: none;">

                <!-- Identity Block -->
                <div class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-[#1C2621]">
                    <x-avatar size="w-11 h-11" text-size="text-base" />
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ Auth::user()->email }}</p>
                        @php
                            $roleBadge = match(Auth::user()->role->value) {
                                'Admin' => 'bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300',
                                'Manager' => 'bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300',
                                default => 'bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300',
                            };
                        @endphp
                        <span class="inline-block mt-1 text-[11px] font-bold px-2 py-0.5 rounded-full {{ $roleBadge }}">
                            {{ Auth::user()->role->value }}
                        </span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="py-1.5">
                    <a href="{{ route('profile.edit', ['tab' => 'notifications']) }}"
                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-[#1C2621] transition-colors">
                        <i data-lucide="bell" class="w-4 h-4 text-gray-400 dark:text-gray-400"></i>
                        Notifications
                    </a>
                    <a href="{{ route('profile.edit') }}"
                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-[#1C2621] transition-colors">
                        <i data-lucide="settings" class="w-4 h-4 text-gray-400 dark:text-gray-400"></i>
                        Settings
                    </a>
                </div>

                <div class="border-t border-gray-100 dark:border-[#27332C] py-1.5">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/40 transition-colors">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                            Sign out
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

</header>
