<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockAdjustmentRequest extends FormRequest
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
            'inventory_id' => [
                'required',
                Rule::exists('inventories', 'id'),
            ],

            'actual_quantity' => [
                'required',
                'numeric',
                'min:0',
            ],

            'reason' => [
                'required',
                Rule::in(['Physical Count', 'Damaged Goods', 'Theft/Loss', 'Expired Removal', 'Data Entry Error', 'Other']),
            ],

            'notes' => [
                'nullable',
                'string',
                'max:100',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'inventory_id.exists' => 'Selected batch is invalid.',
            'actual_quantity.min' => 'Actual quantity cannot be negative.',
            'reason.in' => 'Invalid reason selected.',
        ];
    }

}
