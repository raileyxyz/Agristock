<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth" x-data="themeHandler" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'AgriStock') }}</title>
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (savedTheme === 'dark' || (!savedTheme && systemDark)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('themeHandler', () => ({
                darkMode: localStorage.getItem('theme') === 'dark' ||
                    (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
                toggleTheme() {
                    this.darkMode = !this.darkMode;
                    localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');
                }
            }));
        });
    </script>
    <style>
        body { font-family: 'Figtree', sans-serif; }
        [x-cloak] { display: none !important; }
        section[id] { scroll-margin-top: 5rem; }
    </style>
</head>
<body class="bg-white dark:bg-[#0B0F0D] text-gray-800 dark:text-[#F1F5F2] font-sans transition-colors duration-300"
    x-data="{ mobileMenuOpen: false, scrolled: false }"
    x-effect="document.body.style.overflow = mobileMenuOpen ? 'hidden' : ''"
    @scroll.window="scrolled = window.scrollY > 8"
    @keydown.escape.window="mobileMenuOpen = false">

    <!-- Nav -->
    <header class="sticky top-0 z-40 bg-white/80 dark:bg-[#0B0F0D]/80 backdrop-blur-md transition-all duration-300"
            :class="scrolled ? 'shadow-sm border-b border-gray-200/80 dark:border-[#27332C]' : 'border-b border-transparent'">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-10 h-16 sm:h-20 flex items-center justify-between">
            <a href="#top" class="group flex items-center gap-2.5">
                <div class="w-8 h-8 sm:w-9 sm:h-9 bg-green-600 rounded-lg flex items-center justify-center transition-all duration-300 ease-out group-hover:scale-110 group-hover:rotate-3 group-hover:shadow-md group-hover:shadow-green-700/30">
                    <i data-lucide="leaf" class="w-4.5 h-4.5 sm:w-5 sm:h-5 text-white"></i>
                </div>
                <span class="font-bold text-base sm:text-lg text-gray-900 dark:text-[#F1F5F2] transition-colors duration-300 group-hover:text-green-700 dark:group-hover:text-[#22C55E]">AgriStock</span>
            </a>

            <nav class="hidden md:flex items-center gap-10 text-sm font-medium text-gray-600 dark:text-[#9AA79F]">
                <a href="#features" class="relative py-1 hover:text-gray-900 dark:hover:text-[#F1F5F2] transition-colors after:absolute after:left-0 after:-bottom-0.5 after:h-0.5 after:w-0 after:bg-green-600 dark:after:bg-[#22C55E] after:transition-all after:duration-300 hover:after:w-full">Features</a>
                <a href="#how-it-works" class="relative py-1 hover:text-gray-900 dark:hover:text-[#F1F5F2] transition-colors after:absolute after:left-0 after:-bottom-0.5 after:h-0.5 after:w-0 after:bg-green-600 dark:after:bg-[#22C55E] after:transition-all after:duration-300 hover:after:w-full">How It Works</a>
                <a href="#about" class="relative py-1 hover:text-gray-900 dark:hover:text-[#F1F5F2] transition-colors after:absolute after:left-0 after:-bottom-0.5 after:h-0.5 after:w-0 after:bg-green-600 dark:after:bg-[#22C55E] after:transition-all after:duration-300 hover:after:w-full">About</a>
            </nav>

            <div class="flex items-center gap-2">
                <!-- Theme Toggle Button -->
                <button type="button"
                        @click="toggleTheme()"
                        aria-label="Toggle theme"
                        class="p-2 text-gray-600 dark:text-[#9AA79F] hover:text-gray-900 dark:hover:text-[#F1F5F2] hover:bg-gray-100 dark:hover:bg-[#161D19] rounded-lg transition-colors">
                    <i data-lucide="sun" class="w-5 h-5 hidden dark:block"></i>
                    <i data-lucide="moon" class="w-5 h-5 block dark:hidden"></i>
                </button>

                <a href="{{ route('login') }}"
                    class="group hidden md:flex bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold items-center gap-2 transition-all duration-300 ease-out transform hover:-translate-y-0.5 hover:shadow-lg">
                    Open System
                    <i data-lucide="arrow-right" class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1"></i>
                </a>

                <!-- Hamburger toggle -->
                <button type="button"
                        @click="mobileMenuOpen = true"
                        aria-label="Open menu"
                        class="md:hidden text-gray-700 dark:text-[#F1F5F2] hover:text-green-700 dark:hover:text-[#22C55E] hover:bg-gray-100 dark:hover:bg-[#161D19] rounded-lg p-2 transition-colors">
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
            class="fixed inset-0 bg-gray-900/50 dark:bg-[#0B0F0D]/80 backdrop-blur-sm"></div>

        <!-- Slide-in panel -->
        <div x-show="mobileMenuOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            @click.outside="mobileMenuOpen = false"
            class="fixed inset-y-0 right-0 w-full max-w-xs bg-white dark:bg-[#161D19] shadow-2xl flex flex-col h-full border-l border-transparent dark:border-[#27332C]">

            <!-- Panel header -->
            <div class="h-16 sm:h-20 px-5 flex items-center justify-between border-b border-gray-100 dark:border-[#27332C] shrink-0">
                <span class="flex items-center gap-2.5">
                    <div class="w-8 h-8 bg-green-600 rounded-lg flex items-center justify-center">
                        <i data-lucide="leaf" class="w-4 h-4 text-white"></i>
                    </div>
                    <span class="font-bold text-base text-gray-900 dark:text-[#F1F5F2]">AgriStock</span>
                </span>
                <button @click="mobileMenuOpen = false" aria-label="Close menu"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-[#F1F5F2] hover:bg-gray-100 dark:hover:bg-[#1C2621] rounded-lg p-1.5 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Links -->
            <nav class="flex-1 px-4 py-5 space-y-1 overflow-y-auto">
                <a href="#features" @click="mobileMenuOpen = false"
                   class="flex items-center gap-3 px-3 py-3 rounded-lg text-[15px] font-medium text-gray-700 dark:text-[#9AA79F] hover:bg-gray-50 dark:hover:bg-[#1C2621] hover:text-green-700 dark:hover:text-[#22C55E] transition-colors">
                    Features
                </a>
                <a href="#how-it-works" @click="mobileMenuOpen = false"
                   class="flex items-center gap-3 px-3 py-3 rounded-lg text-[15px] font-medium text-gray-700 dark:text-[#9AA79F] hover:bg-gray-50 dark:hover:bg-[#1C2621] hover:text-green-700 dark:hover:text-[#22C55E] transition-colors">
                    How It Works
                </a>
                <a href="#about" @click="mobileMenuOpen = false"
                   class="flex items-center gap-3 px-3 py-3 rounded-lg text-[15px] font-medium text-gray-700 dark:text-[#9AA79F] hover:bg-gray-50 dark:hover:bg-[#1C2621] hover:text-green-700 dark:hover:text-[#22C55E] transition-colors">
                    About
                </a>
            </nav>

            <!-- CTA footer -->
            <div class="p-4 border-t border-gray-100 dark:border-[#27332C] shrink-0">
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

        <!-- Directional Overlay -->
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
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-white mb-2 leading-[1.1] sm:leading-[1.08]">
                    Farm Smarter.
                </h1>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-green-400 mb-6 leading-[1.1] sm:leading-[1.08]">
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
                        <span>Supplier Management</span>
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
            <span class="inline-flex items-center gap-2 bg-green-50 dark:bg-[#14291D] dark:border dark:border-[#27332C] text-green-700 dark:text-[#22C55E] text-xs font-semibold px-3.5 py-1.5 rounded-full mb-5 sm:mb-6">
                <i data-lucide="leaf" class="w-3.5 h-3.5"></i> Built for Philippine Farms
            </span>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 dark:text-[#F1F5F2] mb-4 tracking-tight">
                Everything your farm inventory needs
            </h2>
            <p class="text-gray-500 dark:text-[#9AA79F] text-base sm:text-lg max-w-2xl mx-auto">
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
                <div class="group border border-gray-200 dark:border-[#27332C] bg-white dark:bg-[#111713] rounded-xl p-6 transition-all duration-300 hover:border-green-400 dark:hover:border-[#22C55E] dark:hover:bg-[#1C2621] hover:-translate-y-1.5 hover:shadow-lg dark:hover:shadow-[#0B0F0D]/60">
                    <div class="w-11 h-11 bg-green-50 dark:bg-[#14291D] rounded-lg flex items-center justify-center mb-5 transition-colors duration-300 group-hover:bg-green-100 dark:group-hover:bg-[#14291D]">
                        <i data-lucide="{{ $feature['icon'] }}" class="w-5 h-5 text-green-700 dark:text-[#22C55E] transition-colors duration-300"></i>
                    </div>
                    <h3 class="font-bold text-lg text-gray-900 dark:text-[#F1F5F2] mb-2">{!! $feature['title'] !!}</h3>
                    <p class="text-gray-500 dark:text-[#9AA79F] text-sm leading-relaxed">{{ $feature['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <!-- How It Works -->
    <section id="how-it-works" class="py-16 sm:py-24 px-4 sm:px-6 lg:px-10 bg-gray-50 dark:bg-[#111714] transition-colors duration-300">
        <div class="max-w-4xl mx-auto text-center mb-12 sm:mb-16">
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 dark:text-[#F1F5F2] mb-4 tracking-tight">How AgriStock Works</h2>
            <p class="text-gray-500 dark:text-[#9AA79F] text-base sm:text-lg">A simple 4-step process that keeps your farm operations running smoothly.</p>
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
                <div class="group bg-white dark:bg-[#111713] rounded-xl p-6 border border-gray-200 dark:border-[#27332C] relative transition-all duration-300 hover:border-green-400 dark:hover:border-[#22C55E] dark:hover:bg-[#1C2621] hover:-translate-y-1.5 hover:shadow-lg dark:hover:shadow-[#0B0F0D]/60">
                    <span class="text-4xl font-extrabold text-green-300 dark:text-[#15803D] block mb-3 transition-colors duration-300 group-hover:text-green-500 dark:group-hover:text-[#22C55E]">{{ $step['num'] }}</span>
                    <h3 class="font-bold text-gray-900 dark:text-[#F1F5F2] mb-2">{!! $step['title'] !!}</h3>
                    <p class="text-gray-500 dark:text-[#9AA79F] text-sm leading-relaxed">{{ $step['desc'] }}</p>

                    @if($i < count($steps) - 1)
                        <i data-lucide="arrow-right" class="w-5 h-5 text-green-400 dark:text-[#15803D] absolute -right-3 top-1/2 -translate-y-1/2 hidden md:block bg-gray-50 dark:bg-[#111714] rounded-full p-0.5"></i>
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
                <div class="relative overflow-hidden group cursor-pointer bg-[#0B0F0D]">
                    <img src="{{ asset($img) }}" alt="AgriStock farm"
                        class="w-full h-full object-cover grayscale-[30%] opacity-85 dark:opacity-60 scale-105 transition-all duration-500 group-hover:grayscale-0 group-hover:opacity-100 dark:group-hover:opacity-100 group-hover:scale-110">
                </div>
            @endforeach
        </div>
    </section>

    <!-- About -->
    <section id="about" class="py-16 sm:py-24 px-4 sm:px-6 lg:px-10">
        <div class="max-w-6xl mx-auto grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">

            <!-- Copy -->
            <div class="min-w-0">
                <span class="inline-flex items-center gap-2 bg-green-50 dark:bg-[#14291D] dark:border dark:border-[#27332C] text-green-700 dark:text-[#22C55E] text-xs font-semibold px-3.5 py-1.5 rounded-full mb-5 sm:mb-6">
                    <i data-lucide="sprout" class="w-3.5 h-3.5"></i> About AgriStock
                </span>

                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-[#F1F5F2] leading-tight mb-5 tracking-tight">
                    Built for the realities of Philippine agriculture
                </h2>

                <p class="text-gray-500 dark:text-[#9AA79F] text-base leading-relaxed mb-4">
                    AgriStock was designed from the ground up for farm managers who need more than a spreadsheet but less than an enterprise ERP. It handles the entire lifecycle of agri-inputs from catalog setup and stock receiving, through daily movements and batch expiry monitoring, to supplier records and reports.
                </p>
                <p class="text-gray-500 dark:text-[#9AA79F] text-base leading-relaxed mb-8">
                    Whether it's the admin, a manager, or field staff, AgriStock gives every team member...
                </p>

                <!-- Checklist -->
                <div class="space-y-4">
                    @php
                        $points = [
                            ['icon' => 'zap', 'text' => 'Real-time alerts when stock drops below reorder thresholds'],
                            ['icon' => 'clock', 'text' => 'Batch expiry tracking prevents costly disposal of inputs'],
                            ['icon' => 'globe', 'text' => 'Complete stock visibility from one dashboard'],
                            ['icon' => 'trending-down', 'text' => 'Movement history and reports reduce guesswork when restocking'],
                        ];
                    @endphp

                    @foreach($points as $point)
                        <div class="flex items-center gap-3.5">
                            <div class="w-9 h-9 rounded-lg bg-green-50 dark:bg-[#14291D] flex items-center justify-center shrink-0">
                                <i data-lucide="{{ $point['icon'] }}" class="w-4 h-4 text-green-600 dark:text-[#22C55E]"></i>
                            </div>
                            <p class="text-gray-600 dark:text-[#F1F5F2] text-[15px] font-medium">{{ $point['text'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Stat cards -->
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-green-700 dark:bg-[#15803D] rounded-2xl p-6 sm:p-7">
                    <p class="text-3xl sm:text-4xl font-extrabold text-white leading-none mb-3">3</p>
                    <p class="text-white font-semibold text-sm mb-1">User Roles</p>
                    <p class="text-green-200 dark:text-[#cfe1d6] text-xs leading-relaxed">Admin, Manager &amp; Field Staff access</p>
                </div>
                <div class="bg-gray-900 dark:bg-[#111713] rounded-2xl p-6 sm:p-7 border border-transparent dark:border-[#27332C]">
                    <p class="text-3xl sm:text-4xl font-extrabold text-white dark:text-[#F1F5F2] leading-none mb-3">100%</p>
                    <p class="text-white dark:text-[#F1F5F2] font-semibold text-sm mb-1">Traceable Movements</p>
                    <p class="text-gray-400 dark:text-[#9AA79F] text-xs leading-relaxed">every stock in, out &amp; adjustment is logged</p>
                </div>
                <div class="bg-green-50 dark:bg-[#111713] rounded-2xl p-6 sm:p-7 border border-transparent dark:border-[#27332C]">
                    <p class="text-3xl sm:text-4xl font-extrabold text-gray-900 dark:text-[#F1F5F2] leading-none mb-3">Auto</p>
                    <p class="text-gray-800 dark:text-[#F1F5F2] font-semibold text-sm mb-1">Expiry Alerts</p>
                    <p class="text-gray-500 dark:text-[#9AA79F] text-xs leading-relaxed">items nearing expiry flagged on the dashboard</p>
                </div>
                <div class="bg-gray-100 dark:bg-[#111713] rounded-2xl p-6 sm:p-7 border border-transparent dark:border-[#27332C]">
                    <p class="text-3xl sm:text-4xl font-extrabold text-gray-900 dark:text-[#F1F5F2] leading-none mb-3">Live</p>
                    <p class="text-gray-800 dark:text-[#F1F5F2] font-semibold text-sm mb-1">Stock Levels</p>
                    <p class="text-gray-500 dark:text-[#9AA79F] text-xs leading-relaxed">low and critical stock at a glance</p>
                </div>
            </div>

        </div>
    </section>

    <!-- CTA -->
    <section class="bg-green-700 dark:bg-[#15803D] text-center py-14 sm:py-20 px-4 sm:px-6">
        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-white mb-4 tracking-tight">
            Ready to take control of your farm inventory?
        </h2>
        <p class="text-green-100 dark:text-[#F1F5F2]/90 text-base sm:text-lg max-w-2xl mx-auto mb-8">
            Use AgriStock to eliminate stockouts, reduce waste, and stay on top of every input in your business.
        </p>
        <a href="{{ route('login') }}"
            class="group inline-flex items-center gap-2 bg-white dark:bg-[#0B0F0D] hover:bg-green-50 dark:hover:bg-[#161D19] text-green-800 dark:text-[#F1F5F2] px-7 py-3.5 rounded-lg font-semibold transition-all duration-300 ease-out hover:-translate-y-1 hover:shadow-lg">
            Open AgriStock Dashboard
            <i data-lucide="arrow-right" class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1"></i>
        </a>
    </section>

    <!-- Footer -->
    <footer class="bg-green-950 dark:bg-[#0B0F0D] text-green-100 dark:text-[#9AA79F] border-t border-transparent dark:border-[#27332C]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-10 py-12 sm:py-16 grid sm:grid-cols-2 md:grid-cols-4 gap-8 sm:gap-10">

            <div class="sm:col-span-2">
                <a href="#top" class="group flex items-center gap-2.5 mb-4 w-fit">
                    <div class="w-9 h-9 bg-green-600 rounded-lg flex items-center justify-center transition-all duration-300 ease-out group-hover:scale-110 group-hover:rotate-3 group-hover:shadow-md group-hover:shadow-green-700/30">
                        <i data-lucide="leaf" class="w-5 h-5 text-white"></i>
                    </div>
                    <span class="font-bold text-lg text-white dark:text-[#F1F5F2] transition-colors duration-300 group-hover:text-green-200 dark:group-hover:text-[#22C55E]">
                        AgriStock
                    </span>
                </a>
                <p class="text-green-300 dark:text-[#9AA79F] text-sm leading-relaxed max-w-sm">
                    Full visibility over every farm input from seeds to fertilizers to equipment built for Philippine agriculture.
                </p>
            </div>

            <div>
                <p class="text-white dark:text-[#F1F5F2] font-semibold text-sm mb-4">Product</p>
                <ul class="space-y-2.5 text-sm text-green-300 dark:text-[#9AA79F]">
                    <li><a href="#features" class="hover:text-white dark:hover:text-[#F1F5F2] transition-colors">Features</a></li>
                    <li><a href="#how-it-works" class="hover:text-white dark:hover:text-[#F1F5F2] transition-colors">How It Works</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-white dark:hover:text-[#F1F5F2] transition-colors">Open System</a></li>
                </ul>
            </div>

            <div>
                <p class="text-white dark:text-[#F1F5F2] font-semibold text-sm mb-4">Company</p>
                <ul class="space-y-2.5 text-sm text-green-300 dark:text-[#9AA79F]">
                    <li><a href="#about" class="hover:text-white dark:hover:text-[#F1F5F2] transition-colors">About Us</a></li>
                    <li><a href="#top" class="hover:text-white dark:hover:text-[#F1F5F2] transition-colors">Back to top</a></li>
                </ul>
            </div>

        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-10 py-6 border-t border-green-900 dark:border-[#27332C] flex flex-col sm:flex-row items-center justify-between text-xs text-green-400 dark:text-[#6F7C74] gap-4">
            <p>&copy; {{ date('Y') }} AgriStock. All rights reserved.</p>
            <p>Agriculture Inventory System</p>
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
