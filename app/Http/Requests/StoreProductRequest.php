<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class StoreProductRequest extends FormRequest
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

            'category_id' => [
                'required',
                Rule::exists('categories', 'id'),
            ],

            'unit_id' => [
                'required',
                Rule::exists('units', 'id'),
            ],

            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'name'),
            ],

            'sku' => [
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'sku'),
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
            ],

            'cost_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'selling_price' => [
                'required',
                'numeric',
                'gte:cost_price',
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
                'required',
                'boolean',
            ],

        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Product already exists.',
            'sku.unique' => 'SKU already exists.',
            'selling_price.gte' => 'Selling price must be greater than or equal to the cost price.',
        ];
    }
}
