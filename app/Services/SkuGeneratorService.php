<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;


class SkuGeneratorService
{

    public function generate(Category $category): string
    {
        $prefix = strtoupper(
            substr($category->name,0,3)
        );

        $count = Product::where('category_id',$category->id)->count();

        $number = $count + 1;

        return $prefix . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

}
