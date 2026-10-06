<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class ChatbotToolService
{
    public const TOOL_ABILITIES = [
        'get_product_stock' => 'inventory.view',
        'get_low_stock_products' => 'inventory.view',
        'find_suppliers' => 'suppliers.view',
    ];

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
                    'description' => 'List active products whose total stock is at or below their reorder point.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => new \stdClass(),
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
            'get_low_stock_products' => $this->getLowStockProducts(),
            'find_suppliers' => $this->findSuppliers((string) ($arguments['keyword'] ?? '')),
        };
    }

    private function canUse(User $user, string $tool): bool
    {
        $ability = self::TOOL_ABILITIES[$tool] ?? null;

        return $ability !== null && Gate::forUser($user)->allows($ability);
    }

    private function getProductStock(string $search): array
    {
        if (trim($search) === '') {
            return ['error' => 'product_name is required'];
        }

        $products = Product::active()
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            })
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

    private function getLowStockProducts(): array
    {
        $products = Product::needsReorder()
            ->with('unit:id,name')
            ->orderBy('name')
            ->limit(15)
            ->get();

        return [
            'count' => $products->count(),
            'products' => $products->map(fn ($product) => [
                'name' => $product->name,
                'sku' => $product->sku,
                'total_stock' => (float) ($product->inventories_sum_remaining_quantity ?? 0),
                'unit' => $product->unit?->name,
                'reorder_point' => (float) $product->reorder_point,
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
