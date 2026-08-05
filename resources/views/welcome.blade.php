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
    <style>
        body { font-family: 'Figtree', sans-serif; }
        h1, h2, h3, .font-display { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="bg-white text-gray-800">

    <!-- Nav -->
    <header class="border-b border-gray-100 sticky top-0 bg-white/90 backdrop-blur-sm z-50">
        <div class="max-w-7xl mx-auto px-6 lg:px-10 h-20 flex items-center justify-between">
            <a href="#top" class="group flex items-center gap-2.5">
                <div class="w-9 h-9 bg-green-700 rounded-lg flex items-center justify-center transition-all duration-300 ease-out group-hover:scale-110 group-hover:rotate-3 group-hover:shadow-md group-hover:shadow-green-700/30">
                    <i data-lucide="leaf" class="w-5 h-5 text-white"></i>
                </div>
                <span class="font-display font-bold text-lg text-gray-900 transition-colors duration-300 group-hover:text-green-700">AgriStock</span>
            </a>

            <nav class="hidden md:flex items-center gap-10 text-sm text-gray-600">
                <a href="#features" class="hover:text-gray-900 transition-colors">Features</a>
                <a href="#how-it-works" class="hover:text-gray-900 transition-colors">How It Works</a>
                <a href="#about" class="hover:text-gray-900 transition-colors">About</a>
            </nav>

            <a href="{{ route('login') }}"
                class="bg-green-700 hover:bg-green-800 text-white px-5 py-2.5 rounded-lg text-sm font-semibold flex items-center gap-2 transition-all duration-300 ease-out transform hover:-translate-y-0.5 hover:shadow-lg">
                Open System
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
    </header>

    <!-- Hero -->
    <section id="top" class="relative overflow-hidden bg-cover bg-center"
            style="background-image: url('{{ asset('images/login-farm.jpg') }}');">

        <!-- Dark green overlay -->
        <div class="absolute inset-0 bg-gradient-to-br from-green-950/90 via-green-900/85 to-green-800/80"></div>

        <div class="relative max-w-7xl mx-auto px-6 lg:px-10 py-20 lg:py-28 grid lg:grid-cols-2 gap-16 items-center">

            <div>
                <span class="inline-flex items-center gap-2 bg-green-800/60 border border-green-600/40 text-green-100 text-xs font-medium px-3.5 py-1.5 rounded-full mb-7">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>
                    Agriculture Inventory System
                </span>

                <h1 class="font-display text-5xl lg:text-6xl font-bold leading-[1.05] text-white mb-2">
                    Farm Smarter.
                </h1>
                <h1 class="font-display text-5xl lg:text-6xl font-bold leading-[1.05] text-green-400 mb-6">
                    Stock Wiser.
                </h1>

                <p class="text-green-100/90 text-lg leading-relaxed mb-8 max-w-lg">
                    AgriStock gives farm managers full visibility over every input — from seeds to fertilizers to equipment — with real-time alerts, expiration tracking, and supplier management in one clean system.
                </p>

                <div class="flex flex-col sm:flex-row gap-3 mb-8">
                    <a href="{{ route('login') }}"
                        class="bg-green-500 hover:bg-green-600 text-white px-6 py-3.5 rounded-lg font-semibold flex items-center justify-center gap-2 transition-all duration-300 ease-out hover:-translate-y-0.5 hover:shadow-lg">
                        Launch Dashboard
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                    <a href="#features"
                        class="bg-white/10 hover:bg-white/20 border border-white/20 text-white px-6 py-3.5 rounded-lg font-semibold text-center transition-all duration-300 ease-out hover:-translate-y-0.5 hover:shadow-lg">
                        Explore Features
                    </a>
                </div>

                <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-green-200">
                    <span class="flex items-center gap-1.5"><i data-lucide="check-circle" class="w-4 h-4 text-green-400"></i> Low Stock Alerts</span>
                    <span class="flex items-center gap-1.5"><i data-lucide="check-circle" class="w-4 h-4 text-green-400"></i> Expiry Monitoring</span>
                    <span class="flex items-center gap-1.5"><i data-lucide="check-circle" class="w-4 h-4 text-green-400"></i> Purchase Orders</span>
                    <span class="flex items-center gap-1.5"><i data-lucide="check-circle" class="w-4 h-4 text-green-400"></i> Inventory History</span>
                    <span class="flex items-center gap-1.5"><i data-lucide="check-circle" class="w-4 h-4 text-green-400"></i> Multi-user Access</span>
                </div>
            </div>

            <!-- Live inventory snapshot card -->
            <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-2xl p-6 shadow-2xl">
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
                        <div class="flex items-center justify-between bg-white/5 rounded-lg px-4 py-3">
                            <span class="flex items-center gap-2.5 text-sm text-white">
                                <span class="w-2 h-2 rounded-full {{ $item['dot'] }}"></span>
                                {{ $item['name'] }}
                            </span>
                            <span class="flex items-center gap-3">
                                <span class="text-sm text-green-200/80">{{ $item['qty'] }}</span>
                                <span class="text-xs font-medium px-2 py-1 rounded-md {{ $item['badge'] }}">{{ $item['status'] }}</span>
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </section>

    <!-- Features -->
    <section id="features" class="py-24 px-6 lg:px-10">
        <div class="max-w-6xl mx-auto text-center mb-16">
            <span class="inline-flex items-center gap-2 bg-green-50 text-green-700 text-xs font-semibold px-3.5 py-1.5 rounded-full mb-6">
                <i data-lucide="leaf" class="w-3.5 h-3.5"></i> Built for Philippine Farms
            </span>
            <h2 class="font-display text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
                Everything your farm inventory needs
            </h2>
            <p class="text-gray-500 text-lg max-w-2xl mx-auto">
                From tracking seed batches to managing purchase orders, AgriStock covers the full lifecycle of your agricultural inputs.
            </p>
        </div>

        <div class="max-w-6xl mx-auto grid md:grid-cols-3 gap-5">
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
    <section id="how-it-works" class="py-24 px-6 lg:px-10 bg-gray-50">
        <div class="max-w-4xl mx-auto text-center mb-16">
            <h2 class="font-display text-4xl lg:text-5xl font-bold text-gray-900 mb-4">How AgriStock Works</h2>
            <p class="text-gray-500 text-lg">A simple 4-step process that keeps your farm operations running smoothly.</p>
        </div>

        <div class="max-w-6xl mx-auto grid md:grid-cols-4 gap-6 relative">
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
        <div class="grid grid-cols-1 md:grid-cols-3 h-72 overflow-hidden">
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

        <div class="bg-green-700 text-center py-20 px-6">
            <h2 class="font-display text-3xl lg:text-4xl font-bold text-white mb-4">
                Ready to take control of your farm inventory?
            </h2>
            <p class="text-green-100 text-lg max-w-2xl mx-auto mb-8">
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
        <div class="max-w-7xl mx-auto px-6 lg:px-10 py-16 grid md:grid-cols-4 gap-10">

            <div class="md:col-span-2">
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
            <div class="max-w-7xl mx-auto px-6 lg:px-10 py-6 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-green-400 text-xs">© {{ date('Y') }} AgriStock · Agriculture Inventory Management System</p>
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
    </script>
</body>
</html>
