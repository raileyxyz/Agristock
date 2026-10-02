<x-app-layout>
    @php
        $roleStyles = [
            'Admin' => ['badge' => 'bg-purple-100 text-purple-700'],
            'Manager' => ['badge' => 'bg-blue-100 text-blue-700'],
            'Staff' => ['badge' => 'bg-gray-100 text-gray-700'],
        ];
    @endphp

    <div
        x-data="{
            role: @js(old('role', 'Staff')),
            permissions: @js($permissions),
            roleStyles: @js($roleStyles),
            showPassword: false,
            get currentBadgeStyle() {
                return (this.roleStyles[this.role] && this.roleStyles[this.role].badge)
                    ? this.roleStyles[this.role].badge
                    : 'bg-gray-100 text-gray-700';
            },
            init() {
                this.$nextTick(() => lucide.createIcons());
                this.$watch('role', () => this.$nextTick(() => lucide.createIcons()));
            }
        }">

        <!-- Header Section -->
        <div class="pb-4 sm:pb-5 border-b border-gray-200">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800 tracking-tight">Add New User</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5 sm:mt-1">Create an account for a farm staff member, manager, or administrator.</p>
        </div>

        <!-- Form Layout -->
        <form method="POST" action="{{ route('users.store') }}" class="mt-4 sm:mt-6">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 items-start">

                <!-- KALIWANG COLUMN: Account Details -->
                <div class="lg:col-span-2 space-y-4 sm:space-y-6">

                    <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-6 space-y-4 sm:space-y-5 shadow-sm">
                        <h2 class="text-sm sm:text-base font-semibold text-gray-800 border-b border-gray-100 pb-2.5 sm:pb-3">User Profile Information</h2>

                        <!-- Full Name -->
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">
                                Full Name
                            </label>
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Juan Dela Cruz"
                                   class="w-full border rounded-lg px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm focus:outline-none focus:ring-2 transition-colors
                                   {{ $errors->has('name') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                            @error('name')
                                <p class="text-xs text-red-600 mt-1 sm:mt-1.5 flex items-center gap-1">
                                    <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Email Address -->
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">
                                Email Address
                            </label>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="user@agristock.ph"
                                   class="w-full border rounded-lg px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm focus:outline-none focus:ring-2 transition-colors
                                   {{ $errors->has('email') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                            @error('email')
                                <p class="text-xs text-red-600 mt-1 sm:mt-1.5 flex items-center gap-1">
                                    <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Role Selection -->
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">
                                System Role
                            </label>
                            <select name="role" x-model="role"
                                    class="w-full border rounded-lg px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm bg-white focus:outline-none focus:ring-2 transition-colors
                                    {{ $errors->has('role') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                                @foreach($roles as $roleOption)
                                    <option value="{{ $roleOption }}" {{ old('role', 'Staff') === $roleOption ? 'selected' : '' }}>
                                        {{ $roleOption }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <p class="text-xs text-red-600 mt-1 sm:mt-1.5 flex items-center gap-1">
                                    <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Temporary Password with Toggle -->
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-1.5">
                                Temporary Password
                            </label>
                            <div class="relative">
                                <input :type="showPassword ? 'text' : 'password'" name="password" placeholder="Minimum 8 characters"
                                       class="w-full border rounded-lg px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm pr-10 focus:outline-none focus:ring-2 transition-colors
                                       {{ $errors->has('password') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                                <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                    <i data-lucide="eye" class="w-4 h-4 shrink-0" x-show="!showPassword"></i>
                                    <i data-lucide="eye-off" class="w-4 h-4 shrink-0" x-show="showPassword"></i>
                                </button>
                            </div>
                            @error('password')
                                <p class="text-xs text-red-600 mt-1 sm:mt-1.5 flex items-center gap-1">
                                    <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                </div>

                <!-- KANANG COLUMN: Role Capabilities Preview & Actions -->
                <div class="space-y-4 sm:space-y-6">

                    <!-- Dynamic Permissions Card -->
                    <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-6 shadow-sm space-y-3 sm:space-y-4">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-2.5 sm:pb-3">
                            <h2 class="text-sm sm:text-base font-semibold text-gray-800">Role Capabilities</h2>

                            <!-- Role Badge -->
                            <span class="inline-block text-[11px] sm:text-xs font-bold px-2.5 py-1 rounded-full transition-all"
                                  :class="currentBadgeStyle"
                                  x-text="role"></span>
                        </div>

                        <div class="bg-gray-50/70 border border-gray-200/80 rounded-lg p-3 sm:p-4">
                            <p class="text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider mb-2.5 sm:mb-3">Allowed Actions</p>
                            <ul class="space-y-2 sm:space-y-2.5">
                                <template x-for="perm in (permissions[role] || [])" :key="perm">
                                    <li class="flex items-start gap-2 sm:gap-2.5 text-xs text-gray-700 leading-tight">
                                        <div class="w-3.5 h-3.5 sm:w-4 sm:h-4 rounded-full bg-green-100 text-green-700 flex items-center justify-center shrink-0 mt-0.5">
                                            <i data-lucide="check" class="w-2.5 h-2.5 sm:w-3 sm:h-3"></i>
                                        </div>
                                        <span x-text="perm"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>

                    <!-- Action Buttons Card -->
                    <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-6 shadow-sm space-y-2.5 sm:space-y-3">
                        <button type="submit"
                                class="w-full bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg text-xs sm:text-sm font-medium transition-colors shadow-sm text-center active:scale-[0.99]">
                            Create User Account
                        </button>
                        <button type="reset"
                                class="w-full bg-white hover:bg-gray-50 text-gray-600 border border-gray-200 px-5 py-2.5 rounded-lg text-xs sm:text-sm font-medium transition-colors text-center">
                            Clear Form
                        </button>
                    </div>

                </div>

            </div>
        </form>
    </div>
</x-app-layout>
