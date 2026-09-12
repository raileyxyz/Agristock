<?php

namespace App\Listeners;

use App\Events\StockTransferCompleted;
use App\Services\NotificationDispatchService;

class SendStockTransferNotification
{
    public function __construct(
        protected NotificationDispatchService $dispatchService
    ) {}

    public function handle(StockTransferCompleted $event): void
    {
        $product = $event->stockOut->product;

        $this->dispatchService->sendToEligibleUsers(
            'stock_transfer',
            'Stock Transfer Completed',
            "{$event->actor->name} transferred {$event->stockOut->quantity} {$product->unit->abbreviation} of {$product->name} from {$event->stockOut->location} to {$event->stockOut->transfer_to}.",
            route('inventories.index'),
            $event->actor
        );
    }
}
