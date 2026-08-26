<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AgriStock</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|playfair-display:700,800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Figtree', sans-serif; }
        h1, h2, h3, .font-display { font-family: 'Playfair Display', serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-white text-gray-800">

    <!-- Nav -->
    <div
        x-data="{ mobileMenuOpen: false }"
        x-effect="document.body.style.overflow = mobileMenuOpen ? 'hidden' : ''"
        @keydown.escape.window="mobileMenuOpen = false">

        <header class="border-b border-gray-100 sticky top-0 bg-white/90 backdrop-blur-sm z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-10 h-16 sm:h-20 flex items-center justify-between">
                <a href="#top" class="group flex items-center gap-2.5">
                    <div class="w-8 h-8 sm:w-9 sm:h-9 bg-green-600 rounded-lg flex items-center justify-center transition-all duration-300 ease-out group-hover:scale-110 group-hover:rotate-3 group-hover:shadow-md group-hover:shadow-green-700/30">
                        <i data-lucide="leaf" class="w-4.5 h-4.5 sm:w-5 sm:h-5 text-white"></i>
                    </div>
                    <span class="font-display font-bold text-base sm:text-lg text-gray-900 transition-colors duration-300 group-hover:text-green-700">AgriStock</span>
                </a>

                <nav class="hidden md:flex items-center gap-10 text-sm text-gray-600">
                    <a href="#features" class="hover:text-gray-900 transition-colors">Features</a>
                    <a href="#how-it-works" class="hover:text-gray-900 transition-colors">How It Works</a>
                    <a href="#about" class="hover:text-gray-900 transition-colors">About</a>
                </nav>

                <div class="flex items-center gap-2">
                    <a href="{{ route('login') }}"
                        class="hidden md:flex bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold items-center gap-2 transition-all duration-300 ease-out transform hover:-translate-y-0.5 hover:shadow-lg">
                        Open System
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>

                    <!-- Hamburger toggle -->
                    <button type="button"
                            @click="mobileMenuOpen = true"
                            aria-label="Open menu"
                            class="md:hidden text-gray-700 hover:text-green-700 hover:bg-gray-100 rounded-lg p-2 transition-colors">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>
        </header>

        <div x-show="mobileMenuOpen" class="md:hidden fixed inset-0 z-[60]" x-cloak>

            <!-- Backdrop -->
            <div x-show="mobileMenuOpen"
                x-transition:enter="transition-opacity ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="mobileMenuOpen = false"
                class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm"></div>

            <!-- Slide-in panel -->
            <div x-show="mobileMenuOpen"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
                @click.outside="mobileMenuOpen = false"
                class="fixed inset-y-0 right-0 w-full max-w-xs bg-white shadow-2xl flex flex-col h-full">

                <!-- Panel header -->
                <div class="h-16 sm:h-20 px-5 flex items-center justify-between border-b border-gray-100 shrink-0">
                    <span class="flex items-center gap-2.5">
                        <div class="w-8 h-8 bg-green-700 rounded-lg flex items-center justify-center">
                            <i data-lucide="leaf" class="w-4 h-4 text-white"></i>
                        </div>
                        <span class="font-display font-bold text-base text-gray-900">AgriStock</span>
                    </span>
                    <button @click="mobileMenuOpen = false" aria-label="Close menu"
                            class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg p-1.5 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Links -->
                <nav class="flex-1 px-4 py-5 space-y-1 overflow-y-auto">
                    <a href="#features" @click="mobileMenuOpen = false"
                       class="flex items-center gap-3 px-3 py-3 rounded-lg text-[15px] font-medium text-gray-700 hover:bg-gray-50 hover:text-green-700 transition-colors">
                        Features
                    </a>
                    <a href="#how-it-works" @click="mobileMenuOpen = false"
                       class="flex items-center gap-3 px-3 py-3 rounded-lg text-[15px] font-medium text-gray-700 hover:bg-gray-50 hover:text-green-700 transition-colors">
                        How It Works
                    </a>
                    <a href="#about" @click="mobileMenuOpen = false"
                       class="flex items-center gap-3 px-3 py-3 rounded-lg text-[15px] font-medium text-gray-700 hover:bg-gray-50 hover:text-green-700 transition-colors">
                        About
                    </a>
                </nav>

                <!-- CTA footer -->
                <div class="p-4 border-t border-gray-100 shrink-0">
                    <a href="{{ route('login') }}"
                       class="bg-green-600 hover:bg-green-700 text-white px-5 py-3 rounded-lg text-sm font-semibold flex items-center justify-center gap-2 transition-colors w-full">
                        Open System
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Hero -->
    <section id="top" class="relative overflow-x-hidden bg-cover bg-center"
            style="background-image: url('{{ asset('images/login-farm.jpg') }}');">

        <!-- Dark green overlay -->
        <div class="absolute inset-0 bg-gradient-to-br from-green-950/90 via-green-900/85 to-green-800/80"></div>

        <div class="relative w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-10 py-12 sm:py-20 lg:py-28 grid lg:grid-cols-2 gap-8 sm:gap-16 items-center">

            <div class="min-w-0">
                <span class="inline-flex max-w-full items-center gap-2 bg-green-800/60 border border-green-600/40 text-green-100 text-[11px] sm:text-xs font-medium px-3 sm:px-3.5 py-1.5 rounded-full mb-4 sm:mb-7">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-400 shrink-0"></span>
                    <span class="truncate">Agriculture Inventory System</span>
                </span>

                <h1 class="font-display text-[28px] sm:text-5xl lg:text-6xl font-bold leading-[1.15] sm:leading-[1.05] text-white mb-0.5 sm:mb-2 break-words">
                    Farm Smarter.
                </h1>
                <h1 class="font-display text-[28px] sm:text-5xl lg:text-6xl font-bold leading-[1.15] sm:leading-[1.05] text-green-400 mb-3 sm:mb-6 break-words">
                    Stock Wiser.
                </h1>

                <p class="text-green-100/90 text-sm sm:text-lg leading-relaxed mb-6 sm:mb-8 max-w-lg">
                    AgriStock gives farm managers full visibility over every input — from seeds to fertilizers to equipment — with real-time alerts, expiration tracking, and supplier management in one clean system.
                </p>

                <div class="flex flex-col sm:flex-row gap-3 mb-6 sm:mb-8">
                    <a href="{{ route('login') }}"
                        class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-6 py-3.5 rounded-lg font-semibold flex items-center justify-center gap-2 transition-all duration-300 ease-out hover:-translate-y-0.5 hover:shadow-lg">
                        <span>Launch Dashboard</span>
                        <i data-lucide="arrow-right" class="w-4 h-4 shrink-0"></i>
                    </a>
                    <a href="#features"
                        class="w-full sm:w-auto bg-white/10 hover:bg-white/20 border border-white/20 text-white px-6 py-3.5 rounded-lg font-semibold flex items-center justify-center gap-2 transition-all duration-300 ease-out hover:-translate-y-0.5 hover:shadow-lg">
                        <span>Explore Features</span>
                    </a>
                </div>

                <div class="grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-x-4 gap-y-2.5 sm:gap-x-6 sm:gap-y-2 text-xs sm:text-sm text-green-200">
                    <span class="flex items-center gap-1.5 min-w-0"><i data-lucide="check-circle" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-green-400 shrink-0"></i> <span class="truncate">Low Stock Alerts</span></span>
                    <span class="flex items-center gap-1.5 min-w-0"><i data-lucide="check-circle" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-green-400 shrink-0"></i> <span class="truncate">Expiry Monitoring</span></span>
                    <span class="flex items-center gap-1.5 min-w-0"><i data-lucide="check-circle" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-green-400 shrink-0"></i> <span class="truncate">Purchase Orders</span></span>
                    <span class="flex items-center gap-1.5 min-w-0"><i data-lucide="check-circle" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-green-400 shrink-0"></i> <span class="truncate">Inventory History</span></span>
                    <span class="flex items-center gap-1.5 min-w-0 col-span-2 sm:col-span-1"><i data-lucide="check-circle" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-green-400 shrink-0"></i> <span class="truncate">Multi-user Access</span></span>
                </div>
            </div>

            <!-- Live inventory snapshot card -->
            <div class="min-w-0 bg-white/10 backdrop-blur-md border border-white/15 rounded-2xl p-4 sm:p-6 shadow-2xl">
                <p class="text-xs font-semibold tracking-wider text-green-200 uppercase mb-4">Live Inventory Snapshot</p>

                <div class="space-y-2.5">
                    @php
                        $items = [
                            ['name' => 'Hybrid Maize Seeds DK-8031', 'qty' => '42 kg', 'status' => 'Low Stock', 'dot' => 'bg-amber-400', 'badge' => 'bg-amber-400/20 text-amber-300'],
                            ['name' => 'Urea 46% N', 'qty' => '95 bags', 'status' => 'In Stock', 'dot' => 'bg-green-400', 'badge' => 'bg-green-400/20 text-green-300'],
                            ['name' => 'Imidacloprid 70WS', 'qty' => '5 kg', 'status' => 'Critical', 'dot' => 'bg-red-400', 'badge' => 'bg-red-400/20 text-red-300'],
                            ['name' => 'Drip Tape 1.6mm', 'qty' => '800 m', 'status' => 'In Stock', 'dot' => 'bg-green-400', 'badge' => 'bg-green-400/20 text-green-300'],
                            ['name' => 'Chlorpyrifos EC', 'qty' => '60 L', 'status' => 'Expiring', 'dot' => 'bg-orange-400', 'badge' => 'bg-orange-400/20 text-orange-300'],
                        ];
                    @endphp

                    @foreach($items as $item)
                        <div class="flex items-center justify-between gap-2 bg-white/5 rounded-lg px-3 sm:px-4 py-3">
                            <span class="flex items-center gap-2.5 text-sm text-white min-w-0 truncate">
                                <span class="w-2 h-2 rounded-full {{ $item['dot'] }} shrink-0"></span>
                                <span class="truncate">{{ $item['name'] }}</span>
                            </span>
                            <span class="flex items-center gap-2 sm:gap-3 shrink-0">
                                <span class="text-sm text-green-200/80 whitespace-nowrap">{{ $item['qty'] }}</span>
                                <span class="text-xs font-medium px-2 py-1 rounded-md whitespace-nowrap {{ $item['badge'] }}">{{ $item['status'] }}</span>
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </section>

    <!-- Features -->
    <section id="features" class="py-16 sm:py-24 px-4 sm:px-6 lg:px-10">
        <div class="max-w-6xl mx-auto text-center mb-12 sm:mb-16">
            <span class="inline-flex items-center gap-2 bg-green-50 text-green-700 text-xs font-semibold px-3.5 py-1.5 rounded-full mb-5 sm:mb-6">
                <i data-lucide="leaf" class="w-3.5 h-3.5"></i> Built for Philippine Farms
            </span>
            <h2 class="font-display text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
                Everything your farm inventory needs
            </h2>
            <p class="text-gray-500 text-base sm:text-lg max-w-2xl mx-auto">
                From tracking seed batches to managing purchase orders, AgriStock covers the full lifecycle of your agricultural inputs.
            </p>
        </div>

        <div class="max-w-6xl mx-auto grid sm:grid-cols-2 md:grid-cols-3 gap-5">
            @php
                $features = [
                    ['icon' => 'package', 'title' => 'Product Catalog', 'desc' => 'Manage your complete agri-input catalog — seeds, fertilizers, pesticides, equipment, and feed — with categories and units.'],
                    ['icon' => 'bar-chart-3', 'title' => 'Inventory Tracking', 'desc' => 'Real-time stock monitoring with Stock In, Stock Out, and Adjustment workflows. Every movement is logged with reference and user.'],
                    ['icon' => 'triangle-alert', 'title' => 'Low Stock Alerts', 'desc' => 'Automatic alerts when items drop below reorder points. Never face planting season without critical inputs again.'],
                    ['icon' => 'clock', 'title' => 'Expiration Monitor', 'desc' => 'Track expiry dates on seeds, pesticides, and biologicals — get ahead of spoilage before it costs you.'],
                    ['icon' => 'shield', 'title' => 'Supplier Management', 'desc' => 'Keep a directory of suppliers, contacts, and purchase history all in one searchable place.'],
                    ['icon' => 'users', 'title' => 'User and Role Control', 'desc' => 'Give staff the right level of access — admin, manager, or field staff — with clear permission boundaries.'],
                ];
            @endphp

            @foreach($features as $feature)
                <div class="group border border-gray-200 rounded-xl p-6 transition-all duration-300 hover:border-green-400 hover:-translate-y-1.5 hover:shadow-lg">
                    <div class="w-11 h-11 bg-green-50 rounded-lg flex items-center justify-center mb-5 transition-colors duration-300 group-hover:bg-green-100">
                        <i data-lucide="{{ $feature['icon'] }}" class="w-5 h-5 text-green-700 transition-colors duration-300"></i>
                    </div>
                    <h3 class="font-display font-bold text-lg text-gray-900 mb-2">{!! $feature['title'] !!}</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">{{ $feature['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <!-- How It Works -->
    <section id="how-it-works" class="py-16 sm:py-24 px-4 sm:px-6 lg:px-10 bg-gray-50">
        <div class="max-w-4xl mx-auto text-center mb-12 sm:mb-16">
            <h2 class="font-display text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 mb-4">How AgriStock Works</h2>
            <p class="text-gray-500 text-base sm:text-lg">A simple 4-step process that keeps your farm operations running smoothly.</p>
        </div>

        <div class="max-w-6xl mx-auto grid sm:grid-cols-2 md:grid-cols-4 gap-6 relative">
            @php
                $steps = [
                    ['num' => '01', 'title' => 'Build Your Catalog', 'desc' => 'Add products with categories, units, and reorder thresholds. No stock quantities here — just your product master list.'],
                    ['num' => '02', 'title' => 'Receive and Move Stock', 'desc' => 'Use Stock In when supplies arrive and Stock Out when used in the field. Every transaction logs user, date, and reference.'],
                    ['num' => '03', 'title' => 'Get Alerts', 'desc' => 'The dashboard flags low stock, critical levels, and items nearing expiry so you always act before there\'s a crisis.'],
                    ['num' => '04', 'title' => 'Order and Analyze', 'desc' => 'Create purchase orders, track supplier deliveries, and review reports to spot trends and optimize spending.'],
                ];
            @endphp

            @foreach($steps as $i => $step)
                <div class="group bg-white rounded-xl p-6 border border-gray-200 relative transition-all duration-300 hover:border-green-400 hover:-translate-y-1.5 hover:shadow-lg">
                    <span class="font-display text-4xl font-bold text-green-300 block mb-3 transition-colors duration-300 group-hover:text-green-500">{{ $step['num'] }}</span>
                    <h3 class="font-display font-bold text-gray-900 mb-2">{!! $step['title'] !!}</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">{{ $step['desc'] }}</p>

                    @if($i < count($steps) - 1)
                        <i data-lucide="arrow-right" class="w-5 h-5 text-green-400 absolute -right-3 top-1/2 -translate-y-1/2 hidden md:block bg-gray-50 rounded-full"></i>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    <!-- About / Image band -->
    <section id="about">
        <div class="grid grid-cols-1 md:grid-cols-3 h-56 sm:h-72 overflow-hidden">
            @php
                $galleryImages = [
                    'images/gallery-1.jpg',
                    'images/gallery-2.jpg',
                    'images/gallery-3.jpg',
                ];
            @endphp

            @foreach($galleryImages as $img)
                <div class="relative overflow-hidden group cursor-pointer">
                    <img src="{{ asset($img) }}" alt="AgriStock farm"
                        class="w-full h-full object-cover grayscale-[30%] opacity-85 scale-105 transition-all duration-500 group-hover:grayscale-0 group-hover:opacity-100 group-hover:scale-110">
                </div>
            @endforeach
        </div>

        <div class="bg-green-700 text-center py-14 sm:py-20 px-4 sm:px-6">
            <h2 class="font-display text-2xl sm:text-3xl lg:text-4xl font-bold text-white mb-4">
                Ready to take control of your farm inventory?
            </h2>
            <p class="text-green-100 text-base sm:text-lg max-w-2xl mx-auto mb-8">
                Join hundreds of farms across the Philippines using AgriStock to eliminate stockouts, reduce waste, and stay on top of every input.
            </p>
            <a href="{{ route('login') }}"
                class="inline-flex items-center gap-2 bg-white hover:bg-green-50 text-green-800 px-7 py-3.5 rounded-lg font-semibold transition-all duration-300 ease-out hover:-translate-y-1 hover:shadow-lg">
                Open AgriStock Dashboard
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-green-950 text-green-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-10 py-12 sm:py-16 grid sm:grid-cols-2 md:grid-cols-4 gap-8 sm:gap-10">

            <div class="sm:col-span-2">
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-9 h-9 bg-green-600 rounded-lg flex items-center justify-center">
                        <i data-lucide="leaf" class="w-5 h-5 text-white"></i>
                    </div>
                    <span class="font-display font-bold text-lg text-white">AgriStock</span>
                </div>
                <p class="text-green-300 text-sm leading-relaxed max-w-sm">
                    Full visibility over every farm input — from seeds to fertilizers to equipment — built for Philippine agriculture.
                </p>
            </div>

            <div>
                <p class="text-white font-semibold text-sm mb-4">Product</p>
                <ul class="space-y-2.5 text-sm text-green-300">
                    <li><a href="#features" class="hover:text-white transition-colors">Features</a></li>
                    <li><a href="#how-it-works" class="hover:text-white transition-colors">How It Works</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-white transition-colors">Open System</a></li>
                </ul>
            </div>

            <div>
                <p class="text-white font-semibold text-sm mb-4">Company</p>
                <ul class="space-y-2.5 text-sm text-green-300">
                    <li><a href="#about" class="hover:text-white transition-colors">About</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Contact</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Privacy Policy</a></li>
                </ul>
            </div>

        </div>

        <div class="border-t border-green-800/60">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-10 py-6 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-green-400 text-xs text-center sm:text-left">© {{ date('Y') }} AgriStock · Agriculture Inventory Management System</p>
                <div class="flex items-center gap-4">
                    <a href="#" class="text-green-400 hover:text-white transition-colors"><i data-lucide="facebook" class="w-4 h-4"></i></a>
                    <a href="#" class="text-green-400 hover:text-white transition-colors"><i data-lucide="mail" class="w-4 h-4"></i></a>
                    <a href="#" class="text-green-400 hover:text-white transition-colors"><i data-lucide="phone" class="w-4 h-4"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
        document.addEventListener('alpine:updated', () => lucide.createIcons());
    </script>
</body>
</html>
