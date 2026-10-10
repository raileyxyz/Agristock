<x-guest-layout>

    <!-- Header Section -->
    <div class="mb-8 text-left">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-[#F1F5F2] mb-2 tracking-tight transition-colors duration-300">
            Forgot password?
        </h1>
        <p class="text-sm text-gray-500 dark:text-[#9AA79F] transition-colors duration-300">
            No problem enter your email and we'll send you a reset link.
        </p>
    </div>

    <!-- Session Status -->
    @if(session('status'))
        <div class="mb-5 bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-900/40 rounded-xl p-3.5 flex items-start gap-3 transition-colors duration-300">
            <i data-lucide="check-circle" class="w-4 h-4 text-green-600 dark:text-[#22C55E] mt-0.5 shrink-0"></i>
            <p class="text-xs sm:text-sm text-green-700 dark:text-green-300 font-medium">{{ session('status') }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email -->
        <div class="mb-6">
            <label for="email" class="block text-sm font-medium text-gray-800 dark:text-[#F1F5F2] mb-2 transition-colors duration-300">
                Email
            </label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   placeholder="demo@agristock.ph" required autofocus autocomplete="username"
                   class="block w-full border rounded-xl px-4 py-3.5 text-sm text-gray-900 dark:text-[#F1F5F2] placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 transition-all
                   {{ $errors->has('email') ? 'border-red-300 dark:border-red-500/60 bg-red-50/30 dark:bg-red-950/20 focus:ring-red-500' : 'border-gray-200 dark:border-[#27332C] bg-gray-50 dark:bg-[#1C2621] focus:bg-white dark:focus:bg-[#111714] focus:ring-green-500 dark:focus:ring-[#22C55E]' }}">

            @error('email')
                <p class="text-xs text-red-600 dark:text-red-400 mt-1.5 flex items-center gap-1 font-medium">
                    <i data-lucide="circle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Submit Button -->
        <button type="submit"
                class="w-full bg-green-600 hover:bg-green-700 active:bg-green-800 dark:bg-green-600 dark:hover:bg-green-500 text-white py-3.5 px-4 rounded-xl font-semibold text-base shadow-sm transition-all duration-150 text-center">
            Email Password Reset Link
        </button>
    </form>

    <!-- Back to Sign In Link -->
    <p class="text-center text-sm text-gray-500 dark:text-[#9AA79F] mt-6 transition-colors duration-300">
        Remember your password?
        <a href="{{ route('login') }}" class="text-green-700 dark:text-[#22C55E] hover:text-green-800 dark:hover:text-green-400 font-medium transition-colors">
            Back to sign in
        </a>
    </p>
</x-guest-layout>
