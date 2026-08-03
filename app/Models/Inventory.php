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
}
