<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockOut;
use Carbon\Carbon;

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
    public function getMovementSummary(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        return [
            'total_received' => (float) Inventory::query()
                ->when($startDate, fn ($q) => $q->where('created_at', '>=', $startDate))
                ->when($endDate, fn ($q) => $q->where('created_at', '<=', $endDate))
                ->sum('quantity'),
            'total_consumed' => (float) StockOut::query()
                ->when($startDate, fn ($q) => $q->where('created_at', '>=', $startDate))
                ->when($endDate, fn ($q) => $q->where('created_at', '<=', $endDate))
                ->sum('quantity'),
            'adjustments_count' => StockAdjustment::query()
                ->when($startDate, fn ($q) => $q->where('created_at', '>=', $startDate))
                ->when($endDate, fn ($q) => $q->where('created_at', '<=', $endDate))
                ->count(),
        ];
    }

    /**
     * Received vs consumed quantity per product, for the grouped bar chart.
     */
    public function getMovementByProduct(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $received = Inventory::query()
            ->join('products', 'products.id', '=', 'inventories.product_id')
            ->when($startDate, fn ($q) => $q->where('inventories.created_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('inventories.created_at', '<=', $endDate))
            ->groupBy('products.id', 'products.name')
            ->selectRaw('products.name as product_name, SUM(inventories.quantity) as total')
            ->pluck('total', 'product_name');

        $consumed = StockOut::query()
            ->join('products', 'products.id', '=', 'stock_outs.product_id')
            ->when($startDate, fn ($q) => $q->where('stock_outs.created_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('stock_outs.created_at', '<=', $endDate))
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

    /**
     * Summary counts for the Expiry Report cards.
     */
    public function getExpirySummary(): array
    {
        $batches = $this->trackedExpiryBatches();

        $expired = 0;
        $within30 = 0;
        $within60 = 0;
        $safe = 0;

        foreach ($batches as $batch) {
            $bucket = $this->resolveExpiryBucket($batch->expiry_date);

            match ($bucket) {
                'expired' => $expired++,
                'within_30' => $within30++,
                'within_60' => $within60++,
                default => $safe++,
            };
        }

        return [
            'tracked' => $batches->count(),
            'expired' => $expired,
            'within_30' => $within30,
            'within_60' => $within60,
            'safe' => $safe,
        ];
    }

    /**
     * Inventory batches with an expiry date, grouped into Expired / Expiring within 60 days / Safe.
     */
    public function getExpiryBatches(): array
    {
        $batches = $this->trackedExpiryBatches();

        $grouped = [
            'expired' => collect(),
            'within_60' => collect(),
            'safe' => collect(),
        ];

        foreach ($batches as $batch) {
            $bucket = $this->resolveExpiryBucket($batch->expiry_date);
            $days = (int) Carbon::today()->diffInDays($batch->expiry_date, false);

            $row = [
                'product_name' => $batch->product->name,
                'category_name' => $batch->product->category->name ?? '—',
                'category_icon' => $batch->product->category->icon ?? 'package',
                'category_color' => $batch->product->category->icon_color ?? '#9ca3af',
                'quantity' => $batch->remaining_quantity,
                'unit_abbr' => $batch->product->unit->abbreviation ?? '',
                'batch_number' => $batch->batch_number,
                'expiry_date' => $batch->expiry_date->format('Y-m-d'),
                'days' => $days,
            ];

            match ($bucket) {
                'expired' => $grouped['expired']->push($row),
                'within_30', 'within_60' => $grouped['within_60']->push($row),
                default => $grouped['safe']->push($row),
            };
        }

        return [
            'expired' => $grouped['expired']->values()->toArray(),
            'within_60' => $grouped['within_60']->values()->toArray(),
            'safe' => $grouped['safe']->values()->toArray(),
        ];
    }

    /**
     * Active, expiry-tracked inventory batches with an expiry date set.
     */
    private function trackedExpiryBatches()
    {
        return Inventory::query()
            ->whereNotNull('expiry_date')
            ->whereHas('product', fn ($q) => $q->where('status', 'Active')->where('expiry_track', true))
            ->with(['product.category', 'product.unit'])
            ->orderBy('expiry_date')
            ->get();
    }

    /**
     * Classify a single expiry date into a bucket.
     */
    private function resolveExpiryBucket($expiryDate): string
    {
        $days = Carbon::today()->diffInDays($expiryDate, false);

        return match (true) {
            $days < 0 => 'expired',
            $days <= 30 => 'within_30',
            $days <= 60 => 'within_60',
            default => 'safe',
        };
    }
}
