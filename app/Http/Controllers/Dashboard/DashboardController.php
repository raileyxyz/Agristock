<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    public function index(Request $request): View
    {
        $canSeeValue = $request->user()->can('reports.valuation');

        $summary = $this->dashboardService->getSummaryCards($canSeeValue);
        $valueTrend = $canSeeValue ? $this->dashboardService->getMonthlyInventoryValueTrend() : ['labels' => [], 'values' => []];
        $categoryData = $this->dashboardService->getStockByCategory();
        $lowStockItems = $this->dashboardService->getLowStockItems();
        $expiringSoonItems = $this->dashboardService->getExpiringSoonItems();

        return view('dashboard', compact('summary', 'valueTrend', 'categoryData', 'lowStockItems', 'expiringSoonItems'));
    }
}
