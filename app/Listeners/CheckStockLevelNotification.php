<?php

namespace App\Listeners;

use App\Events\StockLevelChanged;
use App\Services\NotificationDispatchService;

class CheckStockLevelNotification
{
    public function __construct(
        protected NotificationDispatchService $dispatchService
    ) {}

    public function handle(StockLevelChanged $event): void
    {
        $product = $event->product;

        if ($event->after <= $product->minimum_stock && $event->before > $product->minimum_stock) {
            $this->dispatchService->sendToEligibleUsers(
                'critical_stock',
                'Critical Stock Alert',
                "{$product->name} has reached a critical stock level ({$event->after} remaining).",
                route('low-stock.index'),
                $event->actor
            );

            return;
        }

        if ($event->after <= $product->reorder_point && $event->before > $product->reorder_point) {
            $this->dispatchService->sendToEligibleUsers(
                'low_stock',
                'Low Stock Alert',
                "{$product->name} has reached its reorder point ({$event->after} remaining).",
                route('low-stock.index'),
                $event->actor
            );
        }
    }
}
