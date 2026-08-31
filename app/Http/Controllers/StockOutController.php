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
        $this->authorize('inventory.stock-out');

        $data = $this->stockOutService->getCreateData();

        return view('stock-outs.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStockOutRequest $request)
    {
        $this->authorize('inventory.stock-out');

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
