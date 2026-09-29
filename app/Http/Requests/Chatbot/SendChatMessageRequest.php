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
            'message.required' => 'Mag-type muna ng tanong.',
            'message.max' => 'Masyadong mahaba ang mensahe (max 500 characters).',
        ];
    }
}
