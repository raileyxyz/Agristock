<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
            ],

            'category_id' => [
                'required',
                Rule::exists('categories', 'id'),
            ],

            'unit_id' => [
                'required',
                Rule::exists('units', 'id'),
            ],

            'sku' => [
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'sku')
                    ->ignore($this->product),
            ],

            'cost_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'selling_price' => [
                'required',
                'numeric',
                'min:0',
                'gte:cost_price',
            ],

            'minimum_stock' => [
                'required',
                'integer',
                'min:0',
            ],

            'reorder_point' => [
                'required',
                'integer',
                'min:0',
                'gte:minimum_stock',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'status' => [
                'required',
                Rule::in([
                    'Active',
                    'Archived',
                ]),
            ],

            'expiry_track' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.exists' => 'Selected category is invalid.',
            'unit_id.exists' => 'Selected unit is invalid.',
            'sku.unique' => 'SKU already exists.',
            'selling_price.gte' => 'Selling price must not be lower than the cost price.',
            'reorder_point.gte' => 'Reorder point must not be lower than the minimum stock level.',
            'expiry_track.boolean' => 'Expiry tracking must be true or false.',

        ];
    }
}
