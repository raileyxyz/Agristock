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
        section[id] { scroll-margin-top: 5rem; }
    </style>
</head>
<body class="bg-white text-gray-800"
    x-data="{ mobileMenuOpen: false, scrolled: false }"
    x-effect="document.body.style.overflow = mobileMenuOpen ? 'hidden' : ''"
    @scroll.window="scrolled = window.scrollY > 8"
    @keydown.escape.window="mobileMenuOpen = false">

    <!-- Nav -->
    <header class="sticky top-0 z-40 bg-white/80 backdrop-blur-md transition-shadow duration-300"
            :class="scrolled ? 'shadow-sm border-b border-gray-200/80' : 'border-b border-transparent'">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-10 h-16 sm:h-20 flex items-center justify-between">
            <a href="#top" class="group flex items-center gap-2.5">
                <div class="w-8 h-8 sm:w-9 sm:h-9 bg-green-600 rounded-lg flex items-center justify-center transition-all duration-300 ease-out group-hover:scale-110 group-hover:rotate-3 group-hover:shadow-md group-hover:shadow-green-700/30">
                    <i data-lucide="leaf" class="w-4.5 h-4.5 sm:w-5 sm:h-5 text-white"></i>
                </div>
                <span class="font-display font-bold text-base sm:text-lg text-gray-900 transition-colors duration-300 group-hover:text-green-700">AgriStock</span>
            </a>

            <nav class="hidden md:flex items-center gap-10 text-sm text-gray-600">
                <a href="#features" class="relative py-1 hover:text-gray-900 transition-colors after:absolute after:left-0 after:-bottom-0.5 after:h-0.5 after:w-0 after:bg-green-600 after:transition-all after:duration-300 hover:after:w-full">Features</a>
                <a href="#how-it-works" class="relative py-1 hover:text-gray-900 transition-colors after:absolute after:left-0 after:-bottom-0.5 after:h-0.5 after:w-0 after:bg-green-600 after:transition-all after:duration-300 hover:after:w-full">How It Works</a>
                <a href="#about" class="relative py-1 hover:text-gray-900 transition-colors after:absolute after:left-0 after:-bottom-0.5 after:h-0.5 after:w-0 after:bg-green-600 after:transition-all after:duration-300 hover:after:w-full">About</a>
            </nav>

            <div class="flex items-center gap-2">
                <a href="{{ route('login') }}"
                    class="group hidden md:flex bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold items-center gap-2 transition-all duration-300 ease-out transform hover:-translate-y-0.5 hover:shadow-lg">
                    Open System
                    <i data-lucide="arrow-right" class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1"></i>
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

    <!-- Mobile off-canvas menu -->
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
                    <div class="w-8 h-8 bg-green-600 rounded-lg flex items-center justify-center">
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
                class="group bg-green-600 hover:bg-green-700 text-white px-5 py-3 rounded-lg text-sm font-semibold flex items-center justify-center gap-2 transition-colors w-full">
                    Open System
                    <i data-lucide="arrow-right" class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Hero -->
    <section id="top" class="relative overflow-hidden bg-cover bg-center min-h-[560px] sm:min-h-[640px] lg:min-h-[720px] flex items-center"
            style="background-image: url('{{ asset('images/login-farm.jpg') }}');">

        <!-- Directional Overlay: Darkens the left side for high text contrast while letting the farm image shine through on the right -->
        <div class="absolute inset-0 bg-gradient-to-r from-green-950/95 via-green-950/80 to-green-950/40 lg:from-green-950/95 lg:via-green-950/75 lg:to-green-950/25"></div>

        <div class="relative w-full max-w-7xl mx-auto px-5 sm:px-8 lg:px-12 py-16 sm:py-20 lg:py-24 flex justify-start">

            <!-- Left-aligned Hero Content Container -->
            <div class="w-full md:max-w-xl lg:max-w-2xl min-w-0 text-left">

                <!-- Category Label -->
                <span class="inline-flex items-center gap-2 bg-green-900/60 border border-green-500/30 text-green-200 text-xs font-semibold px-3.5 py-1.5 rounded-full mb-6 backdrop-blur-sm">
                    <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse shrink-0"></span>
                    <span class="tracking-wide">Agriculture Inventory System</span>
                </span>

                <!-- Heading -->
                <h1 class="font-display text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-white mb-2 leading-[1.1] sm:leading-[1.08]">
                    Farm Smarter.
                </h1>
                <h1 class="font-display text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-green-400 mb-6 leading-[1.1] sm:leading-[1.08]">
                    Stock Wiser.
                </h1>

                <!-- Description -->
                <p class="text-green-100/90 text-base sm:text-lg lg:text-xl font-normal leading-relaxed mb-8 max-w-xl">
                    AgriStock gives farm managers full visibility over every input from seeds to fertilizers to equipment with real-time alerts, expiration tracking, and supplier management in one clean system.
                </p>

                <!-- Primary & Secondary CTA Buttons -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 sm:gap-4 mb-8">
                    <a href="{{ route('login') }}"
                        class="group bg-green-600 hover:bg-green-500 text-white px-7 py-3.5 rounded-lg text-base font-semibold flex items-center justify-center gap-2.5 transition-all duration-300 ease-out hover:-translate-y-0.5 shadow-lg shadow-green-950/50 hover:shadow-green-600/30">
                        <span>Launch Dashboard</span>
                        <i data-lucide="arrow-right" class="w-5 h-5 shrink-0 transition-transform duration-300 group-hover:translate-x-1"></i>
                    </a>
                    <a href="#features"
                        class="bg-white/10 hover:bg-white/20 border border-white/25 text-white px-7 py-3.5 rounded-lg text-base font-semibold flex items-center justify-center gap-2 transition-all duration-300 ease-out hover:-translate-y-0.5 backdrop-blur-sm">
                        <span>Explore Features</span>
                    </a>
                </div>

                <!-- Feature Highlights -->
                <div class="flex flex-wrap items-center gap-x-6 gap-y-3 text-xs sm:text-sm font-medium text-green-100/90">
                    <span class="flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4 text-green-400 shrink-0"></i>
                        <span>Low Stock Alerts</span>
                    </span>
                    <span class="flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4 text-green-400 shrink-0"></i>
                        <span>Expiry Monitoring</span>
                    </span>
                    <span class="flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4 text-green-400 shrink-0"></i>
                        <span>Inventory History</span>
                    </span>
                    <span class="flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4 text-green-400 shrink-0"></i>
                        <span>Multi-user Access</span>
                    </span>
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
                From tracking seed batches to monitoring expiry dates and managing suppliers, AgriStock covers the full lifecycle of your agricultural inputs.
            </p>
        </div>

        <div class="max-w-6xl mx-auto grid sm:grid-cols-2 md:grid-cols-3 gap-5">
            @php
                $features = [
                    ['icon' => 'package', 'title' => 'Product Catalog', 'desc' => 'Manage your complete agri-input catalog seeds, fertilizers, pesticides, equipment, and feed with categories and units.'],
                    ['icon' => 'bar-chart-3', 'title' => 'Inventory Tracking', 'desc' => 'Real-time stock monitoring with Stock In, Stock Out, and Adjustment workflows. Every movement is logged with reference and user.'],
                    ['icon' => 'triangle-alert', 'title' => 'Low Stock Alerts', 'desc' => 'Automatic alerts when items drop below reorder points. Never face planting season without critical inputs again.'],
                    ['icon' => 'clock', 'title' => 'Expiration Monitor', 'desc' => 'Track expiry dates on seeds, pesticides, and biologicals get ahead of spoilage before it costs you.'],
                    ['icon' => 'shield', 'title' => 'Supplier Management', 'desc' => 'Keep a directory of suppliers and their contact details all in one searchable place.'],
                    ['icon' => 'users', 'title' => 'User and Role Control', 'desc' => 'Give staff the right level of access admin, manager, or field staff with clear permission boundaries.'],
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
                    ['num' => '01', 'title' => 'Build Your Catalog', 'desc' => 'Add products with categories, units, and reorder thresholds. No stock quantities here just your product master list.'],
                    ['num' => '02', 'title' => 'Receive and Move Stock', 'desc' => 'Use Stock In when supplies arrive and Stock Out when used in the field. Every transaction logs user, date, and reference.'],
                    ['num' => '03', 'title' => 'Get Alerts', 'desc' => 'The dashboard flags low stock, critical levels, and items nearing expiry so you always act before there\'s a crisis.'],
                    ['num' => '04', 'title' => 'Review and Report', 'desc' => 'Check movement history and generate stock, movement, and expiry reports to spot trends and plan your next restock.'],
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

    <!-- Gallery band -->
    <section>
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
    </section>

    <!-- About -->
    <section id="about" class="py-16 sm:py-24 px-4 sm:px-6 lg:px-10">
        <div class="max-w-6xl mx-auto grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">

            <!-- Copy -->
            <div class="min-w-0">
                <span class="inline-flex items-center gap-2 bg-green-50 text-green-700 text-xs font-semibold px-3.5 py-1.5 rounded-full mb-5 sm:mb-6">
                    <i data-lucide="sprout" class="w-3.5 h-3.5"></i> About AgriStock
                </span>

                <h2 class="font-display text-3xl sm:text-4xl font-bold text-gray-900 leading-tight mb-5">
                    Built for the realities of Philippine agriculture
                </h2>

                <p class="text-gray-500 text-base leading-relaxed mb-4">
                    AgriStock was designed from the ground up for farm managers who need more than a spreadsheet — but less than an enterprise ERP. It handles the entire lifecycle of agri-inputs: from catalog setup and stock receiving, through daily movements and batch expiry monitoring, to supplier records and reports.
                </p>
                <p class="text-gray-500 text-base leading-relaxed mb-8">
                    Whether you're managing a single farm or coordinating across multiple locations, AgriStock gives every team member — from admin to field staff — exactly the access they need to keep operations running without stockouts or waste.
                </p>

                <!-- Checklist -->
                <div class="space-y-4">
                    @php
                        $points = [
                            ['icon' => 'zap', 'text' => 'Real-time alerts when stock drops below reorder thresholds'],
                            ['icon' => 'clock', 'text' => 'Batch expiry tracking prevents costly disposal of inputs'],
                            ['icon' => 'globe', 'text' => 'Multi-location stock visibility from one dashboard'],
                            ['icon' => 'trending-down', 'text' => 'Movement history and reports reduce guesswork when restocking'],
                        ];
                    @endphp

                    @foreach($points as $point)
                        <div class="flex items-center gap-3.5">
                            <div class="w-9 h-9 rounded-lg bg-green-50 flex items-center justify-center shrink-0">
                                <i data-lucide="{{ $point['icon'] }}" class="w-4 h-4 text-green-600"></i>
                            </div>
                            <p class="text-gray-600 text-[15px]">{{ $point['text'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Stat cards -->
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-green-700 rounded-2xl p-6 sm:p-7">
                    <p class="font-display text-3xl sm:text-4xl font-bold text-white leading-none mb-3">2,400+</p>
                    <p class="text-white font-semibold text-sm mb-1">Farms Onboarded</p>
                    <p class="text-green-200 text-xs leading-relaxed">across Luzon, Visayas &amp; Mindanao</p>
                </div>
                <div class="bg-gray-900 rounded-2xl p-6 sm:p-7">
                    <p class="font-display text-3xl sm:text-4xl font-bold text-white leading-none mb-3">98%</p>
                    <p class="text-white font-semibold text-sm mb-1">Stock Accuracy</p>
                    <p class="text-gray-400 text-xs leading-relaxed">average across active farms</p>
                </div>
                <div class="bg-green-50 rounded-2xl p-6 sm:p-7">
                    <p class="font-display text-3xl sm:text-4xl font-bold text-gray-900 leading-none mb-3">35%</p>
                    <p class="text-gray-800 font-semibold text-sm mb-1">Waste Reduction</p>
                    <p class="text-gray-500 text-xs leading-relaxed">avg. reduction in expired inputs</p>
                </div>
                <div class="bg-gray-100 rounded-2xl p-6 sm:p-7">
                    <p class="font-display text-3xl sm:text-4xl font-bold text-gray-900 leading-none mb-3">180K+</p>
                    <p class="text-gray-800 font-semibold text-sm mb-1">Movements Logged</p>
                    <p class="text-gray-500 text-xs leading-relaxed">stock in, out &amp; adjustments</p>
                </div>
            </div>

        </div>
    </section>

    <!-- CTA -->
    <section class="bg-green-700 text-center py-14 sm:py-20 px-4 sm:px-6">
        <h2 class="font-display text-2xl sm:text-3xl lg:text-4xl font-bold text-white mb-4">
            Ready to take control of your farm inventory?
        </h2>
        <p class="text-green-100 text-base sm:text-lg max-w-2xl mx-auto mb-8">
            Join hundreds of farms across the Philippines using AgriStock to eliminate stockouts, reduce waste, and stay on top of every input.
        </p>
        <a href="{{ route('login') }}"
            class="group inline-flex items-center gap-2 bg-white hover:bg-green-50 text-green-800 px-7 py-3.5 rounded-lg font-semibold transition-all duration-300 ease-out hover:-translate-y-1 hover:shadow-lg">
            Open AgriStock Dashboard
            <i data-lucide="arrow-right" class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1"></i>
        </a>
    </section>

    <!-- Footer -->
    <footer class="bg-green-950 text-green-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-10 py-12 sm:py-16 grid sm:grid-cols-2 md:grid-cols-4 gap-8 sm:gap-10">

            <div class="sm:col-span-2">
                <a href="#top" class="group flex items-center gap-2.5 mb-4 w-fit">
                    <div class="w-9 h-9 bg-green-600 rounded-lg flex items-center justify-center transition-all duration-300 ease-out group-hover:scale-110 group-hover:rotate-3 group-hover:shadow-md group-hover:shadow-green-700/30">
                        <i data-lucide="leaf" class="w-5 h-5 text-white"></i>
                    </div>
                    <span class="font-display font-bold text-lg text-white transition-colors duration-300 group-hover:text-green-200">
                        AgriStock
                    </span>
                </a>
                <p class="text-green-300 text-sm leading-relaxed max-w-sm">
                    Full visibility over every farm input from seeds to fertilizers to equipment built for Philippine agriculture.
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
                    <li><a href="#about" class="hover:text-white transition-colors">About Us</a></li>
                    <li><a href="#top" class="hover:text-white transition-colors">Back to top</a></li>
                </ul>
            </div>

        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-10 py-6 border-t border-green-900 flex flex-col sm:flex-row items-center justify-between text-xs text-green-400 gap-4">
            <p>&copy; {{ date('Y') }} AgriStock. All rights reserved.</p>
            <p>Agriculture Inventory System</p>
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
