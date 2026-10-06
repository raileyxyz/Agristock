<x-app-layout>
    <!-- Header Section -->
    <div class="pb-4 sm:pb-5 border-b border-gray-200 dark:border-[#27332C] transition-colors duration-200 ease-in-out">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-gray-100 tracking-tight">Roles & Permissions</h1>
        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5 sm:mt-1">System access control matrix for AgriStock.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mt-4 sm:mt-6">
        @php
            $roleStyles = [
                'Admin' => ['badge' => 'bg-purple-100 text-purple-700 dark:bg-purple-950/50 dark:text-purple-400'],
                'Manager' => ['badge' => 'bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:text-blue-400'],
                'Staff' => ['badge' => 'bg-gray-100 text-gray-600 dark:bg-gray-800/60 dark:text-gray-400'],
            ];
        @endphp

        @foreach($permissions as $role => $perms)
            <!-- Card Container (Inilagay ang flex flex-col h-full) -->
            <div class="bg-white dark:bg-[#111713] border border-gray-200 dark:border-[#27332C] rounded-xl p-4 sm:p-6 shadow-sm flex flex-col h-full space-y-4 transition-colors duration-200 ease-in-out">
                <!-- Role Header Card Top -->
                <div class="flex items-center gap-3 pb-3 sm:pb-4 border-b border-gray-100 dark:border-[#1F2B23] shrink-0">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-green-50 dark:bg-green-950/40 text-green-600 dark:text-green-400 flex items-center justify-center shrink-0">
                        <i data-lucide="shield" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                    </div>
                    <div>
                        <span class="inline-block text-[11px] sm:text-xs font-bold px-2.5 py-0.5 sm:py-1 rounded-full {{ $roleStyles[$role]['badge'] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                            {{ $role }}
                        </span>
                        <p class="text-[11px] sm:text-xs text-gray-400 dark:text-gray-400 mt-0.5 sm:mt-1">
                            {{ $userCounts[$role] ?? 0 }} {{ Str::plural('Active User', $userCounts[$role] ?? 0) }}
                        </p>
                    </div>
                </div>

                <!-- Permissions List -->
                <ul class="space-y-2 sm:space-y-2.5 flex-1">
                    @foreach($perms as $perm)
                        <li class="flex items-start gap-2 text-xs sm:text-sm text-gray-600 dark:text-gray-200">
                            <i data-lucide="check-circle" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-green-600 dark:text-green-400 shrink-0 mt-0.5"></i>
                            <span class="leading-tight">{{ $perm }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
</x-app-layout>
