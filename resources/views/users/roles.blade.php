<x-app-layout>
    <!-- Header Section -->
    <div class="pb-4 sm:pb-5 border-b border-gray-200">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-800 tracking-tight">Roles & Permissions</h1>
        <p class="text-xs sm:text-sm text-gray-500 mt-0.5 sm:mt-1">System access control matrix for AgriStock.</p>
    </div>

    <!-- Grid Container -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 items-start mt-4 sm:mt-6">
        @php
            $roleStyles = [
                'Admin' => ['badge' => 'bg-purple-100 text-purple-700'],
                'Manager' => ['badge' => 'bg-blue-100 text-blue-700'],
                'Staff' => ['badge' => 'bg-gray-100 text-gray-700'],
            ];
        @endphp

        @foreach($permissions as $role => $perms)
            <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-6 shadow-sm space-y-4">
                <!-- Role Header Card Top -->
                <div class="flex items-center gap-3 pb-3 sm:pb-4 border-b border-gray-100">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-green-50 text-green-600 flex items-center justify-center shrink-0">
                        <i data-lucide="shield" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                    </div>
                    <div>
                        <span class="inline-block text-[11px] sm:text-xs font-bold px-2.5 py-0.5 sm:py-1 rounded-full {{ $roleStyles[$role]['badge'] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ $role }}
                        </span>
                        <p class="text-[11px] sm:text-xs text-gray-400 mt-0.5 sm:mt-1">
                            {{ $userCounts[$role] ?? 0 }} {{ Str::plural('Active User', $userCounts[$role] ?? 0) }}
                        </p>
                    </div>
                </div>

                <!-- Permissions List -->
                <ul class="space-y-2 sm:space-y-2.5">
                    @foreach($perms as $perm)
                        <li class="flex items-start gap-2 text-xs sm:text-sm text-gray-600">
                            <i data-lucide="check-circle" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-green-600 shrink-0 mt-0.5"></i>
                            <span class="leading-tight">{{ $perm }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
</x-app-layout>
