<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');            // e.g. "iPhone 13 Pro Max 256GB"
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('sku')->nullable()->unique();
            $table->text('description')->nullable();
            $table->enum('tracking_type', ['serialized', 'quantity'])->default('quantity');
            $table->decimal('buying_price', 12, 2)->default(0);   // default/reference cost; actual cost per unit lives on phone_devices for serialized items
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->unsignedInteger('stock_quantity')->default(0); // authoritative only for quantity-tracked products
            $table->unsignedInteger('min_stock_level')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index(['category_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
