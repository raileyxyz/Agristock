<div x-data="chatbot()" x-init="init()" class="fixed bottom-6 right-6 z-50">
    {{-- Floating Action Button (Circle) --}}
    <button type="button" @click="toggle()"
            class="group ml-auto flex h-14 w-14 items-center justify-center rounded-full bg-green-600 text-white shadow-lg shadow-green-600/30 hover:bg-green-700 hover:shadow-xl hover:shadow-green-600/40 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-green-500/30">
        <!-- Chat Icon (visible when closed) -->
        <i data-lucide="message-square-more" x-show="!open" class="w-6 h-6 transition-transform duration-300 group-hover:scale-110"></i>
        <!-- Close Icon (visible when open) -->
        <i data-lucide="x" x-show="open" x-cloak class="w-6 h-6 transition-transform duration-300 group-hover:scale-110"></i>
    </button>

    {{-- Chat Panel --}}
    <div x-show="open"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="absolute bottom-16 right-0 mb-3 flex h-[32rem] w-80 flex-col rounded-2xl border border-gray-100 dark:border-[#27332C] bg-white dark:bg-[#161D19] shadow-2xl sm:w-96 overflow-hidden">

        <!-- Header -->
        <div class="flex items-center justify-between border-b border-green-800/10 dark:border-[#27332C] bg-green-600 px-4 py-3.5 text-white">
            <div class="flex items-center gap-3">
                <div class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-green-500/40 border border-green-400/30 text-white">
                    <i data-lucide="bot" class="w-5 h-5"></i>
                    <!-- Active Status Dot -->
                    <span class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full bg-green-400 ring-2 ring-green-700"></span>
                </div>
                <div>
                    <h3 class="text-sm font-bold leading-tight">AgriStock Assistant</h3>
                    <p class="text-[11px] text-green-100 opacity-90">Always active</p>
                </div>
            </div>

            <button type="button" @click="clearChat()" class="rounded-lg p-1.5 text-green-100 hover:bg-green-700 hover:text-white transition-colors" title="Clear chat history">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Chat Messages Container -->
        <div x-ref="list" class="flex-1 space-y-3.5 overflow-y-auto p-4 text-sm bg-gray-50/50 dark:bg-[#0B0F0D] scrollbar-thin scrollbar-thumb-gray-200 dark:scrollbar-thumb-[#27332C]">

            <!-- Empty State / Welcome Screen -->
            <div x-show="messages.length === 0" class="flex h-full flex-col items-center justify-center text-center p-4">
                <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-950/40 text-green-600 dark:text-green-400">
                    <i data-lucide="sparkles" class="w-6 h-6"></i>
                </div>
                <p class="text-xs font-medium text-gray-700 dark:text-gray-200 mb-1">Hello! How can I help you today?</p>
                <p class="text-[11px] text-gray-400 dark:text-gray-400 mb-4">You can ask questions about stock inventory, low stock alerts, or suppliers.</p>

                <!-- Quick Suggestion Chips with Lucide Icons -->
                <div class="flex flex-wrap justify-center gap-1.5">
                    <button type="button" @click="sendQuick('Who are the active suppliers?')" class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 dark:border-[#27332C] bg-white dark:bg-[#111713] px-3 py-1 text-[11px] font-medium text-gray-600 dark:text-gray-400 hover:border-green-500 dark:hover:border-green-400 hover:text-green-600 dark:hover:text-green-400 transition-colors shadow-2xs">
                        <i data-lucide="truck" class="w-3.5 h-3.5 text-gray-400 dark:text-gray-500"></i>
                        <span>Active suppliers</span>
                    </button>
                    <button type="button" @click="sendQuick('Are there any low stock items?')" class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 dark:border-[#27332C] bg-white dark:bg-[#111713] px-3 py-1 text-[11px] font-medium text-gray-600 dark:text-gray-400 hover:border-green-500 dark:hover:border-green-400 hover:text-green-600 dark:hover:text-green-400 transition-colors shadow-2xs">
                        <i data-lucide="alert-triangle" class="w-3.5 h-3.5 text-amber-500 dark:text-amber-400"></i>
                        <span>Low stock check</span>
                    </button>
                </div>
            </div>

            <!-- Messages List -->
            <template x-for="(m, i) in messages" :key="i">
                <div :class="m.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div class="max-w-[85%] whitespace-pre-wrap rounded-2xl px-3.5 py-2.5 text-xs sm:text-sm shadow-2xs leading-relaxed"
                         :class="m.role === 'user'
                            ? 'bg-green-600 text-white rounded-br-none'
                            : 'bg-white dark:bg-[#111713] text-gray-800 dark:text-gray-100 border border-gray-100 dark:border-[#27332C] rounded-bl-none shadow-sm'"
                         x-text="m.content"></div>
                </div>
            </template>

            <!-- Loading Indicator (Typing Dots) -->
            <div x-show="loading" class="flex justify-start" x-cloak>
                <div class="flex items-center gap-1 rounded-2xl rounded-bl-none border border-gray-100 dark:border-[#27332C] bg-white dark:bg-[#111713] px-4 py-3 shadow-xs">
                    <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-green-500 dark:bg-green-400"></span>
                    <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-green-500 dark:bg-green-400 [animation-delay:0.2s]"></span>
                    <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-green-500 dark:bg-green-400 [animation-delay:0.4s]"></span>
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <form @submit.prevent="send()" class="flex items-center gap-2 border-t border-gray-100 dark:border-[#27332C] bg-white dark:bg-[#161D19] p-3">
            <input type="text" x-model="input" maxlength="500" placeholder="Type a message..."
                   class="flex-1 rounded-xl border border-gray-200 dark:border-[#27332C] bg-gray-50/50 dark:bg-[#0B0F0D] px-3.5 py-2 text-xs sm:text-sm text-gray-800 dark:text-gray-100 focus:border-green-500 dark:focus:border-green-400 focus:bg-white dark:focus:bg-[#0B0F0D] focus:outline-none focus:ring-2 focus:ring-green-500/20 dark:focus:ring-green-400/30 transition-all placeholder:text-gray-400 dark:placeholder:text-gray-500 disabled:opacity-60"
                   :disabled="loading">

            <button type="submit" :disabled="loading || input.trim() === ''"
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-green-600 text-white transition-all hover:bg-green-700 disabled:opacity-40 disabled:hover:bg-green-600 shadow-xs">
                <i data-lucide="send" class="w-4 h-4"></i>
            </button>
        </form>
    </div>
</div>

<script>
    function chatbot() {
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf,
        };

        return {
            open: false,
            loading: false,
            input: '',
            messages: [],
            loaded: false,

            init() {
                this.$nextTick(() => lucide.createIcons());
                this.$watch('open', () => this.$nextTick(() => lucide.createIcons()));
            },

            async toggle() {
                this.open = !this.open;

                if (this.open && !this.loaded) {
                    await this.loadHistory();
                }

                this.scrollToBottom();
            },

            async loadHistory() {
                try {
                    const res = await fetch('{{ route('chatbot.messages.index') }}', { headers });
                    const data = await res.json();
                    this.messages = data.messages ?? [];
                    this.loaded = true;
                } catch (e) {
                    this.messages = [{ role: 'assistant', content: 'Unable to load chat history.' }];
                }
            },

            sendQuick(text) {
                this.input = text;
                this.send();
            },

            async send() {
                const text = this.input.trim();
                if (!text || this.loading) return;

                this.messages.push({ role: 'user', content: text });
                this.input = '';
                this.loading = true;
                this.scrollToBottom();

                try {
                    const res = await fetch('{{ route('chatbot.messages.store') }}', {
                        method: 'POST',
                        headers,
                        body: JSON.stringify({ message: text }),
                    });

                    const data = await res.json();

                    let reply = data.message;
                    if (res.status === 422 && data.errors?.message) {
                        reply = data.errors.message[0];
                    } else if (res.status === 429) {
                        reply = 'Too many requests. Please wait a few seconds.';
                    }

                    this.messages.push({ role: 'assistant', content: reply ?? 'An error occurred. Please try again.' });
                } catch (e) {
                    this.messages.push({ role: 'assistant', content: 'Connection error. Please try again.' });
                } finally {
                    this.loading = false;
                    this.scrollToBottom();
                }
            },

            async clearChat() {
                if (!confirm('Are you sure you want to clear the chat history?')) return;

                await fetch('{{ route('chatbot.messages.destroy') }}', { method: 'DELETE', headers });
                this.messages = [];
            },

            scrollToBottom() {
                this.$nextTick(() => {
                    if (this.$refs.list) {
                        this.$refs.list.scrollTop = this.$refs.list.scrollHeight;
                    }
                    lucide.createIcons();
                });
            },
        };
    }
</script>
