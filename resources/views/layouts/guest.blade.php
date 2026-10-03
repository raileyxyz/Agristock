<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'AgriStock') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">

    <script src="https://unpkg.com/lucide@latest"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-900 bg-gray-50 min-h-screen">

    <div class="min-h-screen flex flex-col lg:grid lg:grid-cols-12">

        <!-- Left Panel: Banner (Desktop LG+) -->
        <div class="relative hidden lg:flex lg:col-span-5 xl:col-span-6 flex-col justify-between p-8 xl:p-12 overflow-hidden bg-cover bg-center"
            style="background-image: url('{{ asset('images/hero-farm.jpg') }}');">

            <div class="absolute inset-0 bg-gradient-to-br from-green-950/90 via-green-900/85 to-green-950/80"></div>

            <!-- Desktop Logo Header (With hover & rotate transition) -->
            <a href="{{ route('landing') }}" class="group relative flex items-center gap-3 z-10 w-fit">
                <div class="w-11 h-11 bg-green-600 rounded-xl flex items-center justify-center shadow-lg transition-all duration-300 ease-out group-hover:scale-110 group-hover:rotate-3 group-hover:shadow-green-400/40">
                    <i data-lucide="leaf" class="w-6 h-6 text-white"></i>
                </div>
                <div>
                    <p class="font-bold text-xl text-white leading-tight transition-colors duration-300 group-hover:text-green-300">
                        {{ config('app.name', 'AgriStock') }}
                    </p>
                    <p class="text-green-300 text-xs font-medium transition-colors duration-300 group-hover:text-green-200">
                        Farm Inventory System
                    </p>
                </div>
            </a>

            <!-- Banner Text Section -->
            <div class="relative z-10 mt-auto pt-12">
                <p class="text-2xl xl:text-3xl font-bold text-white leading-snug mb-3">
                    Smarter inventory. More time in the field.
                </p>
                <p class="text-green-200/90 text-sm xl:text-base mb-6 max-w-md">
                    Keep every seed, fertilizer, and farm input organized and visible in real time all from one simple system.
                </p>

                <div class="flex flex-wrap gap-2">
                    @foreach(['Low Stock Alerts', 'Expiry Monitoring', 'Supplier Management', 'Inventory History', 'Multi-user Access'] as $pill)
                        <span class="text-xs font-medium text-green-100 bg-white/10 backdrop-blur-md border border-white/15 px-3 py-1.5 rounded-full">
                            {{ $pill }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right Panel: Auth Container -->
        <div class="flex-1 flex flex-col justify-between p-6 sm:p-10 lg:p-12 lg:col-span-7 xl:col-span-6 bg-white min-h-screen lg:min-h-0">

            <!-- Mobile Header Logo (Top Header) -->
            <div class="lg:hidden w-full max-w-md mx-auto pt-2 pb-6 shrink-0">
                <a href="{{ route('landing') }}" class="group flex items-center gap-3 w-fit">
                    <div class="w-10 h-10 bg-green-600 rounded-xl flex items-center justify-center shadow-md shrink-0 transition-all duration-300 ease-out group-hover:scale-110 group-hover:rotate-3">
                        <i data-lucide="leaf" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <span class="font-bold text-lg text-gray-900 block leading-tight transition-colors duration-300 group-hover:text-green-700">
                            {{ config('app.name', 'AgriStock') }}
                        </span>
                        <span class="text-[11px] text-green-700 font-semibold tracking-wider uppercase block">
                            Inventory System
                        </span>
                    </div>
                </a>
            </div>

            <!-- Form Content Container -->
            <div class="w-full max-w-md mx-auto my-auto py-2">
                {{ $slot }}
            </div>

            <!-- Footer -->
            <p class="text-center text-xs text-gray-400 mt-8 shrink-0">
                © {{ date('Y') }} {{ config('app.name', 'AgriStock') }} · Agriculture Inventory Management System
            </p>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
        document.addEventListener('alpine:initialized', () => lucide.createIcons());
    </script>
</body>
</html>
