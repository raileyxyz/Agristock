<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth" x-data="themeHandler" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="view-transition" content="same-origin">

    <title>{{ config('app.name', 'AgriStock') }}</title>

    <script>
        (function() {
            const savedTheme = localStorage.getItem('color-theme');
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
                darkMode: localStorage.getItem('color-theme') === 'dark' ||
                    (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
                toggleTheme() {
                    this.darkMode = !this.darkMode;
                    localStorage.setItem('color-theme', this.darkMode ? 'dark' : 'light');

                    if (this.darkMode) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }

                    if (window.lucide) {
                        this.$nextTick(() => window.lucide.createIcons());
                    }
                }
            }));
        });
    </script>

    <style>
        body { font-family: 'Figtree', sans-serif; }
        [x-cloak] { display: none !important; }

        *, ::before, ::after {
            transition-property: background-color, border-color, color, fill, stroke, box-shadow;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 300ms;
        }

        .no-transitions * {
            transition: none !important;
        }

        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 30px #f9fafb inset !important;
            -webkit-text-fill-color: #111827 !important;
            transition: background-color 5000s ease-in-out 0s;
        }

        /* Dark autofill: surface #1C2621, text gray-100 (#f3f4f6) to match dashboard palette */
        .dark input:-webkit-autofill,
        .dark input:-webkit-autofill:hover,
        .dark input:-webkit-autofill:focus,
        .dark input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 30px #1C2621 inset !important;
            -webkit-text-fill-color: #f3f4f6 !important;
            caret-color: #f3f4f6 !important;
        }

        @keyframes fadeInPage {
            from {
                opacity: 0;
                transform: translateY(4px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .page-fade-in {
            animation: fadeInPage 0.35s ease-out forwards;
        }

        ::view-transition-old(root),
        ::view-transition-new(root) {
            animation-duration: 0.3s;
        }
    </style>
</head>
<body class="font-sans antialiased text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-[#0B0F0D] min-h-screen page-fade-in">

    <div class="min-h-screen flex flex-col lg:grid lg:grid-cols-12">

        <!-- Left Panel: Permanent Dark Green Banner -->
        <div class="relative hidden lg:flex lg:col-span-5 xl:col-span-6 flex-col justify-between p-8 xl:p-12 overflow-hidden bg-cover bg-center"
            style="background-image: url('{{ asset('images/hero-farm.jpg') }}');">

            <div class="absolute inset-0 bg-gradient-to-br from-green-950/95 via-green-900/90 to-green-950/85"></div>

            <!-- Desktop Logo Header -->
            <div class="relative z-10 flex items-center justify-between w-full">
                <a href="{{ route('landing') }}" class="group flex items-center gap-3 w-fit">
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
            </div>

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
        <div class="relative flex-1 flex flex-col justify-between p-6 sm:p-10 lg:p-12 lg:col-span-7 xl:col-span-6 bg-white dark:bg-[#161D19] min-h-screen lg:min-h-0 border-l border-transparent dark:border-[#27332C]">

            <!-- Top Right Corner Dark Mode Button (Inayos para gamitin ang Alpine toggleTheme method) -->
            <div class="absolute top-6 right-6 sm:top-8 sm:right-8 z-20">
                <button @click="toggleTheme()"
                    type="button"
                    class="p-2.5 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-[#1C2621] rounded-xl transition-all duration-300 focus:outline-none">
                    <i data-lucide="sun" class="w-5 h-5 hidden dark:block"></i>
                    <i data-lucide="moon" class="w-5 h-5 block dark:hidden"></i>
                </button>
            </div>

            <!-- Mobile Header Logo -->
            <div class="lg:hidden w-full max-w-md mx-auto pt-2 pb-6 shrink-0 flex items-center justify-between">
                <a href="{{ route('landing') }}" class="group flex items-center gap-3 w-fit">
                    <div class="w-10 h-10 bg-green-600 rounded-xl flex items-center justify-center shadow-md shrink-0 transition-all duration-300 ease-out group-hover:scale-110 group-hover:rotate-3">
                        <i data-lucide="leaf" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <span class="font-bold text-lg text-gray-900 dark:text-gray-100 block leading-tight transition-colors duration-300 group-hover:text-green-700 dark:group-hover:text-green-400">
                            {{ config('app.name', 'AgriStock') }}
                        </span>
                        <span class="text-[11px] text-green-700 dark:text-green-400 font-semibold tracking-wider uppercase block">
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
            <p class="text-center text-xs text-gray-400 dark:text-gray-500 mt-8 shrink-0">
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
