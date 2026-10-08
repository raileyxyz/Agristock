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
                $response = $this->callOpenRouter($user, $messages);

                if ($response->status() === 429) {
                    return [
                        'success' => false,
                        'message' => 'The AI is currently busy (rate limit reached). Please try again later.',
                    ];
                }

                if ($response->failed()) {
                    Log::error('OpenRouter request failed', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);

                    return [
                        'success' => false,
                        'message' => 'Unable to connect to the AI right now. Please try again later.',
                    ];
                }

                $assistant = $response->json('choices.0.message');

                if (! $assistant) {
                    return [
                        'success' => false,
                        'message' => 'No response was received from the AI. Please try again.',
                    ];
                }

                $toolCalls = $assistant['tool_calls'] ?? [];

                if (empty($toolCalls)) {
                    $reply = trim((string) ($assistant['content'] ?? ''));

                    if ($reply === '') {
                        return [
                            'success' => false,
                            'message' => 'No response was received from the AI. Please try again.',
                        ];
                    }

                    $reply = str_replace('**', '', $reply);
                    $this->saveExchange($user, $userMessage, $reply);

                    return ['success' => true, 'message' => $reply];
                }

                $messages[] = $assistant;

                foreach ($toolCalls as $call) {
                    $arguments = json_decode($call['function']['arguments'] ?? '{}', true) ?? [];
                    $result = $this->tools->execute($user, $call['function']['name'] ?? '', $arguments);

                    $messages[] = [
                        'role' => 'tool',
                        'tool_call_id' => $call['id'] ?? '',
                        'content' => json_encode($result),
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'I could not complete the response. Please try making your question simpler.',
            ];
        } catch (Throwable $e) {
            Log::error('Chatbot error', ['exception' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'There was an error with the chatbot. Please try again later.',
            ];
        }
    }

    public function clear(User $user): void
    {
        ChatMessage::where('user_id', $user->id)->delete();
    }

    private function callOpenRouter(User $user, array $messages)
    {
        return Http::withToken(config('services.openrouter.key'))
            ->acceptJson()
            ->timeout(45)
            ->post(config('services.openrouter.base_url') . '/chat/completions', [
                'model' => config('services.openrouter.model'),
                'messages' => $messages,
                'tools' => $this->tools->definitions($user),
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
        - Some information is not available to every user. If a tool returns an access error, or no available tool can answer the question, say that you do not have access to that information. Never guess.
        - Use the provided tools to get data. Answer ONLY from tool results.
        - If a tool returns no data, say so. Never guess or invent products, quantities, or suppliers.
        - If the question is not about this inventory system, politely say you can only help with inventory questions.
        - The user may write in English, Tagalog, or Taglish. Understand the question, but always reply in English only. Never reply in Tagalog, even if earlier messages in this conversation were in Tagalog. Keep answers short and clear.
        - Never translate or change product names, SKUs, units, supplier names, or numbers from tool results. Copy them exactly.
        - When listing items from a tool result, include every item.
        - Use plain text only. Do not use markdown (no asterisks, no bold, no headers). For lists, put each item on its own line starting with "- ".
        PROMPT;
    }

    private function saveExchange(User $user, string $userMessage, string $reply): void
    {
        ChatMessage::create(['user_id' => $user->id, 'role' => 'user', 'content' => $userMessage]);
        ChatMessage::create(['user_id' => $user->id, 'role' => 'assistant', 'content' => $reply]);
    }
}
