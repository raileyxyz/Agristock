<?php

namespace App\Listeners;

use App\Events\StockAdjusted;
use App\Services\NotificationDispatchService;

class SendStockAdjustmentNotification
{
    public function __construct(
        protected NotificationDispatchService $dispatchService
    ) {}

    public function handle(StockAdjusted $event): void
    {
        $product = $event->adjustment->inventory->product;

        $this->dispatchService->sendToEligibleUsers(
            'stock_adjusted',
            'Stock Adjustment Made',
            "{$event->actor->name} adjusted {$product->name} from {$event->adjustment->system_quantity} to {$event->adjustment->actual_quantity} ({$event->adjustment->reason}).",
            route('stock-adjustments.create'),
            $event->actor
        );
    }
}
