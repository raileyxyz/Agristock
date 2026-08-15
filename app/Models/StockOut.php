<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOut extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'location',
        'quantity',
        'batch_numbers',
        'reason',
        'transfer_to',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

}
