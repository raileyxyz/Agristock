<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'contact_person',
        'phone',
        'email',
        'address',
        'status',
        'notes',
    ];

    protected $casts = [
        'status' => Status::class,
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', Status::ACTIVE->value);
    }

    public function scopeSearch($query, $search)
    {
        return $query->when($search, function ($query) use ($search) {
            $query->where('company_name', 'like', "%{$search}%")
                ->orWhere('contact_person', 'like', "%{$search}%");
        });
    }

    public function scopeFilterStatus($query, $status)
    {
        return $query->when($status && $status !== 'all', fn($q) => $q->where('status', $status));
    }

    public function scopeFilterCategory($query, $categoryId)
    {
        return $query->when($categoryId, function ($query) use ($categoryId) {
            $query->whereHas('categories', fn($q) => $q->where('categories.id', $categoryId));
        });
    }
}
