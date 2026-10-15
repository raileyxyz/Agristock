<x-app-layout>
    <div x-data="{
            showApproveModal: false,
            showRejectModal: false,
            target: { id: null, name: '' },
            openApprove(id, name) { this.target = { id, name }; this.showApproveModal = true; },
            openReject(id, name) { this.target = { id, name }; this.showRejectModal = true; }
        }">

        <!-- Header -->
        <div class="flex items-center justify-between gap-3 mb-1">
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-gray-100">Deletion Requests</h1>
                <p class="mt-1 text-xs sm:text-sm text-gray-500 dark:text-gray-400">{{ $pending->count() }} pending</p>
            </div>
            <a href="{{ route('users.index') }}"
               class="border border-gray-300 dark:border-[#27332C] text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-[#161F1A] px-3 py-2 rounded-lg text-xs sm:text-sm font-medium transition-colors shrink-0">
                Back to users
            </a>
        </div>

        @if(session('success'))
            <div class="mt-4 bg-green-50 dark:bg-green-950/40 border border-green-200 dark:border-green-900/40 text-green-700 dark:text-green-300 text-xs sm:text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mt-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/40 text-red-700 dark:text-red-300 text-xs sm:text-sm px-4 py-3 rounded-lg">{{ session('error') }}</div>
        @endif
        @error('admin_note')
            <div class="mt-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/40 text-red-700 dark:text-red-300 text-xs sm:text-sm px-4 py-3 rounded-lg">{{ $message }}</div>
        @enderror

        <!-- Pending requests -->
        <div class="mt-6 space-y-3">
            @forelse($pending as $deletionRequest)
                <div class="bg-white dark:bg-[#111713] border border-gray-200 dark:border-[#27332C] rounded-xl p-4 sm:p-5">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-semibold text-gray-900 dark:text-gray-100 break-words">{{ $deletionRequest->user->name }}</span>
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-gray-100 text-gray-600 dark:bg-gray-800/60 dark:text-gray-400">{{ $deletionRequest->user->role->value }}</span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 break-all">{{ $deletionRequest->user->email }}</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Requested {{ $deletionRequest->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button" @click="openReject({{ $deletionRequest->id }}, @js($deletionRequest->user->name))"
                                    class="border border-gray-300 dark:border-[#27332C] text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-[#161F1A] px-3 py-2 rounded-lg text-xs sm:text-sm font-medium transition-colors">
                                Decline
                            </button>
                            <button type="button" @click="openApprove({{ $deletionRequest->id }}, @js($deletionRequest->user->name))"
                                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg text-xs sm:text-sm font-medium transition-colors">
                                Approve deletion
                            </button>
                        </div>
                    </div>

                    <div class="mt-3 rounded-lg bg-gray-50 dark:bg-[#161D19] border border-gray-100 dark:border-[#27332C] px-3.5 py-2.5">
                        <p class="text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">Reason</p>
                        <p class="text-sm text-gray-800 dark:text-gray-100 whitespace-pre-line break-words">{{ $deletionRequest->reason }}</p>
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-[#111713] border border-gray-200 dark:border-[#27332C] rounded-xl px-5 py-12 text-center">
                    <i data-lucide="inbox" class="w-8 h-8 text-gray-300 dark:text-[#27332C] mx-auto mb-2"></i>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">No pending deletion requests.</p>
                </div>
            @endforelse
        </div>

        <!-- Recently reviewed -->
        @if($reviewed->isNotEmpty())
            <h2 class="mt-8 mb-3 text-sm font-semibold text-gray-700 dark:text-gray-200">Recently reviewed</h2>
            <div class="bg-white dark:bg-[#111713] border border-gray-200 dark:border-[#27332C] rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[700px]">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-[#161F1A] border-b border-gray-200 dark:border-[#1F2B23] text-left text-gray-500 dark:text-gray-400">
                                <th class="px-3 py-2.5 font-medium">User</th>
                                <th class="px-3 py-2.5 font-medium">Decision</th>
                                <th class="px-3 py-2.5 font-medium">Decided by</th>
                                <th class="px-3 py-2.5 font-medium">Date</th>
                                <th class="px-3 py-2.5 font-medium">Note</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-[#1F2B23]">
                            @foreach($reviewed as $item)
                                <tr>
                                    <td class="px-3 py-2.5 text-gray-900 dark:text-gray-100">{{ $item->user->name }}</td>
                                    <td class="px-3 py-2.5">
                                        <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $item->status === \App\Enums\DeletionRequestStatus::APPROVED ? 'bg-red-100 text-red-700 dark:bg-red-950/40 dark:text-red-400' : 'bg-gray-100 text-gray-600 dark:bg-[#1C2621] dark:text-gray-400' }}">
                                            {{ $item->status === \App\Enums\DeletionRequestStatus::APPROVED ? 'Deleted' : 'Declined' }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2.5 text-gray-500 dark:text-gray-400">{{ $item->decider?->name ?? '—' }}</td>
                                    <td class="px-3 py-2.5 text-gray-400 dark:text-gray-500 text-xs whitespace-nowrap">{{ $item->decided_at?->format('M d, Y h:i A') }}</td>
                                    <td class="px-3 py-2.5 text-gray-500 dark:text-gray-400 break-words">{{ $item->admin_note ?: '—' }}</td>
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
                    <div class="w-11 h-11 rounded-full bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 flex items-center justify-center mb-4">
                        <i data-lucide="user-x" class="w-5 h-5"></i>
                    </div>
                    <h2 class="font-semibold text-gray-800 dark:text-gray-100 text-base mb-1.5">Approve deletion?</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                        <span class="font-medium text-gray-700 dark:text-gray-100 break-words" x-text="target.name"></span>
                        will be marked as <span class="font-medium">Deleted</span>. They can no longer log in and the account cannot be restored. Their stock records stay in the system.
                    </p>
                </div>
                <form method="POST" :action="`/users/deletion-requests/${target.id}/approve`"
                      class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-2.5 px-6 py-4 bg-gray-50 dark:bg-[#111713] border-t border-gray-100 dark:border-[#27332C]">
                    @csrf
                    <button type="button" @click="showApproveModal = false"
                            class="text-gray-600 dark:text-gray-400 hover:bg-gray-200/70 dark:hover:bg-[#1C2621] px-4 py-2 rounded-lg text-sm font-medium transition-colors order-2 sm:order-1">Cancel</button>
                    <button type="submit"
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm order-1 sm:order-2">Approve deletion</button>
                </form>
            </div>
        </div>

        <!-- Decline Modal -->
        <div x-show="showRejectModal" class="fixed inset-0 bg-black/50 dark:bg-[#0B0F0D]/80 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;" x-cloak>
            <div @click.outside="showRejectModal = false" class="bg-white dark:bg-[#161D19] border border-transparent dark:border-[#27332C] rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
                <form method="POST" :action="`/users/deletion-requests/${target.id}/reject`">
                    @csrf
                    <div class="px-6 pt-6 pb-5">
                        <h2 class="font-semibold text-gray-800 dark:text-gray-100 text-base mb-1.5">Decline request?</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed mb-3">
                            <span class="font-medium text-gray-700 dark:text-gray-100 break-words" x-text="target.name"></span> stays active. You can leave a note that they will see.
                        </p>
                        <textarea name="admin_note" rows="3" maxlength="500" placeholder="Optional note"
                                  class="w-full border border-gray-300 dark:border-[#27332C] rounded-lg px-3 py-2 text-sm dark:bg-[#0B0F0D] dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-green-500/40"></textarea>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-2.5 px-6 py-4 bg-gray-50 dark:bg-[#111713] border-t border-gray-100 dark:border-[#27332C]">
                        <button type="button" @click="showRejectModal = false"
                                class="text-gray-600 dark:text-gray-400 hover:bg-gray-200/70 dark:hover:bg-[#1C2621] px-4 py-2 rounded-lg text-sm font-medium transition-colors order-2 sm:order-1">Cancel</button>
                        <button type="submit"
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm order-1 sm:order-2">Decline request</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
