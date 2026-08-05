<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = [
        'product_id',
        'supplier_id',
        'quantity',
        'remaining_quantity',
        'batch_number',
        'expiry_date',
        'location',
        'notes',
        'status',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'quantity' => 'decimal:2',
        'remaining_quantity' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Uncomment once the Supplier model/table exists:
    // public function supplier()
    // {
    //     return $this->belongsTo(Supplier::class);
    // }

    public function scopeSearch($query, $search)
    {
        return $query->when($search, function ($query) use ($search) {
            $query->whereHas('product', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            })
            ->orWhere('batch_number', 'like', "%{$search}%")
            ->orWhere('location', 'like', "%{$search}%");
        });
    }

    public function scopeFilterCategory($query, $categoryId)
    {
        return $query->when($categoryId, function ($query) use ($categoryId) {
            $query->whereHas('product', fn($q) => $q->where('category_id', $categoryId));
        });
    }

    public function getStockStatusAttribute(): array
    {
        $remaining = $this->remaining_quantity;
        $minimum = $this->product->minimum_stock ?? 0;
        $reorder = $this->product->reorder_point ?? 0;

        if ($remaining <= 0) {
            return ['label' => 'Out of Stock', 'class' => 'bg-gray-100 text-gray-500'];
        }

        if ($remaining <= $minimum) {
            return ['label' => 'Critical', 'class' => 'bg-red-50 text-red-600'];
        }

        if ($remaining <= $reorder) {
            return ['label' => 'Low Stock', 'class' => 'bg-amber-50 text-amber-600'];
        }

        return ['label' => 'In Stock', 'class' => 'bg-green-50 text-green-700'];
    }

    public function getExpiryDisplayAttribute(): array
    {
        if (! $this->expiry_date) {
            return ['label' => '—', 'class' => 'text-gray-300'];
        }

        $daysLeft = now()->startOfDay()->diffInDays($this->expiry_date, false);

        if ($daysLeft < 0) {
            return ['label' => 'Expired', 'class' => 'text-red-600 font-semibold'];
        }

        if ($daysLeft <= 30) {
            return ['label' => $daysLeft . 'd', 'class' => 'text-orange-500 font-medium'];
        }

        if ($daysLeft <= 60) {
            return ['label' => $daysLeft . 'd', 'class' => 'text-yellow-600 font-medium'];
        }

        return ['label' => $this->expiry_date->format('M d, Y'), 'class' => 'text-gray-500'];
    }
}
