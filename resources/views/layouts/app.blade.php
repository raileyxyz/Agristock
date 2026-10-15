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

    @vite(['resources/css/app.css','resources/js/app.js'])

    <script>
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>

<body class="font-sans antialiased bg-gray-50 dark:bg-[#0B0F0D] text-gray-900 dark:text-gray-100">

    <div x-data="{
            sidebarOpen: false,
            isDesktop: window.innerWidth >= 1024
        }"
        x-init="
            window.addEventListener('resize', () => {
                isDesktop = window.innerWidth >= 1024;
                if (isDesktop) sidebarOpen = false;
            })
        "
        class="flex h-screen overflow-hidden">

        <!-- Backdrop -->
        <div x-show="sidebarOpen"
            x-transition:enter="transition-opacity ease-linear duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="sidebarOpen = false"
            class="fixed inset-0 bg-black/40 z-30 lg:hidden"
            style="display: none;"></div>

        <x-sidebar />

        <div class="flex-1 flex flex-col overflow-hidden">

            <x-topbar />

            @auth
                @unless(auth()->user()->hasVerifiedEmail())
                    <div class="bg-amber-50 dark:bg-amber-950/30 border-b border-amber-200 dark:border-amber-900/40 text-amber-800 dark:text-amber-300 text-xs sm:text-sm px-4 sm:px-6 lg:px-8 py-2.5 flex flex-wrap items-center gap-x-2 gap-y-1">
                        <i data-lucide="mail" class="w-4 h-4 shrink-0"></i>
                        <span>Your email address is not verified. You can view data, but you cannot make changes until you verify it.</span>
                        <a href="{{ route('profile.edit') }}" class="font-semibold underline underline-offset-2 hover:text-amber-900 dark:hover:text-amber-200">Verify now</a>
                    </div>
                @endunless
            @endauth

            <main class="flex-1 overflow-y-auto p-4 pb-24 sm:p-6 sm:pb-24 lg:p-8 lg:pb-24">
                {{ $slot }}
            </main>
        </div>

    </div>
    <x-chatbot-widget />

    <script>
        lucide.createIcons();
    </script>
</body>

</html>
