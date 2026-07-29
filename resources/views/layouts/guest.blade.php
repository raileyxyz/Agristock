<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'AgriStock') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|playfair-display:700,800&display=swap" rel="stylesheet">

    <script src="https://unpkg.com/lucide@latest"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .font-display { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="font-sans antialiased">

    <div class="min-h-screen grid lg:grid-cols-2">

        <!-- Left panel -->
        <div class="relative hidden lg:flex flex-col justify-between p-10 overflow-hidden bg-gradient-to-br from-green-800 via-green-900 to-green-950">
            <div class="absolute inset-0 opacity-25 bg-[radial-gradient(circle_at_70%_20%,white,transparent_40%)]"></div>

            <div class="relative flex items-center gap-3">
                <div class="w-11 h-11 bg-green-600 rounded-xl flex items-center justify-center shadow-lg">
                    <i data-lucide="leaf" class="w-6 h-6 text-white"></i>
                </div>
                <div>
                    <p class="font-display font-bold text-xl text-white leading-tight">{{ config('app.name', 'AgriStock') }}</p>
                    <p class="text-green-300 text-xs">Farm Inventory System</p>
                </div>
            </div>

            <div class="relative">
                <p class="font-display text-2xl lg:text-3xl font-bold text-white leading-snug mb-4">
                    "Smarter stock management means more time in the field."
                </p>
                <p class="text-green-200/90">Track every seed, fertilizer, and input across your farm — in real time.</p>
            </div>

            <div class="relative flex flex-wrap gap-2">
                @foreach(['Low Stock Alerts', 'Expiry Monitoring', 'Purchase Orders', 'Inventory History', 'Multi-user Access'] as $pill)
                    <span class="text-xs text-green-100 bg-white/10 border border-white/10 px-3 py-1.5 rounded-full">{{ $pill }}</span>
                @endforeach
            </div>
        </div>

        <!-- Right panel -->
        <div class="flex items-center justify-center p-6 sm:p-10 bg-green-50/40">
            <div class="w-full max-w-md">

                <!-- Mobile logo -->
                <div class="flex lg:hidden items-center gap-2.5 mb-8 justify-center">
                    <div class="w-9 h-9 bg-green-700 rounded-lg flex items-center justify-center">
                        <i data-lucide="leaf" class="w-5 h-5 text-white"></i>
                    </div>
                    <span class="font-display font-bold text-lg text-gray-900">{{ config('app.name', 'AgriStock') }}</span>
                </div>

                {{ $slot }}

                <p class="text-center text-xs text-gray-400 mt-8">
                    © {{ date('Y') }} {{ config('app.name', 'AgriStock') }} · Agriculture Inventory Management System
                </p>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
        document.addEventListener('alpine:initialized', () => lucide.createIcons());
    </script>
</body>
</html>
