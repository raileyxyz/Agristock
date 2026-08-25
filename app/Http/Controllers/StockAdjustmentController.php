<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\StockAdjustmentService;
use App\Http\Requests\StoreStockAdjustmentRequest;
use Illuminate\Support\Facades\Auth;

class StockAdjustmentController extends Controller
{
    public function __construct(
        private StockAdjustmentService $stockAdjustmentService
    ) {}

    public function create()
    {
        $this->authorize('inventory.manage');

        $products = Product::active()->with('unit')->orderBy('name')->get();
        $stockData = $this->stockAdjustmentService->getStockData();

        return view('stock-adjustments.create', compact('products', 'stockData'));
    }

    public function store(StoreStockAdjustmentRequest $request)
    {
        $this->authorize('inventory.manage');

        try {
            $this->stockAdjustmentService->create($request->validated());

            return redirect()->route('stock-adjustments.create')->with('success', 'Stock adjustment recorded successfully.');

        } catch (\Exception $e) {
            return redirect()->route('stock-adjustments.create')->with('error', $e->getMessage());
        }
    }
}
