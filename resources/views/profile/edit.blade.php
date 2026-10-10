<x-app-layout>
    <div x-data="{
        activeTab: '{{
            request('tab') === 'notifications' || in_array(session('status'), ['notifications-updated', 'notifications-unchanged'])
                ? 'notifications'
                : (request('tab') === 'security' || session('status') === 'password-updated' || $errors->updatePassword->isNotEmpty() || $errors->userDeletion->isNotEmpty()
                    ? 'security'
                    : 'profile')
        }}',
        showPhotoModal: false,
        photoPreview: null
    }" class="max-w-8xl mx-auto">

        <!-- Header Section -->
        <div class="pb-4 sm:pb-5 border-b border-gray-200 dark:border-[#27332C] transition-colors duration-200 ease-in-out">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-gray-100 tracking-tight">Settings</h1>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5 sm:mt-1">Manage your profile, security, and preferences</p>
        </div>

        <div class="flex flex-col lg:flex-row gap-4 sm:gap-6 items-start mt-4 sm:mt-6">

            <!-- Side Nav -->
            <div class="w-full lg:w-56 shrink-0">
                <div class="flex flex-row lg:flex-col overflow-x-auto gap-1 pb-2 lg:pb-0">
                    <button @click="activeTab = 'profile'"
                            :class="activeTab === 'profile' ? 'bg-green-600 text-white' : 'text-gray-600 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-[#161F1A]'"
                            class="flex-1 lg:flex-initial flex items-center justify-center lg:justify-start gap-2 sm:gap-2.5 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-lg text-xs sm:text-sm font-medium transition-colors shrink-0">
                        <i data-lucide="user" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                        <span>Profile</span>
                    </button>
                    <button @click="activeTab = 'notifications'"
                            :class="activeTab === 'notifications' ? 'bg-green-600 text-white' : 'text-gray-600 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-[#161F1A]'"
                            class="flex-1 lg:flex-initial flex items-center justify-center lg:justify-start gap-2 sm:gap-2.5 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-lg text-xs sm:text-sm font-medium transition-colors shrink-0">
                        <i data-lucide="bell" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                        <span>Notifications</span>
                    </button>
                    <button @click="activeTab = 'security'"
                            :class="activeTab === 'security' ? 'bg-green-600 text-white' : 'text-gray-600 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-[#161F1A]'"
                            class="flex-1 lg:flex-initial flex items-center justify-center lg:justify-start gap-2 sm:gap-2.5 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-lg text-xs sm:text-sm font-medium transition-colors shrink-0">
                        <i data-lucide="shield" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                        <span>Security</span>
                    </button>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="w-full flex-1 min-w-0">

                <!-- Profile Tab -->
                <div x-show="activeTab === 'profile'" class="bg-white dark:bg-[#111713] border border-gray-200 dark:border-[#27332C] rounded-xl p-4 sm:p-6 shadow-sm space-y-5 sm:space-y-6 transition-colors duration-200 ease-in-out">

                    @if(session('status') === 'profile-updated' || session('status') === 'avatar-updated')
                        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" x-transition
                            class="bg-green-50 dark:bg-green-950/40 border border-green-200 dark:border-green-900/40 text-green-700 dark:text-green-300 text-xs sm:text-sm px-3.5 sm:px-4 py-2.5 sm:py-3 rounded-lg">
                            Changes saved successfully.
                        </div>
                    @endif

                    <h2 class="text-sm sm:text-base font-semibold text-gray-800 dark:text-gray-200">Profile Information</h2>

                    <!-- Photo Upload Section -->
                    <div class="flex items-center gap-3.5 sm:gap-4 pb-5 sm:pb-6 border-b border-gray-100 dark:border-[#1F2B23]">
                        <div class="relative group w-16 h-16 sm:w-20 sm:h-20 shrink-0">
                            <x-avatar :user="$user" size="w-16 h-16 sm:w-20 sm:h-20" text-size="text-xl sm:text-2xl"/>

                            <button type="button" @click="showPhotoModal = true"
                                    class="absolute inset-0 rounded-full bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                                <i data-lucide="pencil" class="w-4 h-4 sm:w-5 sm:h-5 text-white"></i>
                            </button>
                        </div>
                        <div>
                            <p class="text-xs sm:text-sm font-medium text-gray-800 dark:text-gray-100">Profile photo</p>
                            <p class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 mt-0.5">Click the photo to upload a new one. PNG, JPG, or WebP.</p>
                        </div>
                    </div>

                    <form method="post" action="{{ route('profile.update') }}" class="space-y-4 sm:space-y-5">
                        @csrf
                        @method('patch')

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                            <div>
                                <x-input-label for="name" :value="__('Full Name')" class="text-xs sm:text-sm" />
                                <x-text-input id="name" name="name" type="text" class="mt-1 sm:mt-1.5 block w-full text-xs sm:text-sm" :value="old('name', $user->name)" required autocomplete="name" />
                                <x-input-error class="mt-1 sm:mt-1.5" :messages="$errors->get('name')" />
                            </div>

                            <div>
                                <x-input-label for="email" :value="__('Email')" class="text-xs sm:text-sm" />
                                <x-text-input id="email" name="email" type="email" class="mt-1 sm:mt-1.5 block w-full text-xs sm:text-sm" :value="old('email', $user->email)" required autocomplete="username" />
                                <x-input-error class="mt-1 sm:mt-1.5" :messages="$errors->get('email')" />
                            </div>

                            <div>
                                <x-input-label for="phone" :value="__('Phone Number')" class="text-xs sm:text-sm" />
                                <x-text-input id="phone" name="phone" type="text" class="mt-1 sm:mt-1.5 block w-full text-xs sm:text-sm" :value="old('phone', $user->phone)" placeholder="+63 917 234 5678" autocomplete="tel" />
                                <x-input-error class="mt-1 sm:mt-1.5" :messages="$errors->get('phone')" />
                            </div>

                            <div>
                                <x-input-label for="address" :value="__('Address')" class="text-xs sm:text-sm" />
                                <x-text-input id="address" name="address" type="text" class="mt-1 sm:mt-1.5 block w-full text-xs sm:text-sm" :value="old('address', $user->address)" />
                                <x-input-error class="mt-1 sm:mt-1.5" :messages="$errors->get('address')" />
                            </div>
                        </div>

                        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                            <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                                {{ __('Your email address is unverified.') }}
                                <button form="send-verification" class="underline hover:text-gray-900 dark:hover:text-gray-100">
                                    {{ __('Click here to re-send the verification email.') }}
                                </button>
                                @if (session('status') === 'verification-link-sent')
                                    <p class="mt-2 font-medium text-green-600 dark:text-green-400">{{ __('A new verification link has been sent.') }}</p>
                                @endif
                            </div>
                        @endif

                        @php
                            $roleBadge = match($user->role->value) {
                                'Admin' => 'bg-purple-100 text-purple-700 dark:bg-purple-950/50 dark:text-purple-400',
                                'Manager' => 'bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:text-blue-400',
                                default => 'bg-gray-200 text-gray-600 dark:bg-gray-800/60 dark:text-gray-400',
                            };
                        @endphp
                        <div class="bg-gray-50 dark:bg-[#0B0F0D] border border-gray-200 dark:border-[#27332C] rounded-lg p-3 sm:p-3.5 flex flex-wrap items-center gap-2 sm:gap-3">
                            <span class="text-[11px] sm:text-xs font-medium text-gray-500 dark:text-gray-400 shrink-0">Account Role</span>
                            <span class="text-[11px] sm:text-xs font-bold px-2.5 py-0.5 sm:py-1 rounded-full {{ $roleBadge }}">{{ $user->role->value }}</span>
                            <span class="text-[11px] sm:text-xs text-gray-400 dark:text-gray-500 w-full sm:w-auto">Assigned by administrator</span>
                        </div>

                        <div class="pt-1">
                            <button type="submit" class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-4 sm:px-5 py-2 sm:py-2.5 rounded-lg text-xs sm:text-sm font-medium transition-colors">
                                Save Changes
                            </button>
                        </div>
                    </form>

                    <form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>
                </div>

                <!-- Notifications Tab -->
                <div x-show="activeTab === 'notifications'" class="bg-white dark:bg-[#111713] border border-gray-200 dark:border-[#27332C] rounded-xl p-4 sm:p-6 shadow-sm transition-colors duration-200 ease-in-out">

                    @if(session('status') === 'notifications-updated')
                        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" x-transition
                            class="mb-4 sm:mb-5 bg-green-50 dark:bg-green-950/40 border border-green-200 dark:border-green-900/40 text-green-700 dark:text-green-300 text-xs sm:text-sm px-3.5 sm:px-4 py-2.5 sm:py-3 rounded-lg">
                            Notification preferences saved.
                        </div>
                    @endif

                    <h2 class="text-sm sm:text-base font-semibold text-gray-800 dark:text-gray-200">Notification Preferences</h2>
                    <p class="text-xs sm:text-sm text-gray-400 dark:text-gray-400 mt-0.5 sm:mt-1 mb-4 sm:mb-6">Choose which events you want to be alerted about.</p>

                    <form method="post" action="{{ route('notification-preferences.update') }}">
                        @csrf
                        @method('patch')

                        @foreach($notificationGroups as $category => $items)
                            <div class="mb-5 sm:mb-6">
                                <p class="text-[11px] sm:text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2.5 sm:mb-3">{{ $category }}</p>
                                <div class="space-y-2 sm:space-y-2.5">
                                    @foreach($items as $item)
                                        <div class="flex items-center justify-between border border-gray-200 dark:border-[#27332C] rounded-lg p-3 sm:p-3.5 gap-3">
                                            <div class="min-w-0 flex-1">
                                                <p class="text-xs sm:text-sm font-medium text-gray-800 dark:text-gray-100 truncate">{{ $item['label'] }}</p>
                                                <p class="text-[11px] sm:text-xs text-gray-400 dark:text-gray-400 mt-0.5 leading-tight">{{ $item['description'] }}</p>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                                <input type="hidden" name="preferences[{{ $item['type'] }}]" value="0">
                                                <input type="checkbox" name="preferences[{{ $item['type'] }}]" value="1"
                                                    {{ $item['enabled'] ? 'checked' : '' }} class="sr-only peer">
                                                <div class="w-9 h-5 sm:w-11 sm:h-6 bg-gray-200 dark:bg-[#27332C] rounded-full peer peer-checked:bg-green-600 transition-colors"></div>
                                                <div class="absolute left-0.5 sm:left-1 top-0.5 sm:top-1 bg-white w-4 h-4 rounded-full shadow-sm transition-transform peer-checked:translate-x-4 sm:peer-checked:translate-x-5"></div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        <div class="pt-3 sm:pt-4 border-t border-gray-100 dark:border-[#1F2B23]">
                            <button type="submit"
                                    class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-4 sm:px-5 py-2 sm:py-2.5 rounded-lg text-xs sm:text-sm font-medium transition-colors">
                                Save Preferences
                            </button>
                        </div>
                    </form>

                </div>

                <!-- Security Tab -->
                <div x-show="activeTab === 'security'" class="space-y-4 sm:space-y-6">

                    <!-- Password Card -->
                    <div class="bg-white dark:bg-[#111713] border border-gray-200 dark:border-[#27332C] rounded-xl p-4 sm:p-6 shadow-sm transition-colors duration-200 ease-in-out">
                        <h2 class="text-sm sm:text-base font-semibold text-gray-800 dark:text-gray-200">Change Password</h2>
                        <p class="text-xs sm:text-sm text-gray-400 dark:text-gray-400 mt-0.5 sm:mt-1 mb-4 sm:mb-5">Use a long, random password to stay secure.</p>

                        @if(session('status') === 'password-updated')
                            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" x-transition
                                class="mb-4 sm:mb-5 bg-green-50 dark:bg-green-950/40 border border-green-200 dark:border-green-900/40 text-green-700 dark:text-green-300 text-xs sm:text-sm px-3.5 sm:px-4 py-2.5 sm:py-3 rounded-lg">
                                Password updated successfully.
                            </div>
                        @endif

                        <form method="post" action="{{ route('password.update') }}" class="space-y-4 sm:space-y-5">
                            @csrf
                            @method('put')

                            <div>
                                <x-input-label for="update_password_current_password" :value="__('Current Password')" class="text-xs sm:text-sm" />
                                <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 sm:mt-1.5 block w-full text-xs sm:text-sm" autocomplete="current-password" />
                                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1 sm:mt-1.5" />
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                                <div>
                                    <x-input-label for="update_password_password" :value="__('New Password')" class="text-xs sm:text-sm" />
                                    <x-text-input id="update_password_password" name="password" type="password" class="mt-1 sm:mt-1.5 block w-full text-xs sm:text-sm" autocomplete="new-password" />
                                    <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1 sm:mt-1.5" />
                                </div>
                                <div>
                                    <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" class="text-xs sm:text-sm" />
                                    <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 sm:mt-1.5 block w-full text-xs sm:text-sm" autocomplete="new-password" />
                                    <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1 sm:mt-1.5" />
                                </div>
                            </div>

                            <button type="submit" class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-4 sm:px-5 py-2 sm:py-2.5 rounded-lg text-xs sm:text-sm font-medium transition-colors">
                                Update Password
                            </button>
                        </form>
                    </div>

                    <!-- Delete Account Card -->
                    <div class="bg-white dark:bg-[#111713] border border-red-200 dark:border-red-900/40 rounded-xl p-4 sm:p-6 shadow-sm transition-colors duration-200 ease-in-out"
                        x-data="{ showDeleteModal: {{ $errors->userDeletion->isNotEmpty() ? 'true' : 'false' }}, password: '' }"
                        x-init="if (showDeleteModal) $nextTick(() => lucide.createIcons())">
                        <h2 class="text-sm sm:text-base font-bold text-red-600 dark:text-red-400">Delete Account</h2>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5 sm:mt-1 mb-4 sm:mb-5 leading-relaxed">Once deleted, all of your data will be permanently removed. This cannot be undone.</p>

                        <button type="button" @click="showDeleteModal = true; $nextTick(() => lucide.createIcons())"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2 sm:py-2.5 rounded-lg text-xs sm:text-sm font-medium transition-colors">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                            <span>Delete Account</span>
                        </button>

                        <!-- Delete Account Modal -->
                        <div x-show="showDeleteModal"
                            x-transition:enter="transition-opacity ease-out duration-200"
                            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                            x-transition:leave="transition-opacity ease-in duration-150"
                            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                            class="fixed inset-0 bg-black/50 dark:bg-[#0B0F0D]/80 backdrop-blur-sm z-50 flex items-center justify-center p-4"
                            style="display: none;" x-cloak>
                            <div @click.outside="showDeleteModal = false"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                class="bg-white dark:bg-[#161D19] border border-transparent dark:border-[#27332C] rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">

                                <div class="p-5 sm:p-6 text-center">
                                    <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 flex items-center justify-center mx-auto mb-3 sm:mb-4">
                                        <i data-lucide="trash-2" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                                    </div>

                                    <h2 class="text-sm sm:text-base font-semibold text-gray-900 dark:text-gray-100">Delete your account?</h2>
                                    <p class="mt-1 text-xs sm:text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                        This action is permanent. All your data will be removed and cannot be recovered.
                                    </p>

                                    <form method="post" action="{{ route('profile.destroy') }}" class="mt-4 sm:mt-5 text-left">
                                        @csrf
                                        @method('delete')

                                        <label for="password" class="block text-[11px] sm:text-xs font-medium text-gray-700 dark:text-gray-200 mb-1.5">
                                            Confirm your password
                                        </label>
                                        <div class="relative">
                                            <i data-lucide="lock" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-gray-400 dark:text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
                                            <input id="password"
                                                name="password"
                                                type="password"
                                                x-model="password"
                                                placeholder="Enter your password"
                                                class="w-full h-9 sm:h-10 border rounded-lg pl-8 sm:pl-9 pr-3 text-xs sm:text-sm placeholder-gray-400 dark:placeholder-gray-500 dark:bg-[#0B0F0D] dark:text-gray-100 focus:outline-none focus:ring-2 transition-all
                                                {{ $errors->userDeletion->has('password')
                                                    ? 'border-red-400 dark:border-red-500/60 focus:ring-red-500/20 focus:border-red-500'
                                                    : 'border-gray-200 dark:border-[#27332C] focus:ring-red-500/20 focus:border-red-500' }}">
                                        </div>

                                        @error('password', 'userDeletion')
                                            <p class="mt-1.5 text-[11px] sm:text-xs text-red-600 dark:text-red-400 flex items-center gap-1">
                                                <i data-lucide="circle-alert" class="w-3 h-3 shrink-0"></i>
                                                <span>{{ $message }}</span>
                                            </p>
                                        @enderror

                                        <div class="mt-5 sm:mt-6 flex items-center gap-2.5">
                                            <button type="button" @click="showDeleteModal = false; password = ''"
                                                    class="flex-1 h-9 sm:h-10 rounded-lg text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-[#1C2621] dark:hover:text-gray-100 transition-colors">
                                                Cancel
                                            </button>
                                            <button type="submit"
                                                    :disabled="!password"
                                                    :class="password
                                                        ? 'bg-red-600 hover:bg-red-700 text-white'
                                                        : 'bg-red-300 dark:bg-red-950/60 text-white dark:text-red-400/60 cursor-not-allowed'"
                                                    class="flex-1 h-9 sm:h-10 rounded-lg text-xs sm:text-sm font-medium transition-colors shadow-sm">
                                                Delete
                                            </button>
                                        </div>
                                    </form>
                                </div>

                            </div>
                        </div>
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
             class="fixed inset-0 bg-black/50 dark:bg-[#0B0F0D]/80 backdrop-blur-sm z-50 flex items-center justify-center p-4"
             style="display: none;" x-cloak>
            <div @click.outside="showPhotoModal = false; photoPreview = null"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 class="bg-white dark:bg-[#161D19] border border-transparent dark:border-[#27332C] rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">

                <div class="flex items-center justify-between px-5 sm:px-6 pt-5 sm:pt-6 pb-1">
                    <h2 class="font-semibold text-gray-800 dark:text-gray-100 text-sm sm:text-base">Profile photo</h2>
                    <button type="button" @click="showPhotoModal = false; photoPreview = null"
                            class="text-gray-400 dark:text-gray-400 hover:text-gray-600 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-[#1C2621] rounded-lg p-1.5 transition-colors">
                        <i data-lucide="x" class="w-4 h-4 sm:w-4.5 sm:h-4.5"></i>
                    </button>
                </div>
                <p class="px-5 sm:px-6 text-[11px] sm:text-xs text-gray-400 dark:text-gray-400">PNG, JPG, or WebP.</p>

                <form method="post" action="{{ route('profile.avatar') }}" enctype="multipart/form-data" class="p-5 sm:p-6">
                    @csrf

                    <label class="group block border-2 border-dashed border-gray-300 dark:border-[#27332C] rounded-xl p-4 sm:p-5 text-center cursor-pointer hover:border-green-500 dark:hover:border-green-400 hover:bg-green-50 dark:hover:bg-green-950/40 transition-all duration-300 ease-out transform hover:-translate-y-0.5 hover:shadow-md"
                        x-show="!photoPreview">
                        <i data-lucide="upload" class="w-5 h-5 sm:w-6 sm:h-6 text-green-600 dark:text-green-400 mx-auto mb-1 transition-transform duration-300 ease-out group-hover:-translate-y-0.5"></i>
                        <span class="text-xs sm:text-sm font-medium text-green-700 dark:text-green-400 transition-colors duration-300">
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
                        <img :src="photoPreview" class="w-28 h-28 sm:w-32 sm:h-32 rounded-full object-cover">
                    </div>

                    <div class="flex justify-end gap-2.5 mt-5 sm:mt-6">
                        <button type="button" @click="showPhotoModal = false; photoPreview = null"
                                class="text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-[#1C2621] dark:hover:text-gray-100 px-3.5 sm:px-4 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm font-medium transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="bg-green-600 hover:bg-green-700 text-white px-3.5 sm:px-4 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm font-medium transition-colors">
                            Use photo
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
