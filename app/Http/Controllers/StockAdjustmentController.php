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

    public function index(Request $request)
    {
        $adjustments = $this->stockAdjustmentService->getAdjustments($request->all());

        return view('stock-adjustments.index', compact('adjustments'));
    }

    public function create()
    {
        abort_unless(Auth::user()?->role === 'Admin', 403);

        $products = Product::active()->with('unit')->orderBy('name')->get();
        $stockData = $this->stockAdjustmentService->getStockData();

        return view('stock-adjustments.create', compact('products', 'stockData'));
    }

    public function store(StoreStockAdjustmentRequest $request)
    {
        abort_unless(Auth::user()?->role === 'Admin', 403);

        try {
            $this->stockAdjustmentService->create($request->validated());

            return redirect()
                ->route('stock-adjustments.create')
                ->with('success', 'Stock adjustment recorded successfully.');

        } catch (\Exception $e) {
            return redirect()
                ->route('stock-adjustments.create')
                ->with('error', $e->getMessage());
        }
    }
}
