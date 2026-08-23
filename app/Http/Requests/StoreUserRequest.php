<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
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
                'max:255'
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email'
            ],

            'role' => [
                'required',
                Rule::in(User::ROLES)
            ],

            'password' => [
                'required',
                'string',
                'min:8'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter the full name.',
            'email.required' => 'Please enter an email address.',
            'email.unique' => 'This email is already registered.',
            'role.required' => 'Please select a role.',
            'role.in' => 'Selected role is invalid.',
            'password.required' => 'Please set a temporary password.',
            'password.min' => 'Password must be at least 8 characters.',
        ];
    }
}
