<?php

namespace App\Services;

use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ChatbotService
{
    private const MAX_TOOL_ROUNDS = 4;
    private const HISTORY_LIMIT = 10;

    public function __construct(
        private ChatbotToolService $tools,
    ) {}

    /**
     * @return array{success: bool, message: string}
     */
    public function ask(User $user, string $userMessage): array
    {
        try {
            $messages = $this->buildMessages($user, $userMessage);

            for ($round = 0; $round < self::MAX_TOOL_ROUNDS; $round++) {
                $response = $this->callOpenRouter($messages);

                if ($response->status() === 429) {
                    return [
                        'success' => false,
                        'message' => 'Busy ang AI ngayon (limit reached). Subukan ulit maya-maya.',
                    ];
                }

                if ($response->failed()) {
                    Log::error('OpenRouter request failed', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);

                    return [
                        'success' => false,
                        'message' => 'Hindi makakonekta sa AI ngayon. Subukan ulit mamaya.',
                    ];
                }

                $assistant = $response->json('choices.0.message');

                if (! $assistant) {
                    return [
                        'success' => false,
                        'message' => 'Walang sagot na natanggap mula sa AI. Subukan ulit.',
                    ];
                }

                $toolCalls = $assistant['tool_calls'] ?? [];

                // Walang hiningi na tool = final answer na ito.
                if (empty($toolCalls)) {
                    $reply = trim((string) ($assistant['content'] ?? ''));

                    if ($reply === '') {
                        return [
                            'success' => false,
                            'message' => 'Walang sagot na natanggap mula sa AI. Subukan ulit.',
                        ];
                    }

                    $reply = str_replace('**', '', $reply);
                    $this->saveExchange($user, $userMessage, $reply);

                    return ['success' => true, 'message' => $reply];
                }

                // Ibalik muna ang assistant message na may tool_calls, tapos ang bawat tool result.
                $messages[] = $assistant;

                foreach ($toolCalls as $call) {
                    $arguments = json_decode($call['function']['arguments'] ?? '{}', true) ?? [];
                    $result = $this->tools->execute($call['function']['name'] ?? '', $arguments);

                    $messages[] = [
                        'role' => 'tool',
                        'tool_call_id' => $call['id'] ?? '',
                        'content' => json_encode($result),
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Hindi ko natapos ang sagot. Subukan mong gawing mas simple ang tanong.',
            ];
        } catch (Throwable $e) {
            Log::error('Chatbot error', ['exception' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'May error sa chatbot. Subukan ulit mamaya.',
            ];
        }
    }

    public function clear(User $user): void
    {
        ChatMessage::where('user_id', $user->id)->delete();
    }

    private function callOpenRouter(array $messages)
    {
        return Http::withToken(config('services.openrouter.key'))
            ->acceptJson()
            ->timeout(45)
            ->post(config('services.openrouter.base_url') . '/chat/completions', [
                'model' => config('services.openrouter.model'),
                'messages' => $messages,
                'tools' => $this->tools->definitions(),
            ]);
    }

    private function buildMessages(User $user, string $userMessage): array
    {
        $history = ChatMessage::where('user_id', $user->id)
            ->latest('id')
            ->take(self::HISTORY_LIMIT)
            ->get()
            ->reverse()
            ->map(fn ($message) => [
                'role' => $message->role,
                'content' => $message->content,
            ])
            ->values()
            ->all();

        return [
            ['role' => 'system', 'content' => $this->systemPrompt()],
            ...$history,
            ['role' => 'user', 'content' => $userMessage],
        ];
    }

    private function systemPrompt(): string
    {
        return <<<PROMPT
You are the AgriStock assistant, built into an agriculture inventory system.
You answer questions about product stock, low-stock items, and suppliers.

Rules:
- Use the provided tools to get data. Answer ONLY from tool results.
- If a tool returns no data, say so. Never guess or invent products, quantities, or suppliers.
- If the question is not about this inventory system, politely say you can only help with inventory questions.
- Reply in the same language as the user (English, Tagalog, or Taglish). Keep answers short and clear.
- Use plain text only. Do not use markdown (no asterisks, no bold, no headers). For lists, put each item on its own line starting with "- ".
PROMPT;
    }

    private function saveExchange(User $user, string $userMessage, string $reply): void
    {
        ChatMessage::create(['user_id' => $user->id, 'role' => 'user', 'content' => $userMessage]);
        ChatMessage::create(['user_id' => $user->id, 'role' => 'assistant', 'content' => $reply]);
    }
}
