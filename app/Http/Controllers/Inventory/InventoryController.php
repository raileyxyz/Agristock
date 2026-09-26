<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Inventory;
use App\Enums\StorageLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $inventories = $this->inventoryService->getInventories($request->all());
        $summary = $this->inventoryService->getSummary();
        $categories = Category::active()->orderBy('name')->get();
        $products = $this->inventoryService->getActiveProducts();
        $suppliers = $this->inventoryService->getActiveSuppliers();
        $locations = StorageLocation::values();

        return view('inventories.index', compact('inventories', 'summary', 'categories', 'products', 'suppliers', 'locations'));
    }

    public function create()
    {
        $products = $this->inventoryService->getActiveProducts();
        $suppliers = $this->inventoryService->getActiveSuppliers();
        $locations = StorageLocation::values();

        return view('inventories.create', compact('products', 'suppliers', 'locations'));
    }

    public function store(StoreInventoryRequest $request)
    {
        $this->inventoryService->create($request->validated(), Auth::user());

        return redirect()->route('inventories.create')->with('success', 'Stock in recorded successfully.');
    }

    public function edit(string $id)
    {
        //
    }

    public function update(UpdateInventoryRequest $request, Inventory $inventory)
    {
        $result = $this->inventoryService->update($inventory, $request->validated());

        return redirect()->route('inventories.index')->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}
