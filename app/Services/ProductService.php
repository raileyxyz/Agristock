<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;

class ProductService
{
    public function __construct(
        private SkuGeneratorService $skuGenerator
    ) {}

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
        if(empty($data['sku'])){
            $category = Category::findOrFail($data['category_id']);

            $data['sku'] = $this->skuGenerator->generate($category);
        }

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
