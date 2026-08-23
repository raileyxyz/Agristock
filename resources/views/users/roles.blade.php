<x-app-layout>
    <div class="mb-1">
        <h1 class="text-2xl font-bold text-gray-900">Roles & Permissions</h1>
        <p class="text-gray-400 text-sm mt-1">System access control matrix for AgriStock.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-6">
        @php
            $roleStyles = [
                'Admin' => ['badge' => 'bg-purple-100 text-purple-700'],
                'Manager' => ['badge' => 'bg-blue-100 text-blue-700'],
                'Staff' => ['badge' => 'bg-gray-100 text-gray-700'],
            ];
        @endphp

        @foreach($permissions as $role => $perms)
            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-green-50 text-green-600 flex items-center justify-center shrink-0">
                        <i data-lucide="shield" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="inline-block text-xs font-semibold px-2.5 py-1 rounded-full {{ $roleStyles[$role]['badge'] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ $role }}
                        </span>
                        <p class="text-xs text-gray-400 mt-1">
                            {{ $userCounts[$role] ?? 0 }} {{ Str::plural('active user', $userCounts[$role] ?? 0) }}
                        </p>
                    </div>
                </div>

                <ul class="space-y-2">
                    @foreach($perms as $perm)
                        <li class="flex items-start gap-2 text-sm text-gray-600">
                            <i data-lucide="check-circle" class="w-4 h-4 text-green-600 shrink-0 mt-0.5"></i>
                            <span>{{ $perm }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>

</x-app-layout>
