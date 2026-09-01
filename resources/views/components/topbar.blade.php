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

    <div class="flex items-center gap-3 lg:gap-5 shrink-0">
        <button class="hidden sm:flex px-4 py-2 border rounded-lg text-sm hover:bg-gray-100 items-center gap-2">
            <i data-lucide="refresh-cw" class="w-4 h-4"></i>
            Sync
        </button>

        <button class="relative">
            <i data-lucide="bell" class="w-5 h-5"></i>
            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                11
            </span>
        </button>

        <div class="relative" x-data="{ userMenuOpen: false }">
            <button @click="userMenuOpen = !userMenuOpen"
                    class="w-9 h-9 rounded-full bg-green-600 text-white flex items-center justify-center text-sm font-semibold shrink-0 hover:ring-2 hover:ring-green-200 transition-all">
                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
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
                    <div class="w-11 h-11 rounded-full bg-green-600 text-white flex items-center justify-center font-semibold shrink-0">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </div>
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
                    <a href="{{ route('profile.edit') }}"
                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <i data-lucide="settings" class="w-4 h-4 text-gray-400"></i>
                        Account Settings
                    </a>
                    <a href="{{ route('landing') }}"
                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <i data-lucide="home" class="w-4 h-4 text-gray-400"></i>
                        Landing Page
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
