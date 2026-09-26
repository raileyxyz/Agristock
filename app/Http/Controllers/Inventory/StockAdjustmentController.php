<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Product;
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
        $products = Product::active()->with('unit')->orderBy('name')->get();
        $stockData = $this->stockAdjustmentService->getStockData();

        return view('stock-adjustments.create', compact('products', 'stockData'));
    }

    public function store(StoreStockAdjustmentRequest $request)
    {
        $result = $this->stockAdjustmentService->create($request->validated(), Auth::user());

        return redirect()->route('stock-adjustments.create')->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}
