<x-guest-layout>

    <!-- Header Section (Normal font-sans text) -->
    <div class="mb-8 text-left">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Welcome back</h1>
        <p class="text-sm text-gray-500">
            Sign in to your {{ config('app.name', 'AgriStock') }} account
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Validation Errors -->
    @if($errors->any())
        <div class="mb-5 bg-red-50 rounded-xl p-3.5 flex items-start gap-3">
            <i data-lucide="circle-alert" class="w-4 h-4 text-red-600 mt-0.5 shrink-0"></i>
            <p class="text-xs sm:text-sm text-red-700 font-medium">{{ $errors->first() }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" x-data="{ showPassword: false }">
        @csrf

        <!-- Email Address -->
        <div class="mb-5">
            <label for="email" class="block text-sm font-medium text-gray-800 mb-2">
                Email Address
            </label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   placeholder="agristock@gmail.com" required autofocus autocomplete="username"
                   class="block w-full border-0 bg-gray-50 focus:bg-gray-100/80 rounded-xl px-4 py-3.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 transition-all">
        </div>

        <!-- Password -->
        <div class="mb-5">
            <label for="password" class="block text-sm font-medium text-gray-800 mb-2">
                Password
            </label>
            <div class="relative">
                <input :type="showPassword ? 'text' : 'password'" id="password" name="password" required autocomplete="current-password"
                       placeholder="Enter your password"
                       class="block w-full border-0 bg-gray-50 focus:bg-gray-100/80 rounded-xl px-4 py-3.5 pr-11 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 transition-all">

                <button type="button" @click="showPassword = !showPassword"
                        class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 p-1 rounded-md transition-colors"
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
                       class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500 accent-green-600 transition-all">
                <span class="text-gray-600">Remember me</span>
            </label>

            @if(Route::has('password.request'))
                <a class="text-green-700 hover:text-green-800 font-medium transition-colors"
                   href="{{ route('password.request') }}">
                    Forgot password?
                </a>
            @endif
        </div>

        <!-- Submit Button -->
        <button type="submit"
                class="w-full bg-green-600 hover:bg-green-700 active:bg-green-800 text-white py-3.5 px-4 rounded-xl font-semibold text-base shadow-sm transition-all duration-150 text-center">
            Sign In
        </button>
    </form>

</x-guest-layout>
