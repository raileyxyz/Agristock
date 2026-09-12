<?php

namespace App\Listeners;

use App\Events\StockReceived;
use App\Services\NotificationDispatchService;

class SendStockReceivedNotification
{
    public function __construct(
        protected NotificationDispatchService $dispatchService
    ) {}

    public function handle(StockReceived $event): void
    {
        $product = $event->inventory->product;

        $this->dispatchService->sendToEligibleUsers(
            'stock_received',
            'Stock Received',
            "{$event->actor->name} recorded {$event->inventory->quantity} {$product->unit->abbreviation} of {$product->name} received.",
            route('inventories.index'),
            $event->actor
        );
    }
}
