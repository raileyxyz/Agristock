<?php

namespace App\Services;

use App\Enums\StorageLocation;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockOut;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockOutService
{
    public function __construct(
        private StockDeductionService $stockDeduction,
        private BatchNumberGeneratorService $batchNumberGenerator
    ) {}

    public function getAvailableStock(int $productId, string $location): float
    {
        return (float) Inventory::where('product_id', $productId)->where('location', $location)->sum('remaining_quantity');
    }

    public function getCreateData(): array
    {
        $products = Product::active()->with('unit')->orderBy('name')->get();

        $locations = StorageLocation::values();

        $inventories = Inventory::whereIn('product_id', $products->pluck('id'))
            ->where('remaining_quantity', '>', 0)
            ->get()
            ->groupBy(['product_id', 'location']);

        $stockData = [];

        foreach ($products as $product) {
            foreach ($locations as $location) {
                $batches = $inventories->get($product->id, collect())->get($location, collect());

                if ($batches->isEmpty()) {
                    continue;
                }

                $sorted = $product->expiry_track ? $batches->sortBy('expiry_date') : $batches->sortBy('created_at');

                $stockData[$product->id][$location] = [
                    'available' => (float) $sorted->sum('remaining_quantity'),
                    'batch' => $sorted->first()->batch_number,
                ];
            }
        }

        return compact('products', 'stockData',  'locations');
    }

    public function create(array $data): StockOut
    {
        return DB::transaction(function () use ($data) {
            $product = Product::findOrFail($data['product_id']);

            $consumedBatches = $this->stockDeduction->deduct(
                $product,
                $data['location'],
                $data['quantity']
            );

            $data['batch_numbers'] = collect($consumedBatches)
                ->pluck('batch_number')
                ->implode(', ');

            if ($data['reason'] === 'Transfer' && ! empty($data['transfer_to'])) {
                $this->mirrorTransferAtDestination($product, $data, $consumedBatches);
            }

            $data['user_id'] = Auth::id();

            return StockOut::create($data);
        });
    }

    private function mirrorTransferAtDestination(Product $product, array $data, array $consumedBatches): void
    {
        foreach ($consumedBatches as $batch) {
            $existing = Inventory::where('product_id', $product->id)
                ->where('location', $data['transfer_to'])
                ->where(function ($query) use ($batch) {
                    if ($batch['expiry_date']) {
                        $query->whereDate('expiry_date', $batch['expiry_date']);
                    } else {
                        $query->whereNull('expiry_date');
                    }
                })
                ->where('remaining_quantity', '>', 0)
                ->first();

            if ($existing) {
                $existing->quantity += $batch['quantity'];
                $existing->remaining_quantity += $batch['quantity'];
                $existing->notes = trim(
                    ($existing->notes ? $existing->notes . ' | ' : '') .
                    "Received {$batch['quantity']} from {$data['location']} (origin batch: {$batch['batch_number']})"
                );
                $existing->save();

                continue;
            }

            Inventory::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'quantity' => $batch['quantity'],
                'remaining_quantity' => $batch['quantity'],
                'batch_number' => $this->batchNumberGenerator->generate(),
                'expiry_date' => $batch['expiry_date'],
                'location' => $data['transfer_to'],
                'notes' => "Transferred from {$data['location']} (origin batch: {$batch['batch_number']})",
            ]);
        }
    }
}
