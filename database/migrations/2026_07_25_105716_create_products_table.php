<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
                ->constrained()
                ->cascadeOnDelete()
                ->restrictOnDelete();

            $table->foreignId('unit_id')
                ->constrained()
                ->cascadeOnDelete()
                ->restrictOnDelete();

            $table->string('name');

            $table->string('sku')->unique();

            $table->unsignedInteger('minimum_stock')->default(0);

            $table->unsignedInteger('reorder_point')->default(0);

            $table->decimal('cost_price', 10, 2);

            $table->decimal('selling_price', 10, 2);

            $table->text('description')->nullable();

            $table->enum('status', [
                'Active',
                'Archived'
            ])->default('Active');

            $table->boolean('expiry_track')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
