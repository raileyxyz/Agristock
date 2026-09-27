<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Services\StockOutService;
use App\Http\Requests\StoreStockOutRequest;
use Illuminate\Support\Facades\Auth;

class StockOutController extends Controller
{
    public function __construct(
        private StockOutService $stockOutService
    ) {}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data = $this->stockOutService->getCreateData();

        return view('stock-outs.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStockOutRequest $request)
    {
        $this->stockOutService->create($request->validated(), Auth::user());

        return redirect()->route('stock-outs.create')->with('success', 'Stock out recorded successfully.');
    }
}
