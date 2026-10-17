<?php

namespace App\Http\Requests;

use App\Enums\Status;
use App\Enums\UserRole;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge(['email' => Str::lower(trim((string) $this->input('email')))]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255'
            ],

            'email' => [
                'required',
                app()->environment('testing') ? 'email:rfc' : 'email:rfc,dns',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId)
            ],

            'role' => [
                'required',
                new Enum(UserRole::class)
            ],

            'status' => [
                'required',
                Rule::in(Status::values())
            ],

            'current_password' => [
                Rule::requiredIf(fn () => $this->input('role') === UserRole::ADMIN->value
                    && $this->route('user')->role !== UserRole::ADMIN),
                'current_password',
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
            'current_password.required' => 'Enter your password to confirm promoting a user to Admin.',
            'current_password.current_password' => 'The password you entered is incorrect.',
        ];
    }
}
