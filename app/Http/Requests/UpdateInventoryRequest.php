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
                function ($attribute, $value, $fail) {
                    $inventory = $this->route('inventory');
                    if ($inventory && $inventory->quantity != $inventory->remaining_quantity && $value != $inventory->product_id) {
                        $fail('Product cannot be changed once this batch has stock movements.');
                    }
                },
            ],

            'supplier_id' => [
                'nullable',
                'integer',
            ],

            'quantity' => [
                'required',
                'numeric',
                'min:0.01',
                'regex:/^\d+(\.\d{1,2})?$/',
                function ($attribute, $value, $fail) {
                    $inventory = $this->route('inventory');
                    if ($inventory) {
                        $consumed = $inventory->quantity - $inventory->remaining_quantity;
                        if ($value < $consumed) {
                            $fail("Quantity cannot be less than {$consumed} (already consumed).");
                        }
                    }
                },
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
                'after:today',
            ],

            'location' => [
                'required',
                Rule::in(['Main Warehouse', 'Storage Room A', 'Storage Room B', 'Field Storage']),
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
            'product_id.exists' => 'Selected product is invalid.',
            'quantity.min' => 'Quantity must be greater than zero.',
            'expiry_date.required' => 'Expiry date is required for this product.',
            'quantity.regex' => 'Quantity can only have up to 2 decimal places.',
        ];
    }
}
