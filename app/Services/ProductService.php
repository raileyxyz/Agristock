<?php

namespace App\Services;

use App\Models\Product;

class ProductService
{
    public function getProducts(array $filters)
    {
        return Product::query()
            ->with(['category', 'unit']) // Eager Loading
            ->search($filters['search'] ?? null)
            ->filterStatus($filters['status'] ?? null)
            ->filterCategories($filters['category'] ?? null)
            ->select([
                'id',
                'category_id',
                'unit_id',
                'name',
                'sku',
                'minimum_stock',
                'reorder_point',
                'cost_price',
                'selling_price',
                'status',
                'expiry_track',
            ])
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product;
    }

    public function archive(Product $product): void
    {
        $product->update([
            'status' => 'Archived',
        ]);
    }
}
