<?php

namespace App\View\Composers;

use App\Services\LowStockService;
use Illuminate\View\View;

class SidebarComposer
{
    public function __construct(
        private LowStockService $lowStockService
    ) {}

    public function compose(View $view): void
    {
        $view->with('lowStockCount', $this->lowStockService->getSummary());
    }
}
