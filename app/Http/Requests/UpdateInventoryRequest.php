<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                Rule::exists('products', 'id'),
            ],

            'supplier_id' => [
                'nullable',
                'integer',
            ],

            'quantity' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'batch_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('inventories', 'batch_number')->ignore($this->inventory),
            ],

            'expiry_date' => [
                Rule::requiredIf(function () {
                    $product = Product::find($this->product_id);
                    return $product && $product->expiry_track;
                }),
                'nullable',
                'date',
            ],

            'location' => [
                'required',
                'string',
                'max:100',
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
            'quantity.min' => 'Quantity must be greater than zero.',
            'expiry_date.required' => 'Expiry date is required for this product.',
        ];
    }
}
