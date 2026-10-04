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

        <!-- Sidebar -->
        <x-sidebar />

        <!-- 2. Burahin ang bg-gray-50 dark:bg-[#0B0F0D] at transition sa WRAPPER DIV -->
        <div class="flex-1 flex flex-col overflow-hidden">

            <!-- Topbar -->
            <x-topbar />

            <!-- 3. Burahin ang bg-gray-50 dark:bg-[#0B0F0D] at transition sa MAIN tag -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
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
