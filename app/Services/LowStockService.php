<?php

namespace App\Services;

use App\Models\Product;

class LowStockService
{
    public function getLowStockProducts(array $filters)
    {
        return Product::query()
            ->with(['category', 'unit'])
            ->needsReorder()
            ->search($filters['search'] ?? null)
            ->when($filters['category_id'] ?? null, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();
    }

    public function getSummary(): int
    {
        return Product::needsReorder()->count();
    }
}
