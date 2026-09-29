<?php

namespace App\Http\Controllers\Chatbot;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chatbot\SendChatMessageRequest;
use App\Models\ChatMessage;
use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function __construct(
        private ChatbotService $chatbotService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $messages = ChatMessage::where('user_id', $request->user()->id)
            ->latest('id')
            ->take(50)
            ->get()
            ->reverse()
            ->map(fn ($message) => [
                'role' => $message->role,
                'content' => $message->content,
            ])
            ->values();

        return response()->json(['messages' => $messages]);
    }

    public function store(SendChatMessageRequest $request): JsonResponse
    {
        $result = $this->chatbotService->ask(
            $request->user(),
            $request->validated('message')
        );

        return response()->json($result, $result['success'] ? 200 : 502);
    }

    public function destroy(Request $request): JsonResponse
    {
        $this->chatbotService->clear($request->user());

        return response()->json(['success' => true]);
    }
}
