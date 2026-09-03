<x-app-layout>
    <div x-data="{
        activeTab: '{{ session('status') === 'password-updated' || $errors->updatePassword->isNotEmpty() || $errors->userDeletion->isNotEmpty() ? 'security' : 'profile' }}',
        showPhotoModal: false,
        photoPreview: null
    }" class="max-w-8xl mx-auto">

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
            <p class="text-gray-500 text-sm mt-1">Manage your profile, security, and preferences</p>
        </div>

        <div class="flex flex-col lg:flex-row gap-6">

            <!-- Side nav -->
            <div class="lg:w-56 shrink-0">
                <div class="space-y-1">
                    <button @click="activeTab = 'profile'"
                            :class="activeTab === 'profile' ? 'bg-green-600 text-white' : 'text-gray-600 hover:bg-gray-100'"
                            class="w-full flex items-center gap-2.5 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        <i data-lucide="user" class="w-4 h-4"></i>
                        Profile
                    </button>
                    <button @click="activeTab = 'notifications'"
                            :class="activeTab === 'notifications' ? 'bg-green-600 text-white' : 'text-gray-600 hover:bg-gray-100'"
                            class="w-full flex items-center gap-2.5 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        <i data-lucide="bell" class="w-4 h-4"></i>
                        Notifications
                    </button>
                    <button @click="activeTab = 'security'"
                            :class="activeTab === 'security' ? 'bg-green-600 text-white' : 'text-gray-600 hover:bg-gray-100'"
                            class="w-full flex items-center gap-2.5 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        <i data-lucide="shield" class="w-4 h-4"></i>
                        Security
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">

                <!-- Profile tab -->
                <div x-show="activeTab === 'profile'" class="bg-white border border-gray-200 rounded-xl p-6">

                    @if(session('status') === 'profile-updated' || session('status') === 'avatar-updated')
                        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" x-transition
                            class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">
                            Changes saved successfully.
                        </div>
                    @endif

                    <h2 class="text-base font-semibold text-gray-800">Profile Information</h2>

                    <!-- Photo -->
                    <div class="flex items-center gap-4 mt-5 pb-6 border-b border-gray-100">
                        <div class="relative group w-20 h-20 shrink-0">
                            @if($user->avatar)
                                <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}"
                                     class="w-20 h-20 rounded-full object-cover">
                            @else
                                <div class="w-20 h-20 rounded-full bg-green-600 text-white flex items-center justify-center text-2xl font-bold">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                            @endif

                            <button type="button" @click="showPhotoModal = true"
                                    class="absolute inset-0 rounded-full bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                                <i data-lucide="pencil" class="w-5 h-5 text-white"></i>
                            </button>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800">Profile photo</p>
                            <p class="text-xs text-gray-500 mt-0.5">Click the photo to upload a new one. PNG, JPG, or WebP.</p>
                        </div>
                    </div>

                    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
                        @csrf
                        @method('patch')

                        <div class="grid sm:grid-cols-2 gap-5">
                            <div>
                                <x-input-label for="name" :value="__('Full Name')" />
                                <x-text-input id="name" name="name" type="text" class="mt-1.5 block w-full" :value="old('name', $user->name)" required autocomplete="name" />
                                <x-input-error class="mt-1.5" :messages="$errors->get('name')" />
                            </div>

                            <div>
                                <x-input-label for="email" :value="__('Email')" />
                                <x-text-input id="email" name="email" type="email" class="mt-1.5 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
                                <x-input-error class="mt-1.5" :messages="$errors->get('email')" />
                            </div>

                            <div>
                                <x-input-label for="phone" :value="__('Phone Number')" />
                                <x-text-input id="phone" name="phone" type="text" class="mt-1.5 block w-full" :value="old('phone', $user->phone)" placeholder="+63 917 234 5678" autocomplete="tel" />
                                <x-input-error class="mt-1.5" :messages="$errors->get('phone')" />
                            </div>

                            <div>
                                <x-input-label for="address" :value="__('Address')" />
                                <x-text-input id="address" name="address" type="text" class="mt-1.5 block w-full" :value="old('address', $user->address)" />
                                <x-input-error class="mt-1.5" :messages="$errors->get('address')" />
                            </div>
                        </div>

                        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                            <div class="text-sm text-gray-600">
                                {{ __('Your email address is unverified.') }}
                                <button form="send-verification" class="underline hover:text-gray-900">
                                    {{ __('Click here to re-send the verification email.') }}
                                </button>
                                @if (session('status') === 'verification-link-sent')
                                    <p class="mt-2 font-medium text-green-600">{{ __('A new verification link has been sent.') }}</p>
                                @endif
                            </div>
                        @endif

                        @php
                            $roleBadge = match($user->role->value) {
                                'Admin' => 'bg-purple-100 text-purple-700',
                                'Manager' => 'bg-blue-100 text-blue-700',
                                default => 'bg-gray-200 text-gray-700',
                            };
                        @endphp
                        <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3.5 flex items-center gap-3">
                            <span class="text-xs font-medium text-gray-500 shrink-0">Account Role</span>
                            <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $roleBadge }}">{{ $user->role->value }}</span>
                            <span class="text-xs text-gray-400">Assigned by administrator</span>
                        </div>

                        <div class="flex items-center gap-4 pt-1">
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition-colors">
                                Save Changes
                            </button>
                        </div>
                    </form>

                    <form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>
                </div>

                <!-- Notifications tab -->
                <div x-show="activeTab === 'notifications'" class="bg-white border border-gray-200 rounded-xl p-6">
                    <h2 class="text-base font-semibold text-gray-800">Notification Preferences</h2>
                    <p class="text-sm text-gray-400 mt-1 mb-5">Choose what you want to be notified about.</p>

                    <div class="space-y-3" x-data="{ lowStock: true, poUpdates: true, emailDigest: false }">
                        <label class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-lg px-4 py-3.5 cursor-pointer">
                            <div>
                                <p class="text-sm font-medium text-gray-700">Low stock alerts</p>
                                <p class="text-xs text-gray-400">Get notified when items fall below threshold</p>
                            </div>
                            <input type="checkbox" x-model="lowStock" class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500/40">
                        </label>
                        <label class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-lg px-4 py-3.5 cursor-pointer">
                            <div>
                                <p class="text-sm font-medium text-gray-700">Purchase order updates</p>
                                <p class="text-xs text-gray-400">Notify me on order status changes</p>
                            </div>
                            <input type="checkbox" x-model="poUpdates" class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500/40">
                        </label>
                        <label class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-lg px-4 py-3.5 cursor-pointer">
                            <div>
                                <p class="text-sm font-medium text-gray-700">Weekly email digest</p>
                                <p class="text-xs text-gray-400">Summary of activity sent every Monday</p>
                            </div>
                            <input type="checkbox" x-model="emailDigest" class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500/40">
                        </label>
                    </div>

                    <p class="text-xs text-gray-400 mt-4">Note: notification preferences are not yet wired to the backend.</p>
                </div>

                <!-- Security tab -->
                <div x-show="activeTab === 'security'" class="space-y-6">

                    <div class="bg-white border border-gray-200 rounded-xl p-6">
                        <h2 class="text-base font-semibold text-gray-800">Change Password</h2>
                        <p class="text-sm text-gray-400 mt-1 mb-5">Use a long, random password to stay secure.</p>

                        @if(session('status') === 'password-updated')
                            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" x-transition
                                class="mb-5 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">
                                Password updated successfully.
                            </div>
                        @endif

                        <form method="post" action="{{ route('password.update') }}" class="space-y-5">
                            @csrf
                            @method('put')

                            <div>
                                <x-input-label for="update_password_current_password" :value="__('Current Password')" />
                                <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1.5 block w-full" autocomplete="current-password" />
                                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1.5" />
                            </div>

                            <div class="grid sm:grid-cols-2 gap-5">
                                <div>
                                    <x-input-label for="update_password_password" :value="__('New Password')" />
                                    <x-text-input id="update_password_password" name="password" type="password" class="mt-1.5 block w-full" autocomplete="new-password" />
                                    <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1.5" />
                                </div>
                                <div>
                                    <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" />
                                    <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1.5 block w-full" autocomplete="new-password" />
                                    <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1.5" />
                                </div>
                            </div>

                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition-colors">
                                Update Password
                            </button>
                        </form>
                    </div>

                    <div class="bg-white border border-red-300 rounded-xl p-6">
                        <h2 class="text-base font-bold text-red-600">Delete Account</h2>
                        <p class="text-sm text-gray-500 mt-1 mb-5">Once deleted, all of your data will be permanently removed. This cannot be undone.</p>

                        <x-danger-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')" class="inline-flex items-center gap-2">
                            <i data-lucide="trash-2" class="w-4 h-4 stroke-[3]"></i>Delete Account
                        </x-danger-button>

                        <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" max-width="md" focusable>
                            <div x-data="{ password: '' }" class="p-6 sm:p-7">

                                <!-- Header & Icon -->
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                                        <i data-lucide="triangle-alert" class="w-6 h-6 text-red-600 stroke-[2.5]"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-base font-semibold text-gray-900">Delete Account</h2>
                                        <p class="mt-1 text-sm text-gray-500 leading-normal">
                                            This action is permanent and cannot be undone. All associated data will be removed.
                                        </p>
                                    </div>
                                </div>

                                <!-- Form -->
                                <form method="post" action="{{ route('profile.destroy') }}" class="mt-6">
                                    @csrf
                                    @method('delete')

                                    <div class="space-y-1.5">
                                        <label for="password" class="block text-xs font-medium text-gray-700">
                                            Confirm your password
                                        </label>
                                        <input id="password"
                                            name="password"
                                            type="password"
                                            x-model="password"
                                            class="w-full h-10 border border-gray-200 rounded-lg px-3 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all">
                                        <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-1 text-xs" />
                                    </div>

                                    <!-- Actions -->
                                    <div class="mt-6 flex items-center justify-end gap-3">
                                        <button type="button"
                                                x-on:click="$dispatch('close')"
                                                class="h-9 px-4 rounded-lg text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 transition-colors">
                                            Cancel
                                        </button>
                                        <button type="submit"
                                                :disabled="!password"
                                                class="h-9 px-4 rounded-lg text-sm font-medium text-white transition-all disabled:opacity-50 disabled:cursor-not-allowed bg-red-600 hover:bg-red-700 active:bg-red-800 shadow-sm">
                                            Delete Account
                                        </button>
                                    </div>
                                </form>

                            </div>
                        </x-modal>
                    </div>

                </div>

            </div>
        </div>

        <!-- Profile Photo Modal -->
        <div x-show="showPhotoModal"
             x-transition:enter="transition-opacity ease-out duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-150"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
             style="display: none;" x-cloak>
            <div @click.outside="showPhotoModal = false; photoPreview = null"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">

                <div class="flex items-center justify-between px-6 pt-6 pb-2">
                    <h2 class="font-semibold text-gray-800 text-base">Profile photo</h2>
                    <button type="button" @click="showPhotoModal = false; photoPreview = null"
                            class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg p-1.5">
                        <i data-lucide="x" class="w-4.5 h-4.5"></i>
                    </button>
                </div>
                <p class="px-6 text-xs text-gray-400">PNG, JPG, or WebP.</p>

                <form method="post" action="{{ route('profile.avatar') }}" enctype="multipart/form-data" class="p-6">
                    @csrf

                <label class="group block border-2 border-dashed border-gray-300 rounded-xl p-4 text-center cursor-pointer hover:border-green-500 hover:bg-green-50 transition-all duration-300 ease-out transform hover:-translate-y-0.5 hover:shadow-lg"
                    x-show="!photoPreview">
                    <i data-lucide="upload" class="w-6 h-6 text-green-600 mx-auto mb-1 transition-transform duration-300 ease-outgroup-hover:-translate-y-0.5"></i>
                    <span class="text-sm font-medium text-green-700 transition-colors duration-300">
                        Drop image or browse
                    </span>
                    <input type="file" name="avatar" accept="image/png, image/jpeg, image/webp" class="hidden"
                        @change="const file = $event.target.files[0]; if (file) {
                            const reader = new FileReader();
                            reader.onload = e => photoPreview = e.target.result;
                            reader.readAsDataURL(file);
                        }">
                </label>

                    <div x-show="photoPreview" class="flex justify-center">
                        <img :src="photoPreview" class="w-32 h-32 rounded-full object-cover">
                    </div>

                    <div class="flex justify-end gap-2.5 mt-6">
                        <button type="button" @click="showPhotoModal = false; photoPreview = null"
                                class="text-gray-600 hover:bg-gray-100 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            Use photo
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
