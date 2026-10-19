<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;

class SkuGeneratorService
{
    public function generate(Category $category): string
    {
        $clean = preg_replace('/[^A-Za-z0-9]/', '', $category->name);
        $prefix = str_pad(strtoupper(substr($clean, 0, 3)), 3, 'X');
        $skuPrefix = $prefix . '-';

        $last = Product::where('sku', 'like', $skuPrefix . '%')
            ->orderByRaw('LENGTH(sku) DESC')
            ->orderByDesc('sku')
            ->value('sku');

        $next = $last ? ((int) substr($last, strlen($skuPrefix))) + 1 : 1;

        return $skuPrefix . str_pad($next, 3, '0', STR_PAD_LEFT);
    }
}
