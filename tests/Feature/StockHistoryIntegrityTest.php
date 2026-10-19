<?php

use App\Models\Product;
use App\Models\StockOut;
use App\Models\User;
use Illuminate\Database\QueryException;

it('does not let a user with stock-out history be hard deleted', function () {
    $user = User::factory()->create();
    StockOut::factory()->create(['user_id' => $user->id]);

    expect(fn () => $user->delete())->toThrow(QueryException::class);
    expect(StockOut::count())->toBe(1);
});

it('does not let a product with stock-out history be hard deleted', function () {
    $product = Product::factory()->create();
    StockOut::factory()->create(['product_id' => $product->id]);

    expect(fn () => $product->delete())->toThrow(QueryException::class);
    expect(StockOut::count())->toBe(1);
});
