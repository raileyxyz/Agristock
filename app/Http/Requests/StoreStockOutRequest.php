<?php

namespace App\Http\Requests;

use App\Enums\StorageLocation;
use App\Models\Inventory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockOutRequest extends FormRequest
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
            'product_id' => [
                'required',
                Rule::exists('products', 'id'),
            ],

            'location' => [
                'required',
                Rule::in(StorageLocation::values()),
            ],

            'quantity' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'reason' => [
                'required',
                Rule::in([
                    'Sale',
                    'Damaged',
                    'Expired',
                    'Transfer',
                    'Adjustment',
                    'Return to Supplier',
                    'Other',
                ]),
            ],

            'transfer_to' => [
                Rule::requiredIf(
                    fn () => $this->reason === 'Transfer'
                ),
                'nullable',
                Rule::in(StorageLocation::values()),
                function ($attribute, $value, $fail) {
                    if ($value && $value === $this->location) {
                        $fail('Transfer destination must be different from the current location.');
                    }
                },
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.exists' => 'Selected product is invalid.',
            'location.in' => 'Invalid storage location.',
            'reason.in' => 'Invalid reason selected.',
            'transfer_to.required' => 'Please select a transfer destination.',
        ];
    }
}
