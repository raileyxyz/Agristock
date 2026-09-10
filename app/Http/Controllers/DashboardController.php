<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    public function index(): View
    {
        $summary = $this->dashboardService->getSummaryCards();
        $valueTrend = $this->dashboardService->getMonthlyInventoryValueTrend();
        $categoryData = $this->dashboardService->getStockByCategory();
        $lowStockItems = $this->dashboardService->getLowStockItems();
        $expiringSoonItems = $this->dashboardService->getExpiringSoonItems();

        return view('dashboard', compact('summary', 'valueTrend', 'categoryData', 'lowStockItems', 'expiringSoonItems'));
    }
}
