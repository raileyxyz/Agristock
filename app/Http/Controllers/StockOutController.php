<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\StockOutService;
use App\Http\Requests\StoreStockOutRequest;
use Illuminate\Http\Request;

class StockOutController extends Controller
{
    private array $locations = ['Main Warehouse', 'Storage Room A', 'Storage Room B', 'Field Storage'];

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
        $products = Product::active()
            ->with('unit')
            ->orderBy('name')
            ->get();

        $locations = $this->locations;

        return view('stock-outs.create', compact('products', 'locations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStockOutRequest $request)
    {
        try {
            $this->stockOutService->create($request->validated());

            return redirect()
                ->route('inventories.index')
                ->with('success', 'Stock out recorded successfully.');

        } catch (\Exception $e) {
            return redirect()
                ->route('stock-outs.create')
                ->with('error', $e->getMessage());
        }
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
