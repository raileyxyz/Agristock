<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\StockOut;
use App\Models\StockAdjustment;
use Illuminate\Pagination\LengthAwarePaginator;

class InventoryHistoryService
{
    public function getMovements(array $filters)
    {
        $type = $filters['type'] ?? 'all';
        $search = $filters['search'] ?? null;
        $page = (int) ($filters['page'] ?? 1);
        $perPage = 15;

        $movements = collect();

        if (in_array($type, ['all', 'stock-in', 'transfer'])) {
            $movements = $movements->merge($this->stockInRows($search, $type));
        }

        if (in_array($type, ['all', 'stock-out', 'transfer'])) {
            $movements = $movements->merge($this->stockOutRows($search, $type));
        }

        if (in_array($type, ['all', 'adjustment'])) {
            $movements = $movements->merge($this->adjustmentRows($search));
        }

        $sorted = $movements->sortByDesc('date')->values();

        return new LengthAwarePaginator(
            $sorted->forPage($page, $perPage),
            $sorted->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    private function stockInRows(?string $search, string $type)
    {
        return Inventory::with(['product.unit', 'user'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('product', fn($q2) => $q2->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"))
                    ->orWhere('batch_number', 'like', "%{$search}%");
                });
            })
            ->get()
            ->map(function ($inv) {
                $isTransferMirror = str_starts_with($inv->notes ?? '', 'Transferred from');

                return (object) [
                    'date' => $inv->created_at,
                    'product_name' => $inv->product->name ?? '—',
                    'type' => $isTransferMirror ? 'Transfer In' : 'Stock In',
                    'type_class' => $isTransferMirror ? 'bg-blue-50 text-blue-700' : 'bg-green-50 text-green-700',
                    'batch_number' => $inv->batch_number,
                    'location' => $inv->location,
                    'quantity' => (float) $inv->quantity,
                    'unit_abbr' => $inv->product->unit->abbreviation ?? '',
                    'reason' => $isTransferMirror ? $inv->notes : '—',
                    'user_name' => $inv->user->name ?? '—',
                    'user_role' => $inv->user->role ?? '—',
                    'is_transfer' => $isTransferMirror,
                ];
            })
            ->when($type === 'stock-in', fn($rows) => $rows->reject(fn($row) => $row->is_transfer))
            ->when($type === 'transfer', fn($rows) => $rows->filter(fn($row) => $row->is_transfer));
    }

    private function stockOutRows(?string $search, string $type)
    {
        return StockOut::with(['product.unit', 'user'])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('product', fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
            })
            ->get()
            ->map(function ($out) {
                $isTransfer = $out->reason === 'Transfer';

                return (object) [
                    'date' => $out->created_at,
                    'product_name' => $out->product->name ?? '—',
                    'type' => $isTransfer ? 'Transfer Out' : 'Stock Out',
                    'type_class' => $isTransfer ? 'bg-blue-50 text-blue-700' : 'bg-red-50 text-red-700',
                    'batch_number' => $out->batch_numbers ?: '—',
                    'location' => $out->location,
                    'quantity' => -1 * (float) $out->quantity,
                    'unit_abbr' => $out->product->unit->abbreviation ?? '',
                    'reason' => $isTransfer ? "Transfer to {$out->transfer_to}" : $out->reason,
                    'user_name' => $out->user->name ?? '—',
                    'user_role' => $out->user->role ?? '—',
                    'is_transfer' => $isTransfer,
                ];
            })
            ->when($type === 'stock-out', fn($rows) => $rows->reject(fn($row) => $row->is_transfer))
            ->when($type === 'transfer', fn($rows) => $rows->filter(fn($row) => $row->is_transfer));
    }

    private function adjustmentRows(?string $search)
    {
        return StockAdjustment::with(['inventory.product.unit', 'user'])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('inventory.product', fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
            })
            ->get()
            ->map(fn($adj) => (object) [
                'date' => $adj->created_at,
                'product_name' => $adj->inventory->product->name ?? '—',
                'type' => 'Adjustment',
                'type_class' => 'bg-amber-50 text-amber-700',
                'batch_number' => $adj->inventory->batch_number ?? '—',
                'location' => $adj->inventory->location ?? '—',
                'quantity' => $adj->difference,
                'unit_abbr' => $adj->inventory->product->unit->abbreviation ?? '',
                'reason' => $adj->reason,
                'user_name' => $adj->user->name ?? '—',
                'user_role' => $adj->user->role ?? '—',
            ]);
    }
}
