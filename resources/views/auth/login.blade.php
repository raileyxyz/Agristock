<x-guest-layout>

    <!-- Header Section -->
    <div class="mb-8 text-left">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2 tracking-tight transition-colors duration-300">
            Welcome back
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 transition-colors duration-300">
            Sign in to your {{ config('app.name', 'AgriStock') }} account
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" x-data="{ showPassword: false }">
        @csrf

        <!-- Email Address -->
        <div class="mb-5">
            <label for="email" class="block text-sm font-medium text-gray-800 dark:text-gray-200 mb-2 transition-colors duration-300">
                Email
            </label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                placeholder="agristock@gmail.com" required autofocus autocomplete="username"
                class="block w-full border rounded-xl px-4 py-3.5 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 transition-all
                {{ $errors->has('email') ? 'border-red-300 dark:border-red-500/60 bg-red-50/30 dark:bg-red-950/20 focus:ring-red-500' : 'border-gray-200 dark:border-[#27332C] bg-gray-50 dark:bg-[#1C2621] focus:bg-white dark:focus:bg-[#111713] focus:ring-green-500 dark:focus:ring-green-400' }}">
            @error('email')
                <p class="text-xs text-red-600 dark:text-red-400 mt-1.5 flex items-center gap-1 font-medium">
                    <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-5">
            <label for="password" class="block text-sm font-medium text-gray-800 dark:text-gray-200 mb-2 transition-colors duration-300">
                Password
            </label>
            <div class="relative">
                <input :type="showPassword ? 'text' : 'password'" id="password" name="password" required autocomplete="current-password"
                    placeholder="Enter your password"
                    class="block w-full border rounded-xl px-4 py-3.5 pr-11 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 transition-all
                    {{ $errors->has('password') ? 'border-red-300 dark:border-red-500/60 bg-red-50/30 dark:bg-red-950/20 focus:ring-red-500' : 'border-gray-200 dark:border-[#27332C] bg-gray-50 dark:bg-[#1C2621] focus:bg-white dark:focus:bg-[#111713] focus:ring-green-500 dark:focus:ring-green-400' }}">

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

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between mb-6 text-sm">
            <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                <input type="checkbox" name="remember"
                    class="w-4 h-4 rounded border-gray-300 dark:border-[#27332C] dark:bg-[#1C2621] text-green-600 dark:text-green-400 focus:ring-green-500 dark:focus:ring-green-400 accent-green-600 dark:accent-green-400 transition-all">
                <span class="text-gray-600 dark:text-gray-400 transition-colors duration-300">Remember me</span>
            </label>

            @if(Route::has('password.request'))
                <a class="text-green-700 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 font-medium transition-colors"
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
