<x-guest-layout>
    <h1 class="font-display text-3xl font-bold text-gray-900 mb-1.5">Welcome back</h1>
    <p class="text-gray-500 mb-8">Sign in to your {{ config('app.name', 'AgriStock') }} account</p>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Validation Errors -->
    @if($errors->any())
        <div class="mb-5 bg-red-50 border border-red-200 rounded-xl p-3.5 flex items-start gap-2.5">
            <i data-lucide="circle-alert" class="w-4 h-4 text-red-600 mt-0.5 shrink-0"></i>
            <p class="text-sm text-red-700">{{ $errors->first() }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" x-data="{ showPassword: false }">
        @csrf

        <!-- Email -->
        <div class="mb-5">
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email Address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="agristock@gmail.com" required autofocus autocomplete="username"
                class="block w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 transition-colors">
        </div>

        <!-- Password -->
        <div class="mb-4">
            <div class="flex items-center justify-between mb-1.5">
                <x-input-label for="password" value="Password" class="text-sm font-medium text-gray-700" />
                @if(Route::has('password.request'))
                    <a class="text-sm text-green-700 hover:text-green-800 font-medium" href="{{ route('password.request') }}">
                        Forgot password?
                    </a>
                @endif
            </div>
            <div class="relative">
                <input :type="showPassword ? 'text' : 'password'" id="password" name="password" required autocomplete="current-password"
                       placeholder="Enter your password"
                       class="block w-full border-gray-300 rounded-lg px-4 py-3 pr-11 text-sm focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 transition-colors">
                <button type="button" @click="showPassword = !showPassword"
                        class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    <i data-lucide="eye" class="w-4 h-4" x-show="!showPassword"></i>
                    <i data-lucide="eye-off" class="w-4 h-4" x-show="showPassword" x-cloak></i>
                </button>
            </div>
        </div>

        <!-- Remember Me -->
        <label class="flex items-center gap-2.5 mb-6 cursor-pointer">
            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500/40">
            <span class="text-sm text-gray-600">Remember me for 30 days</span>
        </label>

        <button type="submit"
                class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-semibold transition-colors">
            Sign In
        </button>
    </form>

</x-guest-layout>
