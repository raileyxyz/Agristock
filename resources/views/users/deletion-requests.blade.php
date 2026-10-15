<x-app-layout>
    <div x-data="{
            showApproveModal: false,
            showRejectModal: false,
            target: { id: null, name: '' },
            openApprove(id, name) { this.target = { id, name }; this.showApproveModal = true; },
            openReject(id, name) { this.target = { id, name }; this.showRejectModal = true; }
        }">

        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 sm:pb-5 border-b border-gray-200 dark:border-[#27332C]">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-gray-100 tracking-tight">Deletion Requests</h1>
                <p class="mt-0.5 sm:mt-1 text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                    Review and manage user account deletion requests ({{ $pending->count() }} pending).
                </p>
            </div>
            <a href="{{ route('users.index') }}"
               class="inline-flex items-center justify-center gap-2 border border-gray-300 dark:border-[#27332C] text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-[#161F1A] px-3.5 py-2 rounded-lg text-xs sm:text-sm font-medium transition-colors shrink-0">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Back to users
            </a>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mt-4 bg-green-50 dark:bg-green-950/40 border border-green-200 dark:border-green-900/50 text-green-700 dark:text-green-300 text-xs sm:text-sm px-4 py-3 rounded-xl flex items-center gap-2.5">
                <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0 text-green-600 dark:text-green-400"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="mt-4 bg-roed-50 dark:bg-red-950/40 border border-red-200 dark:border-rose-900/50 text-red-700 dark:text-red-300 text-xs sm:text-sm px-4 py-3 rounded-xl flex items-center gap-2.5">
                <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 text-red-600 dark:text-red-400"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @error('admin_note')
            <div class="mt-4 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900/50 text-red-700 dark:text-red-300 text-xs sm:text-sm px-4 py-3 rounded-xl flex items-center gap-2.5">
                <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 text-red-600 dark:red-rose-400"></i>
                <span>{{ $message }}</span>
            </div>
        @enderror

        <!-- Pending requests -->
        <div class="mt-6 space-y-4">
            @forelse ($pending as $deletionRequest)
                <div class="bg-white dark:bg-[#111713] border border-gray-200 dark:border-[#27332C] rounded-xl p-4 sm:p-5 shadow-sm transition-colors">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">

                        <!-- User Info with Avatar -->
                        <div class="flex items-start gap-3.5 min-w-0">
                            <x-avatar :user="$deletionRequest->user" size="w-10 h-10" text-size="text-sm font-semibold" />
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-semibold text-gray-900 dark:text-gray-100 text-sm sm:text-base truncate">{{ $deletionRequest->user->name }}</span>

                                    @php
                                        $roleBadge = match($deletionRequest->user->role->value) {
                                            'Admin' => 'bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-400',
                                            'Manager' => 'bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-400',
                                            default => 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400',
                                        };
                                    @endphp
                                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full {{ $roleBadge }}">
                                        {{ $deletionRequest->user->role->value }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">{{ $deletionRequest->user->email }}</p>
                                <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1 flex items-center gap-1">
                                    <i data-lucide="clock" class="w-3 h-3"></i>
                                    Requested {{ $deletionRequest->created_at->format('M d, Y · h:i A') }}
                                </p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-2 shrink-0 self-start sm:self-center">
                            <button type="button" @click="openReject({{ $deletionRequest->id }}, @js($deletionRequest->user->name))"
                                    class="border border-gray-300 dark:border-[#27332C] text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-[#161F1A] px-3.5 py-2 rounded-lg text-xs sm:text-sm font-medium transition-colors">
                                Decline
                            </button>
                            <button type="button" @click="openApprove({{ $deletionRequest->id }}, @js($deletionRequest->user->name))"
                                    class="bg-red-600 hover:bg-red-700 text-white px-3.5 py-2 rounded-lg text-xs sm:text-sm font-medium transition-colors shadow-sm">
                                Approve deletion
                            </button>
                        </div>
                    </div>

                    <!-- Reason Box -->
                    <div class="mt-4 rounded-lg bg-gray-50 dark:bg-[#161D19] border border-gray-100 dark:border-[#27332C] px-3.5 py-2.5">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Reason for request</p>
                        <p class="text-xs sm:text-sm text-gray-700 dark:text-gray-200 whitespace-pre-line leading-relaxed">{{ $deletionRequest->reason }}</p>
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-[#111713] border border-gray-200 dark:border-[#27332C] rounded-xl px-5 py-12 text-center shadow-sm">
                    <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-[#161D19] flex items-center justify-center mx-auto mb-3 text-gray-400 dark:text-gray-500">
                        <i data-lucide="inbox" class="w-6 h-6"></i>
                    </div>
                    <p class="text-gray-800 dark:text-gray-200 font-medium text-sm">No pending deletion requests</p>
                    <p class="text-gray-400 dark:text-gray-500 text-xs mt-1">All requested account deletions have been processed.</p>
                </div>
            @endforelse
        </div>

        <!-- Recently reviewed -->
        @if($reviewed->isNotEmpty())
            <div class="mt-8 mb-3">
                <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Recently reviewed</h2>
            </div>

            <div class="bg-white dark:bg-[#111713] border border-gray-200 dark:border-[#27332C] rounded-xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[700px]">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-[#161F1A] border-b border-gray-200 dark:border-[#27332C] text-left text-xs uppercase tracking-wider font-semibold text-gray-500 dark:text-gray-400">
                                <th class="px-4 py-3">User</th>
                                <th class="px-4 py-3">Decision</th>
                                <th class="px-4 py-3">Decided by</th>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Admin Note</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-[#27332C]">
                            @foreach ($reviewed as $item)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-[#161F1A]/40 transition-colors">
                                    <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                                        <div class="flex items-center gap-2.5">
                                            <x-avatar :user="$item->user" size="w-7 h-7" text-size="text-[10px] font-semibold" />
                                            <span class="font-medium">{{ $item->user->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($item->status === \App\Enums\DeletionRequestStatus::APPROVED)
                                            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-0.5 rounded-full bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-400">
                                                Deleted
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                                Declined
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300 font-medium">
                                        {{ $item->decider?->name ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-400 dark:text-gray-500 text-xs whitespace-nowrap">
                                        {{ $item->decided_at?->format('M d, Y · h:i A') }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs break-words max-w-xs">
                                        {{ $item->admin_note ?: '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Approve Modal -->
        <div x-show="showApproveModal" class="fixed inset-0 bg-black/50 dark:bg-[#0B0F0D]/80 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;" x-cloak>
            <div @click.outside="showApproveModal = false" class="bg-white dark:bg-[#161D19] border border-transparent dark:border-[#27332C] rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
                <div class="px-6 pt-6 pb-5">
                    <div class="w-11 h-11 rounded-full bg-rose-50 dark:bg-rose-950/40 text-red-600 dark:text-red-400 flex items-center justify-center mb-4 shrink-0">
                        <i data-lucide="user-x" class="w-5 h-5"></i>
                    </div>
                    <h2 class="font-semibold text-gray-800 dark:text-gray-100 text-base mb-1.5">Approve deletion?</h2>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                        <span class="font-semibold text-gray-800 dark:text-gray-100 break-words" x-text="target.name"></span>
                        will be marked as <span class="font-semibold text-red-600 dark:text-red-400">Deleted</span>. They can no longer log in and the account cannot be restored. Their activity records will stay in the system.
                    </p>
                </div>
                <form method="POST" :action="`/users/deletion-requests/${target.id}/approve`"
                      class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-2.5 px-6 py-4 bg-gray-50 dark:bg-[#111713] border-t border-gray-100 dark:border-[#27332C]">
                    @csrf
                    <button type="button" @click="showApproveModal = false"
                            class="text-gray-600 dark:text-gray-400 hover:bg-gray-200/70 dark:hover:bg-[#1C2621] px-4 py-2 rounded-lg text-xs sm:text-sm font-medium transition-colors order-2 sm:order-1">
                        Cancel
                    </button>
                    <button type="submit"
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-xs sm:text-sm font-medium transition-colors shadow-sm order-1 sm:order-2">
                        Approve deletion
                    </button>
                </form>
            </div>
        </div>

        <!-- Decline Modal -->
        <div x-show="showRejectModal" class="fixed inset-0 bg-black/50 dark:bg-[#0B0F0D]/80 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;" x-cloak>
            <div @click.outside="showRejectModal = false" class="bg-white dark:bg-[#161D19] border border-transparent dark:border-[#27332C] rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
                <form method="POST" :action="`/users/deletion-requests/${target.id}/reject`">
                    @csrf
                    <div class="px-6 pt-6 pb-5">
                        <div class="w-11 h-11 rounded-full bg-green-50 dark:bg-green-950/40 text-green-600 dark:text-green-400 flex items-center justify-center mb-4 shrink-0">
                            <i data-lucide="shield-check" class="w-5 h-5"></i>
                        </div>
                        <h2 class="font-semibold text-gray-800 dark:text-gray-100 text-base mb-1.5">Decline request?</h2>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 leading-relaxed mb-3">
                            <span class="font-semibold text-gray-800 dark:text-gray-100 break-words" x-text="target.name"></span> stays active. You can leave an optional note explaining why.
                        </p>
                        <textarea name="admin_note" rows="3" maxlength="500" placeholder="Optional note for user..."
                                class="w-full border border-gray-300 dark:border-[#27332C] rounded-lg px-3 py-2 text-xs sm:text-sm dark:bg-[#0B0F0D] dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-green-500/40 dark:focus:ring-green-400/40 resize-none"></textarea>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-2.5 px-6 py-4 bg-gray-50 dark:bg-[#111713] border-t border-gray-100 dark:border-[#27332C]">
                        <button type="button" @click="showRejectModal = false"
                                class="text-gray-600 dark:text-gray-400 hover:bg-gray-200/70 dark:hover:bg-[#1C2621] px-4 py-2 rounded-lg text-xs sm:text-sm font-medium transition-colors order-2 sm:order-1">
                            Cancel
                        </button>
                        <button type="submit"
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-xs sm:text-sm font-medium transition-colors shadow-sm order-1 sm:order-2">
                            Decline request
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
