<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Normalize the phone number before validation runs.
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('phone')) {
            $this->merge([
                'phone' => preg_replace('/[\s\-]+/', '', $this->phone),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[\pL\s\-\.\']+$/u',
            ],

            'email' => [
                'required',
                'string',
                'lowercase',
                app()->environment('testing') ? 'email:rfc' : 'email:rfc,dns',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],

            'phone' => [
                'nullable',
                'regex:/^(\+63|0)9\d{9}$/',
            ],

            'address' => [
                'nullable',
                'string',
                'min:5',
                'max:255',
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'Name may only contain letters, spaces, hyphens, periods, and apostrophes.',
            'phone.regex' => 'Enter a valid Philippine mobile number (e.g. 0917 234 5678 or +63 917 234 5678).',
            'address.min' => 'Please enter a more complete address.',
        ];
    }
}
