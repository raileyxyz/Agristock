<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockOut;

class ReportService
{
    /**
     * Summary counts for the Stock Report cards.
     */
    public function getStockSummary(): array
    {
        $products = Product::query()
            ->where('status', 'Active')
            ->withSum('inventories', 'remaining_quantity')
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
            ->leftJoin('inventories', 'inventories.product_id', '=', 'products.id')
            ->groupBy('categories.id', 'categories.name', 'categories.icon_color')
            ->orderBy('categories.name')
            ->selectRaw('categories.name as category_name, categories.icon_color as category_color, COALESCE(SUM(inventories.remaining_quantity), 0) as total_quantity')
            ->get();

        return [
            'labels' => $rows->pluck('category_name')->toArray(),
            'values' => $rows->pluck('total_quantity')->toArray(),
            'colors' => $rows->pluck('category_color')->toArray(),
        ];
    }
    /**
     * Summary counts for the Movement Report cards.
     */
    public function getMovementSummary(): array
    {
        return [
            'total_received' => (float) Inventory::sum('quantity'),
            'total_consumed' => (float) StockOut::sum('quantity'),
            'adjustments_count' => StockAdjustment::count(),
        ];
    }

    /**
     * Received vs consumed quantity per product, for the grouped bar chart.
     */
    public function getMovementByProduct(): array
    {
        $received = Inventory::query()
            ->join('products', 'products.id', '=', 'inventories.product_id')
            ->groupBy('products.id', 'products.name')
            ->selectRaw('products.name as product_name, SUM(inventories.quantity) as total')
            ->pluck('total', 'product_name');

        $consumed = StockOut::query()
            ->join('products', 'products.id', '=', 'stock_outs.product_id')
            ->groupBy('products.id', 'products.name')
            ->selectRaw('products.name as product_name, SUM(stock_outs.quantity) as total')
            ->pluck('total', 'product_name');

        $labels = $received->keys()
            ->merge($consumed->keys())
            ->unique()
            ->sort()
            ->values();

        return [
            'labels' => $labels->toArray(),
            'received' => $labels->map(fn ($name) => (float) ($received[$name] ?? 0))->toArray(),
            'consumed' => $labels->map(fn ($name) => (float) ($consumed[$name] ?? 0))->toArray(),
        ];
    }
}
