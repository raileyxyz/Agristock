<x-guest-layout>

    <!-- Header Section -->
    <div class="mb-8 text-left">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-[#F1F5F2] mb-2 tracking-tight transition-colors duration-300">
            Welcome back
        </h1>
        <p class="text-sm text-gray-500 dark:text-[#9AA79F] transition-colors duration-300">
            Sign in to your {{ config('app.name', 'AgriStock') }} account
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Validation Errors -->
    @if($errors->any())
        <div class="mb-5 bg-red-50 dark:bg-red-950/30 border border-transparent dark:border-red-900/40 rounded-xl p-3.5 flex items-start gap-3 transition-colors duration-300">
            <i data-lucide="circle-alert" class="w-4 h-4 text-red-600 dark:text-red-400 mt-0.5 shrink-0"></i>
            <p class="text-xs sm:text-sm text-red-700 dark:text-red-300 font-medium">{{ $errors->first() }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" x-data="{ showPassword: false }">
        @csrf

        <!-- Email Address -->
        <div class="mb-5">
            <label for="email" class="block text-sm font-medium text-gray-800 dark:text-[#F1F5F2] mb-2 transition-colors duration-300">
                Email
            </label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   placeholder="agristock@gmail.com" required autofocus autocomplete="username"
                   class="block w-full border border-gray-200 dark:border-[#27332C] bg-gray-50 dark:bg-[#1C2621] focus:bg-white dark:focus:bg-[#111714] rounded-xl px-4 py-3.5 text-sm text-gray-900 dark:text-[#F1F5F2] placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-[#22C55E] transition-all">
        </div>

        <!-- Password -->
        <div class="mb-5">
            <label for="password" class="block text-sm font-medium text-gray-800 dark:text-[#F1F5F2] mb-2 transition-colors duration-300">
                Password
            </label>
            <div class="relative">
                <input :type="showPassword ? 'text' : 'password'" id="password" name="password" required autocomplete="current-password"
                       placeholder="Enter your password"
                       class="block w-full border border-gray-200 dark:border-[#27332C] bg-gray-50 dark:bg-[#1C2621] focus:bg-white dark:focus:bg-[#111714] rounded-xl px-4 py-3.5 pr-11 text-sm text-gray-900 dark:text-[#F1F5F2] placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-[#22C55E] transition-all">

                <button type="button" @click="showPassword = !showPassword"
                        class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-[#9AA79F] hover:text-gray-600 dark:hover:text-[#F1F5F2] p-1 rounded-md transition-colors"
                        aria-label="Toggle password visibility">
                    <i data-lucide="eye" class="w-5 h-5" x-show="!showPassword"></i>
                    <i data-lucide="eye-off" class="w-5 h-5" x-show="showPassword" x-cloak></i>
                </button>
            </div>
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between mb-6 text-sm">
            <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                <input type="checkbox" name="remember"
                       class="w-4 h-4 rounded border-gray-300 dark:border-[#27332C] dark:bg-[#1C2621] text-green-600 dark:text-[#22C55E] focus:ring-green-500 dark:focus:ring-[#22C55E] accent-green-600 dark:accent-[#22C55E] transition-all">
                <span class="text-gray-600 dark:text-[#9AA79F] transition-colors duration-300">Remember me</span>
            </label>

            @if(Route::has('password.request'))
                <a class="text-green-700 dark:text-[#22C55E] hover:text-green-800 dark:hover:text-green-400 font-medium transition-colors"
                   href="{{ route('password.request') }}">
                    Forgot password?
                </a>
            @endif
        </div>

        <!-- Submit Button -->
        <button type="submit"
                class="w-full bg-green-600 hover:bg-green-700 active:bg-green-800 dark:bg-green-600 dark:hover:bg-green-500 text-white py-3.5 px-4 rounded-xl font-semibold text-base shadow-sm transition-all duration-150 text-center">
            Sign In
        </button>
    </form>

</x-guest-layout>
