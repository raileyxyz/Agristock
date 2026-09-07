<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function stock(): View
    {
        $this->authorize('reports.stock');

        $summary = $this->reportService->getStockSummary();
        $categoryData = $this->reportService->getStockByCategory();

        return view('reports.stock', compact('summary', 'categoryData'));
    }

    public function movement(): View
    {
        $this->authorize('reports.movement');

        $summary = $this->reportService->getMovementSummary();
        $productData = $this->reportService->getMovementByProduct();

        return view('reports.movement', compact('summary', 'productData'));
    }
}
