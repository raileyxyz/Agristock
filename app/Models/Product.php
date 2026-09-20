<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Inventory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'unit_id',
        'name',
        'sku',
        'minimum_stock',
        'reorder_point',
        'cost_price',
        'selling_price',
        'description',
        'status',
        'expiry_track',
    ];

    protected $casts = [
        'cost_price' => "decimal:2",
        'selling_price' => "decimal:2",
        'expiry_track' => "boolean",
        'status' => Status::class,
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', Status::ACTIVE->value);
    }

    public function scopeSearch($query, $search)
    {
        return $query->when($search, function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%");
        });
    }

    public function scopeFilterStatus($query, $status)
    {
        return $query->when($status && $status !== 'all', fn ($query) => $query->where('status', $status));
    }

    public function scopeFilterCategories($query, $categoryId)
    {
        return $query->when($categoryId, fn ($query) => $query->where('category_id', $categoryId));
    }

    public function scopeNeedsReorder($query)
    {
        return $query->where('status', Status::ACTIVE->value)
            ->withSum('inventories', 'remaining_quantity')
            ->whereRaw('(select coalesce(sum(remaining_quantity), 0) from inventories where inventories.product_id = products.id) <= products.reorder_point');
    }
}
