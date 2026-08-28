<x-app-layout>
    <div class="max-w-2xl"
        x-data="{
            role: @js(old('role', 'Staff')),
            permissions: @js($permissions),
            init() {
                this.$nextTick(() => lucide.createIcons());
                this.$watch('role', () => this.$nextTick(() => lucide.createIcons()));
            }
        }">

        <div class="flex items-center justify-between mb-1">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Add New User</h1>
                <p class="text-gray-400 text-sm mt-1">Create an account for a farm staff member or manager.</p>
            </div>
        </div>

        @if($errors->any())
            <div class="mt-5 bg-red-50 border border-red-200 rounded-xl p-4 flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-red-700">
                        {{ $errors->count() === 1 ? 'There is 1 problem with this form' : "There are {$errors->count()} problems with this form" }}
                    </p>
                    <ul class="text-sm text-red-600 mt-1 space-y-0.5 list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="bg-white border border-gray-200 rounded-xl p-6 mt-6">
            <form method="POST" action="{{ route('users.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Full Name
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Juan Dela Cruz"
                           class="w-full border rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 transition-colors
                           {{ $errors->has('name') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                    @error('name')
                        <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Email Address
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="user@agristock.ph"
                           class="w-full border rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 transition-colors
                           {{ $errors->has('email') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                    @error('email')
                        <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Role
                    </label>
                    <select name="role" x-model="role"
                            class="w-full border rounded-lg px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 transition-colors
                            {{ $errors->has('role') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                        @foreach($roles as $roleOption)
                            <option value="{{ $roleOption }}" {{ old('role', 'Staff') === $roleOption ? 'selected' : '' }}>
                                {{ $roleOption }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')
                        <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <p class="text-sm font-semibold text-gray-700 mb-2" x-text="role + ' Permissions:'"></p>
                    <ul class="space-y-1.5">
                        <template x-for="perm in permissions[role]" :key="perm">
                            <li class="flex items-center gap-2 text-sm text-gray-600">
                                <i data-lucide="check-circle" class="w-3.5 h-3.5 text-green-600 shrink-0"></i>
                                <span x-text="perm"></span>
                            </li>
                        </template>
                    </ul>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Temporary Password
                    </label>
                    <input type="password" name="password" placeholder="Min 8 characters"
                           class="w-full border rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 transition-colors
                           {{ $errors->has('password') ? 'border-red-300 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-green-500/40 focus:border-green-500' }}">
                    @error('password')
                        <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        Create User
                    </button>
                    <button type="reset"
                            class="border border-gray-300 hover:bg-gray-50 text-gray-700 px-5 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        Clear
                    </button>
                </div>

            </form>
        </div>
    </div>
</x-app-layout>
