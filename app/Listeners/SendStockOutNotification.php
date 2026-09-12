<?php

namespace App\Listeners;

use App\Events\StockOutRecorded;
use App\Services\NotificationDispatchService;

class SendStockOutNotification
{
    public function __construct(
        protected NotificationDispatchService $dispatchService
    ) {}

    public function handle(StockOutRecorded $event): void
    {
        $product = $event->stockOut->product;

        $this->dispatchService->sendToEligibleUsers(
            'stock_out',
            'Stock Out Recorded',
            "{$event->actor->name} recorded {$event->stockOut->quantity} {$product->unit->abbreviation} of {$product->name} removed ({$event->stockOut->reason}).",
            route('inventories.index'),
            $event->actor
        );
    }
}
