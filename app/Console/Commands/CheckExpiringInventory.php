<?php

namespace App\Console\Commands;

use App\Models\Inventory;
use App\Services\NotificationDispatchService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckExpiringInventory extends Command
{
    protected $signature = 'inventory:check-expiring';

    protected $description = 'Check inventory batches for expiring/expired status and send notifications';

    public function handle(NotificationDispatchService $dispatchService): int
    {
        $batches = Inventory::query()
            ->whereNotNull('expiry_date')
            ->whereHas('product', fn ($q) => $q->where('status', 'Active')->where('expiry_track', true))
            ->with('product')
            ->get();

        $expiredCount = 0;
        $expiringSoonCount = 0;

        foreach ($batches as $batch) {
            $daysUntilExpiry = Carbon::today()->diffInDays($batch->expiry_date, false);

            if ($daysUntilExpiry < 0) {
                if (is_null($batch->expired_notified_at)) {
                    $dispatchService->sendToEligibleUsers(
                        'expired_products',
                        'Expired Products',
                        "{$batch->product->name} (Batch {$batch->batch_number}) has expired.",
                        route('reports.expiry'),
                        null
                    );

                    $batch->update(['expired_notified_at' => now()]);
                    $expiredCount++;
                }
            } elseif ($daysUntilExpiry <= 60) {
                if (is_null($batch->expiring_soon_notified_at)) {
                    $dispatchService->sendToEligibleUsers(
                        'expiring_soon',
                        'Expiring Soon',
                        "{$batch->product->name} (Batch {$batch->batch_number}) expires in {$daysUntilExpiry} days.",
                        route('reports.expiry'),
                        null
                    );

                    $batch->update(['expiring_soon_notified_at' => now()]);
                    $expiringSoonCount++;
                }
            }
        }

        $this->info("Checked {$batches->count()} batches — {$expiredCount} new expired, {$expiringSoonCount} new expiring soon.");

        return self::SUCCESS;
    }
}
