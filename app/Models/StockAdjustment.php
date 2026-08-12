<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    protected $fillable = [
        'inventory_id',
        'user_id',
        'system_quantity',
        'actual_quantity',
        'reason',
        'notes',
    ];

    protected $casts = [
        'system_quantity' => 'decimal:2',
        'actual_quantity' => 'decimal:2',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getDifferenceAttribute(): float
    {
        return (float) $this->actual_quantity - (float) $this->system_quantity;
    }

    public function scopeSearch($query, $search)
    {
        return $query->when($search, function ($query) use ($search) {
            $query->whereHas('inventory.product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%");
            })
            ->orWhereHas('inventory', function ($q) use ($search) {
                $q->where('batch_number', 'like', "%{$search}%");
            });
        });
    }

    public function scopeFilterReason($query, $reason)
    {
        return $query->when($reason, fn($q) => $q->where('reason', $reason));
    }
}
