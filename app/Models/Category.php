<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{

    protected $fillable = [
        'name',
        'description',
        'icon',
        'icon_color',
        'status',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class);
    }

    public function scopeSearch($query,$search)
    {
        return $query->when($search,function($query) use($search){

            $query->where('name','like',"%{$search}%")->orWhere('description','like',"%{$search}%");

        });
    }

    public function scopeFilterStatus($query,$status)
    {
        return $query->when($status && $status !== 'all', fn($query)=>$query->where('status',$status));
    }

}
