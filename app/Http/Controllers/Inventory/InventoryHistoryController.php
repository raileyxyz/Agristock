<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\InventoryHistoryService;

class InventoryHistoryController extends Controller
{
    public function __construct(
        private InventoryHistoryService $historyService
    ) {}

    public function index(Request $request)
    {
        $movements = $this->historyService->getMovements($request->all());

        return view('inventory-history.index', compact('movements'));
    }
}
