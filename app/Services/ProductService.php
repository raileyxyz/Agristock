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
            ->with(['category', 'unit'])
            ->search($filters['search'] ?? null)
            ->filterStatus($filters['status'] ?? 'Active')
            ->filterCategories($filters['category_id'] ?? null)
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
                'description',
                'status',
                'expiry_track',
            ])
            ->latest()
            ->paginate(15)
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

    public function getStatistics(): array
    {
        return [
            'total' => Product::count(),
            'active' => Product::where('status', 'Active')->count(),
            'archived' => Product::where('status', 'Archived')->count(),
        ];
    }
}
