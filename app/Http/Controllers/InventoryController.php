<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Http\Request;
use App\Services\InventoryService;
use App\Http\Requests\StoreInventoryRequest;
use App\Http\Requests\UpdateInventoryRequest;

class InventoryController extends Controller
{
    public function __construct(
        private InventoryService $inventoryService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('inventory.view');

        $inventories = $this->inventoryService->getInventories($request->all());
        $summary = $this->inventoryService->getSummary();
        $categories = Category::where('status', 'Active')->orderBy('name')->get();
        $products = $this->getActiveProducts();

        return view('inventories.index', compact('inventories', 'summary', 'categories', 'products'));
    }

    public function create()
    {
        $this->authorize('inventory.stock-in');

        $products = $this->getActiveProducts();

        return view('inventories.create', compact('products'));
    }

    public function store(StoreInventoryRequest $request)
    {
        $this->authorize('inventory.stock-in');

        $this->inventoryService->create($request->validated());

        return redirect()->route('inventories.create')->with('success', 'Stock added successfully.');
    }

    public function edit(Inventory $inventory)
    {
        $this->authorize('inventory.manage');

        $products = $this->getActiveProducts();

        return view('inventories.edit', compact('inventory', 'products'));
    }

    public function update(UpdateInventoryRequest $request, Inventory $inventory)
    {
        $this->authorize('inventory.manage');

        try {
            $this->inventoryService->update($inventory, $request->validated());

            return redirect()->route('inventories.index')->with('success', 'Stock updated successfully.');

        } catch (\Exception $e) {
            return redirect()->route('inventories.index')->with('error', $e->getMessage());
        }
    }

    public function destroy(Inventory $inventory)
    {
        $this->authorize('inventory.manage');

        $this->inventoryService->archive($inventory);

        return redirect()->route('inventories.index')->with('success', 'Stock archived successfully.');
    }

    private function getActiveProducts()
    {
        return Product::active()->with('unit')->orderBy('name')->get();
    }
}
