<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockOut;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function getSummaryCards(): array
    {
        $lowStockProducts = Product::needsReorder()->get(['id', 'minimum_stock']);

        $criticalCount = $lowStockProducts->filter(function ($product) {
            return ($product->inventories_sum_remaining_quantity ?? 0) <= $product->minimum_stock;
        })->count();

        $expirySummary = $this->reportService->getExpirySummary();

        return [
            'total_products' => Product::where('status', 'Active')->count(),
            'total_products_archived' => Product::where('status', 'Archived')->count(),
            'total_categories' => Category::where('status', 'Active')->count(),
            'low_stock_count' => $lowStockProducts->count(),
            'low_stock_critical' => $criticalCount,
            'expiring_soon_count' => $expirySummary['within_30'] + $expirySummary['within_60'],
            'expired_count' => $expirySummary['expired'],
            'monthly_inventory_value' => $this->currentInventoryValue(),
        ];
    }

    /**
     * Reconstructed inventory value at the end of each of the last N months,
     * using current cost_price as an approximation for historical value.
     */
    public function getMonthlyInventoryValueTrend(int $months = 6): array
    {
        $labels = [];
        $values = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $endOfMonth = now()->subMonths($i)->endOfMonth();

            $received = Inventory::query()
                ->join('products', 'products.id', '=', 'inventories.product_id')
                ->where('inventories.created_at', '<=', $endOfMonth)
                ->sum(DB::raw('inventories.quantity * products.cost_price'));

            $consumed = StockOut::query()
                ->join('products', 'products.id', '=', 'stock_outs.product_id')
                ->where('stock_outs.created_at', '<=', $endOfMonth)
                ->sum(DB::raw('stock_outs.quantity * products.cost_price'));

            $adjusted = StockAdjustment::query()
                ->join('inventories', 'inventories.id', '=', 'stock_adjustments.inventory_id')
                ->join('products', 'products.id', '=', 'inventories.product_id')
                ->where('stock_adjustments.created_at', '<=', $endOfMonth)
                ->sum(DB::raw('(stock_adjustments.actual_quantity - stock_adjustments.system_quantity) * products.cost_price'));

            $labels[] = $endOfMonth->format('M');
            $values[] = round((float) $received - (float) $consumed + (float) $adjusted, 2);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    private function currentInventoryValue(): float
    {
        return (float) Inventory::query()
            ->join('products', 'products.id', '=', 'inventories.product_id')
            ->sum(DB::raw('inventories.remaining_quantity * products.cost_price'));
    }

    public function getStockByCategory(): array
    {
        return $this->reportService->getStockByCategory();
    }

    public function getLowStockItems(int $limit = 8)
    {
        return Product::query()
            ->with('unit')
            ->needsReorder()
            ->orderByRaw('COALESCE(inventories_sum_remaining_quantity, 0) asc')
            ->limit($limit)
            ->get();
    }

    public function getExpiringSoonItems(int $limit = 5): array
    {
        $batches = $this->reportService->getExpiryBatches();

        return array_slice($batches['within_60'], 0, $limit);
    }
}
