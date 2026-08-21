<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSupplierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-Z0-9À-ÿ\s.,\'&\-]+$/',
            ],

            'contact_person' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-ZÀ-ÿ\s.\'-]+$/',
            ],

            'phone' => [
                'required',
                'string',
                'regex:/^(09\d{9}|\+639\d{9})$/',
                Rule::unique('suppliers', 'phone')->ignore($this->supplier),
            ],

            'email' => [
                'nullable',
                'string',
                'email:rfc',
                'max:255',
                Rule::unique('suppliers', 'email')->ignore($this->supplier),
            ],

            'address' => [
                'nullable',
                'string',
                'max:500'
            ],

            'status' => [
                'required',
                Rule::in(['Active', 'Archived',]),
            ],

            'supply_categories' => [
                'required',
                'array',
                'min:1'
            ],

            'supply_categories.*' => ['exists:categories,id'],

            'notes' => [
                'nullable',
                'string',
                'max:1000'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'company_name.regex' => 'Company name contains invalid characters.',
            'contact_person.regex' => 'Contact person should only contain letters, spaces, periods, apostrophes, and hyphens.',
            'phone.regex' => 'Enter a valid Philippine mobile number (e.g. 09171234567 or +639171234567).',
            'phone.unique' => 'This phone number is already registered.',
            'email.email' => 'Enter a valid email address.',
            'email.unique' => 'This email is already registered.',
            'supply_categories.required' => 'Select at least one supply category.',
            'supply_categories.min' => 'Select at least one supply category.',
        ];
    }
}
