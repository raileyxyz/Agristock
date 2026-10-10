<x-guest-layout>

    <!-- Header Section -->
    <div class="mb-8 text-left">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-[#F1F5F2] mb-2 tracking-tight transition-colors duration-300">
            Reset password
        </h1>
        <p class="text-sm text-gray-500 dark:text-[#9AA79F] transition-colors duration-300">
            Please enter your new password below.
        </p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" x-data="{ showPassword: false, showConfirmPassword: false }">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div class="mb-5">
            <label for="email" class="block text-sm font-medium text-gray-800 dark:text-[#F1F5F2] mb-2 transition-colors duration-300">
                Email
            </label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}"
                   required autofocus autocomplete="username"
                   class="block w-full border rounded-xl px-4 py-3.5 text-sm text-gray-900 dark:text-[#F1F5F2] placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 transition-all
                   {{ $errors->has('email') ? 'border-red-300 dark:border-red-500/60 bg-red-50/30 dark:bg-red-950/20 focus:ring-red-500' : 'border-gray-200 dark:border-[#27332C] bg-gray-50 dark:bg-[#1C2621] focus:bg-white dark:focus:bg-[#111714] focus:ring-green-500 dark:focus:ring-[#22C55E]' }}">

            @error('email')
                <p class="text-xs text-red-600 dark:text-red-400 mt-1.5 flex items-center gap-1 font-medium">
                    <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-5">
            <label for="password" class="block text-sm font-medium text-gray-800 dark:text-[#F1F5F2] mb-2 transition-colors duration-300">
                New Password
            </label>
            <div class="relative">
                <input :type="showPassword ? 'text' : 'password'" id="password" name="password" required autocomplete="new-password"
                       placeholder="Enter new password"
                       class="block w-full border rounded-xl px-4 py-3.5 pr-11 text-sm text-gray-900 dark:text-[#F1F5F2] placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 transition-all
                       {{ $errors->has('password') ? 'border-red-300 dark:border-red-500/60 bg-red-50/30 dark:bg-red-950/20 focus:ring-red-500' : 'border-gray-200 dark:border-[#27332C] bg-gray-50 dark:bg-[#1C2621] focus:bg-white dark:focus:bg-[#111714] focus:ring-green-500 dark:focus:ring-[#22C55E]' }}">

                <button type="button" @click="showPassword = !showPassword"
                        class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-400 hover:text-gray-600 dark:hover:text-gray-100 p-1 rounded-md transition-colors"
                        aria-label="Toggle password visibility">
                    <i data-lucide="eye" class="w-5 h-5" x-show="!showPassword"></i>
                    <i data-lucide="eye-off" class="w-5 h-5" x-show="showPassword" x-cloak></i>
                </button>
            </div>

            @error('password')
                <p class="text-xs text-red-600 dark:text-red-400 mt-1.5 flex items-center gap-1 font-medium">
                    <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="mb-6">
            <label for="password_confirmation" class="block text-sm font-medium text-gray-800 dark:text-[#F1F5F2] mb-2 transition-colors duration-300">
                Confirm New Password
            </label>
            <div class="relative">
                <input :type="showConfirmPassword ? 'text' : 'password'" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                       placeholder="Confirm new password"
                       class="block w-full border rounded-xl px-4 py-3.5 pr-11 text-sm text-gray-900 dark:text-[#F1F5F2] placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 transition-all
                       {{ $errors->has('password_confirmation') ? 'border-red-300 dark:border-red-500/60 bg-red-50/30 dark:bg-red-950/20 focus:ring-red-500' : 'border-gray-200 dark:border-[#27332C] bg-gray-50 dark:bg-[#1C2621] focus:bg-white dark:focus:bg-[#111714] focus:ring-green-500 dark:focus:ring-[#22C55E]' }}">

                <button type="button" @click="showConfirmPassword = !showConfirmPassword"
                        class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-400 hover:text-gray-600 dark:hover:text-gray-100 p-1 rounded-md transition-colors"
                        aria-label="Toggle confirm password visibility">
                    <i data-lucide="eye" class="w-5 h-5" x-show="!showConfirmPassword"></i>
                    <i data-lucide="eye-off" class="w-5 h-5" x-show="showConfirmPassword" x-cloak></i>
                </button>
            </div>

            @error('password_confirmation')
                <p class="text-xs text-red-600 dark:text-red-400 mt-1.5 flex items-center gap-1 font-medium">
                    <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Submit Button -->
        <button type="submit"
                class="w-full bg-green-600 hover:bg-green-700 active:bg-green-800 dark:bg-green-600 dark:hover:bg-green-500 text-white py-3.5 px-4 rounded-xl font-semibold text-base shadow-sm transition-all duration-150 text-center">
            Reset Password
        </button>
    </form>

</x-guest-layout>
