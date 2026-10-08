<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            // Set only for serialized items (a specific IMEI unit). Null for quantity-tracked items.
            $table->foreignId('phone_device_id')->nullable()->constrained()->nullOnDelete();

            $table->unsignedInteger('quantity')->default(1); // always 1 for serialized items
            $table->decimal('unit_price', 12, 2);             // selling price actually charged
            $table->decimal('cost_price', 12, 2);             // actual cost of the specific unit/product at sale time — profit is derived from this, never from current product price
            $table->decimal('subtotal', 12, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
