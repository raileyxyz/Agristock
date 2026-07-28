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

                        request()->routeIs('inventory.*') => 'Inventory Management',

                        request()->routeIs('suppliers.*') => 'Suppliers',
                        request()->routeIs('purchase-orders.*') => 'Purchase Orders',
                        request()->routeIs('reports.*') => 'Reports',
                        request()->routeIs('users.*') => 'User Management',

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

        <div class="w-10 h-10 rounded-full bg-green-600 text-white flex items-center justify-center font-semibold shrink-0">
            {{ strtoupper(substr(Auth::user()->name,0,2)) }}
        </div>
    </div>

</header>
