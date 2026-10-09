<?php

namespace App\Services;

use App\Enums\Status;
use App\Enums\StorageLocation;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class ChatbotToolService
{
    public const TOOL_ABILITIES = [
        'get_product_stock' => 'inventory.view',
        'get_low_stock_products' => 'inventory.view',
        'find_suppliers' => 'suppliers.view',
        'get_expiry_status' => 'reports.expiry',
        'get_recent_movements' => 'inventory.view',
        'get_product_details' => 'products.view',
        'get_stock_by_location' => 'inventory.view',
        'get_inventory_summary' => 'reports.stock',
        'get_inventory_value' => 'reports.valuation',
        'get_movement_summary' => 'reports.movement',
    ];

    public function __construct(
        private ReportService $reports,
        private InventoryHistoryService $history,
        private DashboardService $dashboard,
    ) {}

    public function definitions(User $user): array
    {
        return array_values(array_filter(
            $this->allDefinitions(),
            fn (array $tool) => $this->canUse($user, $tool['function']['name'])
        ));
    }

    private function allDefinitions(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_product_stock',
                    'description' => 'Get the current stock of a product by name or SKU. Use this when the user asks how many of a product are in stock.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'product_name' => [
                                'type' => 'string',
                                'description' => 'Product name or SKU, e.g. "Urea"',
                            ],
                        ],
                        'required' => ['product_name'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_low_stock_products',
                    'description' => 'List active products whose total stock is at or below their reorder point. Set only_out_of_stock to true to list only products that have no stock left.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'only_out_of_stock' => [
                                'type' => 'boolean',
                                'description' => 'true = only products with zero stock',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'find_suppliers',
                    'description' => 'Find active suppliers by product category or product name, e.g. "seeds" or "Urea".',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'keyword' => [
                                'type' => 'string',
                                'description' => 'Category name or product name',
                            ],
                        ],
                        'required' => ['keyword'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_expiry_status',
                    'description' => 'List batches that are expired or expiring soon (within 60 days). Only batches that still have stock are included.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'status' => [
                                'type' => 'string',
                                'enum' => ['all', 'expired', 'expiring_soon'],
                                'description' => 'Which batches to list. Defaults to all.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_recent_movements',
                    'description' => 'Show inventory history: stock in, stock out, transfers, and adjustments, newest first, including who recorded each one. Use it for questions like "who stocked out Urea?" or "what did I stock out today?".',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'type' => [
                                'type' => 'string',
                                'enum' => ['all', 'stock-in', 'stock-out', 'transfer', 'adjustment'],
                                'description' => 'Kind of movement. Defaults to all.',
                            ],
                            'search' => [
                                'type' => 'string',
                                'description' => 'Product name, SKU, or batch number',
                            ],
                            'days' => [
                                'type' => 'integer',
                                'description' => 'Only movements from the last N days (1 = today only)',
                            ],
                            'only_mine' => [
                                'type' => 'boolean',
                                'description' => 'true = only movements recorded by the current user',
                            ],
                            'page' => [
                                'type' => 'integer',
                                'description' => 'Page number when has_more is true. Defaults to 1.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_product_details',
                    'description' => 'Get the details of a product: category, unit, minimum stock, reorder point, cost price, selling price, whether it tracks expiry, and total stock.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'product_name' => [
                                'type' => 'string',
                                'description' => 'Product name or SKU',
                            ],
                        ],
                        'required' => ['product_name'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_stock_by_location',
                    'description' => 'Show where stock is stored. With no arguments it summarizes every storage location. Give a location and/or a product to see quantities. Locations: ' . implode(', ', StorageLocation::values()) . '.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'location' => [
                                'type' => 'string',
                                'description' => 'Storage location name, e.g. "Main Warehouse"',
                            ],
                            'product_name' => [
                                'type' => 'string',
                                'description' => 'Product name or SKU',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_inventory_summary',
                    'description' => 'Get an overview of the inventory: how many active products are in stock, low on stock, or out of stock, plus expiry counts and supplier counts when the user may see them.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => new \stdClass(),
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_inventory_value',
                    'description' => 'Get the total value of the inventory in pesos (remaining stock multiplied by cost price). Optionally include the approximate value at the end of each of the last 6 months.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'include_monthly_trend' => [
                                'type' => 'boolean',
                                'description' => 'true = also return the monthly inventory value trend',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_movement_summary',
                    'description' => 'Movement report: total received, total consumed, and number of adjustments for a period, plus the top products by quantity consumed.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'range' => [
                                'type' => 'string',
                                'enum' => ['7d', '30d', '1y', 'all'],
                                'description' => 'Period ending today. Defaults to all.',
                            ],
                            'start_date' => [
                                'type' => 'string',
                                'description' => 'Custom period start, YYYY-MM-DD. Overrides range.',
                            ],
                            'end_date' => [
                                'type' => 'string',
                                'description' => 'Custom period end, YYYY-MM-DD. Overrides range.',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function execute(User $user, string $name, array $arguments): array
    {
        if (! array_key_exists($name, self::TOOL_ABILITIES)) {
            return ['error' => "Unknown tool: {$name}"];
        }

        if (! $this->canUse($user, $name)) {
            return ['error' => 'You do not have access to this information.'];
        }

        return match ($name) {
            'get_product_stock' => $this->getProductStock((string) ($arguments['product_name'] ?? '')),
            'get_low_stock_products' => $this->getLowStockProducts(
                filter_var($arguments['only_out_of_stock'] ?? false, FILTER_VALIDATE_BOOLEAN)
            ),
            'find_suppliers' => $this->findSuppliers((string) ($arguments['keyword'] ?? '')),
            'get_expiry_status' => $this->getExpiryStatus((string) ($arguments['status'] ?? 'all')),
            'get_recent_movements' => $this->getRecentMovements($user, $arguments),
            'get_product_details' => $this->getProductDetails((string) ($arguments['product_name'] ?? '')),
            'get_stock_by_location' => $this->getStockByLocation(
                (string) ($arguments['location'] ?? ''),
                (string) ($arguments['product_name'] ?? '')
            ),
            'get_inventory_summary' => $this->getInventorySummary($user),
            'get_inventory_value' => $this->getInventoryValue(
                filter_var($arguments['include_monthly_trend'] ?? false, FILTER_VALIDATE_BOOLEAN)
            ),
            'get_movement_summary' => $this->getMovementSummary($arguments),
        };
    }

    private function canUse(User $user, string $tool): bool
    {
        $ability = self::TOOL_ABILITIES[$tool] ?? null;

        return $ability !== null && $this->can($user, $ability);
    }

    private function can(User $user, string $ability): bool
    {
        return Gate::forUser($user)->allows($ability);
    }

    private function activeProductsMatching(string $search)
    {
        return Product::active()->where(function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%");
        });
    }

    private function getProductStock(string $search): array
    {
        if (trim($search) === '') {
            return ['error' => 'product_name is required'];
        }

        $products = $this->activeProductsMatching($search)
            ->with('unit:id,name')
            ->withSum('inventories', 'remaining_quantity')
            ->limit(5)
            ->get();

        if ($products->isEmpty()) {
            return ['found' => false, 'message' => "No active product matches '{$search}'."];
        }

        return [
            'found' => true,
            'products' => $products->map(fn ($product) => [
                'name' => $product->name,
                'sku' => $product->sku,
                'total_stock' => (float) ($product->inventories_sum_remaining_quantity ?? 0),
                'unit' => $product->unit?->name,
                'minimum_stock' => (float) $product->minimum_stock,
                'reorder_point' => (float) $product->reorder_point,
            ])->all(),
        ];
    }

    private function getLowStockProducts(bool $onlyOutOfStock = false): array
    {
        $products = Product::needsReorder()
            ->with('unit:id,name')
            ->orderBy('name')
            ->get();

        if ($onlyOutOfStock) {
            $products = $products->filter(fn ($product) => (float) ($product->inventories_sum_remaining_quantity ?? 0) <= 0);
        }

        return [
            'count' => $products->count(),
            'products' => $products->take(15)->map(fn ($product) => [
                'name' => $product->name,
                'sku' => $product->sku,
                'total_stock' => (float) ($product->inventories_sum_remaining_quantity ?? 0),
                'unit' => $product->unit?->name,
                'reorder_point' => (float) $product->reorder_point,
            ])->all(),
        ];
    }

    private function getProductDetails(string $search): array
    {
        if (trim($search) === '') {
            return ['error' => 'product_name is required'];
        }

        $products = $this->activeProductsMatching($search)
            ->with(['category:id,name', 'unit:id,name'])
            ->withSum('inventories', 'remaining_quantity')
            ->limit(5)
            ->get();

        if ($products->isEmpty()) {
            return ['found' => false, 'message' => "No active product matches '{$search}'."];
        }

        return [
            'found' => true,
            'products' => $products->map(fn ($product) => [
                'name' => $product->name,
                'sku' => $product->sku,
                'category' => $product->category?->name,
                'unit' => $product->unit?->name,
                'description' => $product->description ? Str::limit($product->description, 200) : null,
                'minimum_stock' => (float) $product->minimum_stock,
                'reorder_point' => (float) $product->reorder_point,
                'cost_price' => (float) $product->cost_price,
                'selling_price' => (float) $product->selling_price,
                'tracks_expiry' => (bool) $product->expiry_track,
                'total_stock' => (float) ($product->inventories_sum_remaining_quantity ?? 0),
            ])->all(),
        ];
    }

    private function getStockByLocation(string $location, string $product): array
    {
        $location = trim($location);
        $product = trim($product);

        $base = Inventory::query()
            ->inStock()
            ->join('products', 'products.id', '=', 'inventories.product_id')
            ->where('products.status', Status::ACTIVE->value)
            ->when($location !== '', fn ($query) => $query->where('inventories.location', 'like', "%{$location}%"))
            ->when($product !== '', fn ($query) => $query->where(function ($query) use ($product) {
                $query->where('products.name', 'like', "%{$product}%")
                    ->orWhere('products.sku', 'like', "%{$product}%");
            }));

        if ($location === '' && $product === '') {
            $rows = $base
                ->groupBy('inventories.location')
                ->orderBy('inventories.location')
                ->selectRaw('inventories.location as location, COUNT(DISTINCT inventories.product_id) as products, COUNT(*) as batches')
                ->get();

            return [
                'locations' => $rows->map(fn ($row) => [
                    'location' => $row->location,
                    'products' => (int) $row->products,
                    'batches' => (int) $row->batches,
                ])->all(),
            ];
        }

        $rows = $base
            ->leftJoin('units', 'units.id', '=', 'products.unit_id')
            ->groupBy('products.id', 'products.name', 'products.sku', 'units.name', 'inventories.location')
            ->orderBy('products.name')
            ->orderBy('inventories.location')
            ->selectRaw('products.name as product_name, products.sku as sku, units.name as unit_name, inventories.location as location, SUM(inventories.remaining_quantity) as quantity, COUNT(*) as batches')
            ->limit(21)
            ->get();

        if ($rows->isEmpty()) {
            return ['found' => false, 'message' => 'No stock found for that location or product.'];
        }

        return [
            'found' => true,
            'has_more' => $rows->count() > 20,
            'items' => $rows->take(20)->map(fn ($row) => [
                'product' => $row->product_name,
                'sku' => $row->sku,
                'location' => $row->location,
                'quantity' => (float) $row->quantity,
                'unit' => $row->unit_name,
                'batches' => (int) $row->batches,
            ])->values()->all(),
        ];
    }

    private function getInventorySummary(User $user): array
    {
        $stock = $this->reports->getStockSummary();

        $result = [
            'note' => 'low_stock means above zero but at or below the reorder point. out_of_stock means no stock left.',
            'products' => [
                'total_active' => $stock['total_skus'],
                'in_stock' => $stock['in_stock'],
                'low_stock' => $stock['low_critical'],
                'out_of_stock' => $stock['out_of_stock'],
            ],
        ];

        if ($this->can($user, 'reports.expiry')) {
            $expiry = $this->reports->getExpirySummary();

            $result['expiry'] = [
                'expired' => $expiry['expired'],
                'expiring_within_60_days' => $expiry['within_30'] + $expiry['within_60'],
            ];
        }

        if ($this->can($user, 'suppliers.view')) {
            $result['active_suppliers'] = Supplier::active()->count();
        }

        if ($this->can($user, 'reports.valuation')) {
            $result['inventory_value'] = round($this->dashboard->currentInventoryValue(), 2);
        }

        return $result;
    }

    private function getInventoryValue(bool $includeTrend): array
    {
        $result = [
            'currency' => 'PHP',
            'current_value' => round($this->dashboard->currentInventoryValue(), 2),
            'basis' => 'Remaining quantity of every batch that still has stock, multiplied by the current cost price.',
        ];

        if ($includeTrend) {
            $trend = $this->dashboard->getMonthlyInventoryValueTrend();

            $result['monthly_trend'] = [
                'note' => 'Approximate: past months are rebuilt from stock movements using the current cost price.',
                'months' => collect($trend['labels'])
                    ->map(fn ($label, $index) => ['month' => $label, 'value' => $trend['values'][$index]])
                    ->values()
                    ->all(),
            ];
        }

        return $result;
    }

    private function getMovementSummary(array $arguments): array
    {
        $range = (string) ($arguments['range'] ?? 'all');
        $start = trim((string) ($arguments['start_date'] ?? ''));
        $end = trim((string) ($arguments['end_date'] ?? ''));

        try {
            if ($start !== '' || $end !== '') {
                $label = 'custom';
                $startDate = $start !== '' ? Carbon::parse($start)->startOfDay() : null;
                $endDate = $end !== '' ? Carbon::parse($end)->endOfDay() : null;
            } else {
                $label = in_array($range, ['7d', '30d', '1y'], true) ? $range : 'all';
                [$startDate, $endDate] = match ($label) {
                    '7d' => [now()->subDays(7)->startOfDay(), now()->endOfDay()],
                    '30d' => [now()->subDays(30)->startOfDay(), now()->endOfDay()],
                    '1y' => [now()->subYear()->startOfDay(), now()->endOfDay()],
                    default => [null, null],
                };
            }
        } catch (\Throwable) {
            return ['error' => 'Invalid date. Use the format YYYY-MM-DD.'];
        }

        $summary = $this->reports->getMovementSummary($startDate, $endDate);
        $byProduct = $this->reports->getMovementByProduct($startDate, $endDate);

        $topProducts = collect($byProduct['labels'])
            ->map(fn ($name, $index) => [
                'product' => $name,
                'received' => $byProduct['received'][$index],
                'consumed' => $byProduct['consumed'][$index],
            ])
            ->sortByDesc('consumed')
            ->take(10)
            ->values()
            ->all();

        return [
            'note' => 'Totals add up quantities of different units, so use the per-product figures when precision matters.',
            'range' => $label,
            'from' => $startDate?->toDateString(),
            'to' => $endDate?->toDateString(),
            'total_received' => $summary['total_received'],
            'total_consumed' => $summary['total_consumed'],
            'adjustments_count' => $summary['adjustments_count'],
            'top_products_by_consumed' => $topProducts,
        ];
    }

    private function getExpiryStatus(string $status): array
    {
        $status = in_array($status, ['all', 'expired', 'expiring_soon'], true) ? $status : 'all';

        $summary = $this->reports->getExpirySummary();
        $batches = $this->reports->getExpiryBatches();

        $result = [
            'note' => 'Only batches that still have stock are counted. Expiring soon means within 60 days.',
            'summary' => [
                'expired' => $summary['expired'],
                'expiring_within_30_days' => $summary['within_30'],
                'expiring_in_31_to_60_days' => $summary['within_60'],
            ],
        ];

        if ($status !== 'expiring_soon') {
            $result['expired'] = $this->expiryRows($batches['expired'], 'days_overdue');
        }

        if ($status !== 'expired') {
            $result['expiring_soon'] = $this->expiryRows($batches['within_60'], 'days_left');
        }

        return $result;
    }

    private function expiryRows(array $rows, string $daysKey): array
    {
        return [
            'total' => count($rows),
            'items' => collect($rows)->take(15)->map(fn (array $row) => [
                'product' => $row['product_name'],
                'batch_number' => $row['batch_number'],
                'quantity' => (float) $row['quantity'],
                'unit' => $row['unit_abbr'],
                'expiry_date' => $row['expiry_date'],
                $daysKey => abs($row['days']),
            ])->all(),
        ];
    }

    private function getRecentMovements(User $user, array $arguments): array
    {
        $type = (string) ($arguments['type'] ?? 'all');
        $type = in_array($type, ['all', 'stock-in', 'stock-out', 'transfer', 'adjustment'], true) ? $type : 'all';

        $search = trim((string) ($arguments['search'] ?? ''));
        $onlyMine = filter_var($arguments['only_mine'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $movements = $this->history->getMovements([
            'type' => $type,
            'search' => $search !== '' ? $search : null,
            'days' => max(0, (int) ($arguments['days'] ?? 0)),
            'user_id' => $onlyMine ? $user->id : null,
            'page' => max(1, (int) ($arguments['page'] ?? 1)),
        ]);

        return [
            'note' => 'Quantity is negative when stock was removed. done_by is who recorded the movement.',
            'total' => $movements->total(),
            'page' => $movements->currentPage(),
            'has_more' => $movements->hasMorePages(),
            'movements' => collect($movements->items())->values()->map(fn ($movement) => [
                'date' => $movement->date->format('Y-m-d H:i'),
                'type' => $movement->type,
                'product' => $movement->product_name,
                'batch_number' => $movement->batch_number,
                'location' => $movement->location,
                'quantity' => (float) $movement->quantity,
                'unit' => $movement->unit_abbr,
                'reason' => $movement->reason,
                'done_by' => $movement->user_name,
            ])->all(),
        ];
    }

    private function findSuppliers(string $keyword): array
    {
        if (trim($keyword) === '') {
            return ['error' => 'keyword is required'];
        }

        $suppliers = Supplier::active()
            ->whereHas('categories', function ($query) use ($keyword) {
                $query->where('categories.status', Status::ACTIVE->value)
                    ->where(function ($query) use ($keyword) {
                        $query->where('categories.name', 'like', "%{$keyword}%")
                            ->orWhereIn('categories.id', Product::where('name', 'like', "%{$keyword}%")->select('category_id'));
                    });
            })
            ->with('categories:id,name')
            ->orderBy('company_name')
            ->limit(10)
            ->get();

        if ($suppliers->isEmpty()) {
            return ['found' => false, 'message' => "No active supplier found for '{$keyword}'."];
        }

        return [
            'found' => true,
            'suppliers' => $suppliers->map(fn ($supplier) => [
                'company_name' => $supplier->company_name,
                'contact_person' => $supplier->contact_person,
                'phone' => $supplier->phone,
                'categories' => $supplier->categories->pluck('name')->all(),
            ])->all(),
        ];
    }
}
