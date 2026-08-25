<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Services\LowStockService;

class LowStockController extends Controller
{
    public function __construct(
        private LowStockService $lowStockService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('inventory.view');

        $products = $this->lowStockService->getLowStockProducts($request->all());
        $totalCount = $this->lowStockService->getSummary();
        $categories = Category::where('status', 'Active')->orderBy('name')->get();

        return view('low-stock.index', compact('products', 'totalCount', 'categories'));
    }
}
