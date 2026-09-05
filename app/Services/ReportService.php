<?php

namespace App\Services;

use App\Models\Product;

class ReportService
{
    /**
     * Summary counts for the Stock Report cards.
     */
    public function getStockSummary(): array
    {
        $products = Product::query()
            ->where('status', 'Active')
            ->withSum(['inventories' => function ($q) {
                $q->where('status', '!=', 'Archived');
            }], 'remaining_quantity')
            ->get();

        $totalSkus = $products->count();
        $outOfStock = 0;
        $lowCritical = 0;
        $inStock = 0;

        foreach ($products as $product) {
            $remaining = $product->inventories_sum_remaining_quantity ?? 0;

            if ($remaining <= 0) {
                $outOfStock++;
            } elseif ($remaining <= $product->reorder_point) {
                $lowCritical++;
            } else {
                $inStock++;
            }
        }

        return [
            'total_skus' => $totalSkus,
            'in_stock' => $inStock,
            'low_critical' => $lowCritical,
            'out_of_stock' => $outOfStock,
        ];
    }

    /**
     * Total remaining quantity grouped by category, for the bar/donut charts.
     */
    public function getStockByCategory(): array
    {
        $rows = Product::query()
            ->where('products.status', 'Active')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->leftJoin('inventories', function ($join) {
                $join->on('inventories.product_id', '=', 'products.id')
                     ->where('inventories.status', '!=', 'Archived');
            })
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('categories.name')
            ->selectRaw('categories.name as category_name, COALESCE(SUM(inventories.remaining_quantity), 0) as total_quantity')
            ->get();

        return [
            'labels' => $rows->pluck('category_name')->toArray(),
            'values' => $rows->pluck('total_quantity')->toArray(),
        ];
    }
}
