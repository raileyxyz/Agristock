<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUnitRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('units', 'name'),
            ],

            'abbreviation' => [
                'required',
                'string',
                'max:10',
                Rule::unique('units', 'abbreviation'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'A unit with this name already exists.',
            'abbreviation.unique' => 'This abbreviation is already in use.',
        ];
    }

}
