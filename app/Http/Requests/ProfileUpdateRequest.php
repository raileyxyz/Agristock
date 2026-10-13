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
        $rules = [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[\pL\s\-\.\']+$/u',
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

        if ($this->user()->isAdmin()) {
            $rules['email'] = [
                'required',
                'string',
                'lowercase',
                app()->environment('testing') ? 'email:rfc' : 'email:rfc,dns',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ];

            if (strcasecmp((string) $this->input('email'), $this->user()->email) !== 0) {
                $rules['current_password'] = ['required', 'current_password'];
            }
        }

        return $rules;
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
            'current_password.required' => 'Enter your current password to change your email.',
            'current_password.current_password' => 'The password you entered is incorrect.',
        ];
    }
}
