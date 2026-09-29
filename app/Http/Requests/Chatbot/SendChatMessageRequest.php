<?php

namespace App\Http\Requests\Chatbot;

use Illuminate\Foundation\Http\FormRequest;

class SendChatMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'Please enter a question first.',
            'message.max' => 'The message is too long (maximum of 500 characters).',
        ];
    }
}
