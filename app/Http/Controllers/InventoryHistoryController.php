<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\InventoryHistoryService;

class InventoryHistoryController extends Controller
{
    public function __construct(
        private InventoryHistoryService $historyService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('inventory.view');

        $movements = $this->historyService->getMovements($request->all());

        return view('inventory-history.index', compact('movements'));
    }
}
