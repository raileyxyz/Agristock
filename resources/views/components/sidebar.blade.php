<aside
    x-show="sidebarOpen || isDesktop"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="-translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="-translate-x-full"
    @click.outside="sidebarOpen = false"
    class="fixed lg:static inset-y-0 left-0 z-40 w-66 bg-white border-r border-gray-200 flex flex-col h-screen transform lg:translate-x-0 shrink-0">

    <!-- Logo -->
    <div class="h-20 px-6 flex items-center justify-between border-b border-gray-100 shrink-0">
        <div class="flex items-center">
            <div class="w-10 h-10 bg-gradient-to-br from-green-600 to-green-700 rounded-xl flex items-center justify-center shadow-sm">
                <i data-lucide="leaf" class="w-5 h-5 text-white"></i>
            </div>
            <div class="ml-3">
                <h1 class="font-bold text-lg text-gray-800 leading-tight">AgriStock</h1>
                <p class="text-xs text-gray-400">Farm Inventory</p>
            </div>
        </div>
        <button @click="sidebarOpen = false" class="text-gray-400 hover:text-gray-600 lg:hidden">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
    </div>

    <!-- Menu -->
    <nav class="flex-1 px-3 py-4 overflow-y-auto"
        x-data="{
            open: '{{ request()->routeIs('products.*','categories.*','units.*') ? 'products'
                    : (request()->routeIs('inventories.*', 'stock-outs.*', 'stock-adjustments.*', 'inventory-history.*', 'low-stock.*') ? 'inventory'
                    : (request()->routeIs('suppliers.*') ? 'suppliers'
                    : (request()->routeIs('purchase-orders.*') ? 'orders'
                    : (request()->routeIs('reports.*') ? 'reports'
                    : (request()->routeIs('users.*') ? 'users' : ''))))) }}'
        }">

        <div class="space-y-2">

            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
            {{ request()->routeIs('dashboard')
                    ? 'bg-green-600 text-white shadow-sm'
                    : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                Dashboard
            </a>

            <!-- Product Management -->
            @canany(['products.view'])
            <div>
                <button @click="open = (open === 'products' ? '' : 'products')"
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                        {{ request()->routeIs('products.*','categories.*','units.*') ? 'text-green-700 bg-green-100 font-bold' : 'text-gray-700 hover:bg-gray-100' }}">
                    <span class="flex items-center gap-3">
                        <i data-lucide="package" class="w-4 h-4"></i>
                        Product Management
                    </span>
                    <i data-lucide="chevron-right" class="w-4 h-4 transition-transform duration-200" :class="open === 'products' && 'rotate-90'"></i>
                </button>

                <div x-show="open === 'products'" x-collapse class="mt-1 ml-[1.15rem] pl-4 border-l border-gray-150 space-y-0.5">
                    @can('products.view')
                    <a href="{{ route('products.index') }}" class="flex items-center gap-2.5 px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('products.index') ? 'bg-green-600 text-white font-medium' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100' }}">
                        <i data-lucide="eye" class="w-3.5 h-3.5"></i> All Products
                    </a>
                    @endcan
                    @can('products.create')
                    <a href="{{ route('products.create') }}" class="flex items-center gap-2.5 px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('products.create') ? 'bg-green-600 text-white font-medium' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100' }}">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add Product
                    </a>
                    @endcan
                    @can('products.view')
                    <a href="{{ route('categories.index') }}" class="flex items-center gap-2.5 px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('categories.index') ? 'bg-green-600 text-white font-medium' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100' }}">
                        <i data-lucide="tags" class="w-3.5 h-3.5"></i> Manage Categories
                    </a>
                    <a href="{{ route('units.index') }}" class="flex items-center gap-2.5 px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('units.*') ? 'bg-green-600 text-white font-medium' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100' }}">
                        <i data-lucide="ruler" class="w-3.5 h-3.5"></i> Units of Measurement
                    </a>
                    @endcan
                </div>
            </div>
            @endcanany

            <!-- Inventory Management -->
            @canany(['inventory.view'])
            <div>
                <button @click="open = (open === 'inventory' ? '' : 'inventory')"
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                        {{ request()->routeIs('inventories.*', 'stock-outs.*', 'stock-adjustments.*', 'inventory-history.*', 'low-stock.*')
                            ? 'text-green-700 bg-green-50 font-semibold'
                            : 'text-gray-600 hover:bg-gray-50' }}">
                    <span class="flex items-center gap-3 flex-1 min-w-0">
                        <i data-lucide="warehouse" class="w-4 h-4 shrink-0"></i>
                        <span class="truncate">Inventory Management</span>
                    </span>
                    <span class="flex items-center gap-2 shrink-0 pl-3">
                        @if(isset($lowStockCount) && $lowStockCount > 0)
                            <span class="bg-red-600 text-red-100 text-[8px] font-semibold min-w-[14px] h-[14px] px-1 flex items-center justify-center rounded-full">
                                {{ $lowStockCount }}
                            </span>
                        @endif
                        <i data-lucide="chevron-right"
                        class="w-3.5 h-3.5 text-gray-400 transition-transform"
                        :class="open === 'inventory' && 'rotate-90'"></i>
                    </span>
                </button>

                <div x-show="open === 'inventory'" x-collapse class="mt-1 ml-[1.15rem] pl-4 border-l border-gray-150 space-y-0.5">
                    <a href="{{ route('inventories.index') }}" class="flex items-center gap-2.5 px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('inventories.index') ? 'bg-green-600 text-white font-medium' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100' }}">
                        <i data-lucide="layers" class="w-3.5 h-3.5"></i> Current Stock
                    </a>
                    @can('inventory.stock-in')
                    <a href="{{ route('inventories.create') }}" class="flex items-center gap-2.5 px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('inventories.create') ? 'bg-green-600 text-white font-medium' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100' }}">
                        <i data-lucide="arrow-down-to-line" class="w-3.5 h-3.5"></i> Stock In
                    </a>
                    @endcan
                    @can('inventory.stock-out')
                    <a href="{{ route('stock-outs.create') }}" class="flex items-center gap-2.5 px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('stock-outs.create') ? 'bg-green-600 text-white font-medium' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100' }}">
                        <i data-lucide="arrow-up-from-line" class="w-3.5 h-3.5"></i> Stock Out
                    </a>
                    @endcan
                    @can('inventory.manage')
                    <a href="{{ route('stock-adjustments.create') }}" class="flex items-center gap-2.5 px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('stock-adjustments.create') ? 'bg-green-600 text-white font-medium' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100' }}">
                        <i data-lucide="sliders-horizontal" class="w-3.5 h-3.5"></i> Stock Adjustment
                    </a>
                    @endcan
                    <a href="{{ route('inventory-history.index') }}" class="flex items-center gap-2.5 px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('inventory-history.index') ? 'bg-green-600 text-white font-medium' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100' }}">
                        <i data-lucide="history" class="w-3.5 h-3.5"></i> Inventory History
                    </a>
                    <a href="{{ route('low-stock.index') }}" class="flex items-center justify-between px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('low-stock.index') ? 'bg-green-600 text-white font-medium' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100' }}">
                        <span class="flex items-center gap-2.5">
                            <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i> Low Stock Monitoring
                        </span>
                        @if(isset($lowStockCount) && $lowStockCount > 0)
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                        @endif
                    </a>
                </div>
            </div>
            @endcanany

            <!-- Suppliers -->
            @canany(['suppliers.view'])
            <div>
                <button @click="open = (open === 'suppliers' ? '' : 'suppliers')"
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                        {{ request()->routeIs('suppliers.*') ? 'text-green-700 bg-green-100 font-bold' : 'text-gray-700 hover:bg-gray-100' }}">
                    <span class="flex items-center gap-3">
                        <i data-lucide="truck" class="w-4 h-4"></i>
                        Suppliers
                    </span>
                    <i data-lucide="chevron-right" class="w-4 h-4 transition-transform duration-200" :class="open === 'suppliers' && 'rotate-90'"></i>
                </button>

                <div x-show="open === 'suppliers'" x-collapse class="mt-0.5 ml-[1.15rem] pl-4 border-l border-gray-150 space-y-0.5">
                    @can('suppliers.view')
                    <a href="{{ route('suppliers.index') }}" class="flex items-center gap-2.5 px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('suppliers.index') ? 'bg-green-600 text-white font-medium' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100' }}">
                        <i data-lucide="list" class="w-3.5 h-3.5"></i> All Suppliers
                    </a>
                    @endcan
                    @can('suppliers.create')
                    <a href="{{ route('suppliers.create') }}" class="flex items-center gap-2.5 px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('suppliers.create') ? 'bg-green-600 text-white font-medium' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100' }}">
                        <i data-lucide="user-plus" class="w-3.5 h-3.5"></i> Add Supplier
                    </a>
                    @endcan
                    @can('suppliers.view')
                    <a href="{{ route('suppliers.directory') }}" class="flex items-center gap-2.5 px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('suppliers.directory') ? 'bg-green-600 text-white font-medium' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100' }}">
                        <i data-lucide="book-user" class="w-3.5 h-3.5"></i> Contact Directory
                    </a>
                    @endcan
                </div>
            </div>
            @endcanany

            <!-- Purchase Orders -->
            <div>
                <button @click="open = (open === 'orders' ? '' : 'orders')"
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                        {{ request()->routeIs('purchase-orders.*') ? 'text-green-700' : 'text-gray-700 hover:bg-gray-100' }}">
                    <span class="flex items-center gap-3">
                        <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                        Purchase Orders
                    </span>
                    <i data-lucide="chevron-right" class="w-4 h-4 transition-transform duration-200" :class="open === 'orders' && 'rotate-90'"></i>
                </button>

                <div x-show="open === 'orders'" x-collapse class="mt-0.5 ml-[1.15rem] pl-4 border-l border-gray-150 space-y-0.5">
                    <a href="" class="flex items-center gap-2.5 px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('purchase-orders.index') ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100' }}">
                        <i data-lucide="list-checks" class="w-3.5 h-3.5"></i> All Orders
                    </a>
                    <a href="" class="flex items-center gap-2.5 px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('purchase-orders.create') ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100' }}">
                        <i data-lucide="file-plus" class="w-3.5 h-3.5"></i> Create PO
                    </a>
                    <a href="" class="flex items-center gap-2.5 px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('purchase-orders.history') ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100' }}">
                        <i data-lucide="history" class="w-3.5 h-3.5"></i> Order History
                    </a>
                </div>
            </div>

            <!-- Reports -->
            <div>
                <button @click="open = (open === 'reports' ? '' : 'reports')"
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                        {{ request()->routeIs('reports.*') ? 'text-green-700' : 'text-gray-700 hover:bg-gray-100' }}">
                    <span class="flex items-center gap-3">
                        <i data-lucide="chart-column" class="w-4 h-4"></i>
                        Reports
                    </span>
                    <i data-lucide="chevron-right" class="w-4 h-4 transition-transform duration-200" :class="open === 'reports' && 'rotate-90'"></i>
                </button>

                <div x-show="open === 'reports'" x-collapse class="mt-0.5 ml-[1.15rem] pl-4 border-l border-gray-150 space-y-0.5">
                    @can('reports.stock')
                    <a href="" class="flex items-center gap-2.5 px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('reports.stock') ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100' }}">
                        <i data-lucide="bar-chart-3" class="w-3.5 h-3.5"></i> Stock Report
                    </a>
                    @endcan
                    @can('reports.movement')
                    <a href="" class="flex items-center gap-2.5 px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('reports.movement') ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100' }}">
                        <i data-lucide="repeat" class="w-3.5 h-3.5"></i> Movement Report
                    </a>
                    @endcan
                    @can('reports.expiry')
                    <a href="" class="flex items-center gap-2.5 px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('reports.expiry') ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100' }}">
                        <i data-lucide="calendar-x" class="w-3.5 h-3.5"></i> Expiry Report
                    </a>
                    @endcan
                    @can('reports.purchase')
                    <a href="" class="flex items-center gap-2.5 px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('reports.purchase') ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100' }}">
                        <i data-lucide="receipt" class="w-3.5 h-3.5"></i> Purchase Report
                    </a>
                    @endcan
                </div>
            </div>

            <!-- User Management -->
            @can('viewAny', App\Models\User::class)
            <div>
                <button @click="open = (open === 'users' ? '' : 'users')"
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                        {{ request()->routeIs('users.*') ? 'text-green-700 bg-green-100 font-bold' : 'text-gray-700 hover:bg-gray-100' }}">
                    <span class="flex items-center gap-3">
                        <i data-lucide="users" class="w-4 h-4"></i>
                        User Management
                    </span>
                    <i data-lucide="chevron-right" class="w-4 h-4 transition-transform duration-200" :class="open === 'users' && 'rotate-90'"></i>
                </button>

                <div x-show="open === 'users'" x-collapse class="mt-0.5 ml-[1.15rem] pl-4 border-l border-gray-150 space-y-0.5">
                    @can('viewAny', App\Models\User::class)
                    <a href="{{ route('users.index') }}" class="flex items-center gap-2.5 px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('users.index') ? 'bg-green-600 text-white font-medium' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100' }}">
                        <i data-lucide="users" class="w-3.5 h-3.5"></i> All Users
                    </a>
                    @endcan
                    @can('create', App\Models\User::class)
                    <a href="{{ route('users.create') }}" class="flex items-center gap-2.5 px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('users.create') ? 'bg-green-600 text-white font-medium' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100' }}">
                        <i data-lucide="user-plus" class="w-3.5 h-3.5"></i> Add User
                    </a>
                    @endcan
                    @can('viewAny', App\Models\User::class)
                    <a href="{{ route('users.roles') }}" class="flex items-center gap-2.5 px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('users.roles') ? 'bg-green-600 text-white font-medium' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100' }}">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5"></i> Roles &amp; Permissions
                    </a>
                    @endcan
                </div>
            </div>
            @endcan

        </div>
    </nav>

    <!-- User -->
    <div class="border-t border-gray-100 p-4 shrink-0 relative" x-data="{ userMenuOpen: false }">

        <button @click="userMenuOpen = !userMenuOpen"
                class="flex items-center gap-3 px-1 w-full rounded-lg hover:bg-gray-50 transition-colors py-1"
                :class="userMenuOpen && 'bg-gray-50'">

            <div class="w-9 h-9 rounded-full bg-green-600 text-white flex items-center justify-center text-sm font-semibold shrink-0">
                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
            </div>

            <div class="min-w-0 text-left">
                <p class="text-sm font-semibold text-gray-800 truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-gray-500">{{ Auth::user()->role }}</p>
            </div>

            <i data-lucide="chevron-up" class="w-4 h-4 text-gray-400 ml-auto shrink-0 transition-transform duration-200"
            :class="userMenuOpen && 'rotate-180'"></i>
        </button>

        <!-- Dropdown card -->
        <div x-show="userMenuOpen"
            @click.outside="userMenuOpen = false"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute bottom-full left-4 right-4 mb-2 bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden"
            style="display: none;">

            <!-- Identity block -->
            <div class="flex items-center gap-3 p-4 bg-gray-50">
                <div class="w-11 h-11 rounded-full bg-green-600 text-white flex items-center justify-center font-semibold shrink-0">
                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                    <span class="inline-block mt-1 text-[11px] font-medium px-2 py-0.5 rounded-full bg-purple-100 text-purple-700">
                        {{ Auth::user()->role }}
                    </span>
                </div>
            </div>

            <!-- Actions -->
            <div class="py-1.5">
                <a href=""
                class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                    <i data-lucide="settings" class="w-4 h-4 text-gray-400"></i>
                    Account Settings
                </a>
                <a href=""
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

</aside>
