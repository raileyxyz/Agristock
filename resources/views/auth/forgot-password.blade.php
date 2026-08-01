<x-guest-layout>
    <h1 class="font-display text-3xl font-bold text-gray-900 mb-1.5">Forgot password?</h1>
    <p class="text-gray-500 mb-8">No problem — enter your email and we'll send you a reset link.</p>

    <!-- Session Status -->
    @if(session('status'))
        <div class="mb-5 bg-green-50 border border-green-200 rounded-xl p-3.5 flex items-start gap-2.5">
            <i data-lucide="check-circle" class="w-4 h-4 text-green-600 mt-0.5 shrink-0"></i>
            <p class="text-sm text-green-700">{{ session('status') }}</p>
        </div>
    @endif

    <!-- Validation Errors -->
    @if($errors->any())
        <div class="mb-5 bg-red-50 border border-red-200 rounded-xl p-3.5 flex items-start gap-2.5">
            <i data-lucide="circle-alert" class="w-4 h-4 text-red-600 mt-0.5 shrink-0"></i>
            <p class="text-sm text-red-700">{{ $errors->first() }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email -->
        <div class="mb-6">
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email Address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="demo@agristock.ph" required autofocus
                   class="block w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-green-500/40 focus:border-green-500 transition-colors">
        </div>

        <button type="submit"
                class="w-full bg-green-700 hover:bg-green-800 text-white py-3 rounded-lg font-semibold transition-colors">
            Email Password Reset Link
        </button>
    </form>

    <p class="text-center text-sm text-gray-500 mt-6">
        Remember your password?
        <a href="{{ route('login') }}" class="text-green-700 hover:text-green-800 font-medium">Back to sign in</a>
    </p>
</x-guest-layout>
