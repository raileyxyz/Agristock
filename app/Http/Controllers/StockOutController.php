<?php

namespace App\Http\Controllers;

use App\Enums\StorageLocation;
use App\Models\Product;
use App\Models\Inventory;
use App\Services\StockOutService;
use App\Http\Requests\StoreStockOutRequest;
use Illuminate\Http\Request;

class StockOutController extends Controller
{
    public function __construct(
        private StockOutService $stockOutService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::active()->with('unit')->orderBy('name')->get();
        $locations = StorageLocation::values();

        $inventories = Inventory::whereIn('product_id', $products->pluck('id'))
            ->where('remaining_quantity', '>', 0)
            ->get()
            ->groupBy(['product_id', 'location']);

        $stockData = [];
        foreach ($products as $product) {
            foreach ($locations as $location) {
                $batches = $inventories->get($product->id, collect())->get($location, collect());

                if ($batches->isEmpty()) continue;

                $sorted = $product->expiry_track
                    ? $batches->sortBy('expiry_date')
                    : $batches->sortBy('created_at');

                $stockData[$product->id][$location] = [
                    'available' => (float) $sorted->sum('remaining_quantity'),
                    'batch' => $sorted->first()->batch_number,
                ];
            }
        }

        return view('stock-outs.create', compact('products', 'stockData', 'locations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStockOutRequest $request)
    {
        $this->stockOutService->create($request->validated());

        return redirect()->route('stock-outs.create')->with('success', 'Stock out recorded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
