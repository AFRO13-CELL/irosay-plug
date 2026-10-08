<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phone_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();

            $table->string('model');           // denormalized copy for fast display/search
            $table->string('storage')->nullable();
            $table->string('color')->nullable();

            $table->enum('condition', ['preowned', 'brand_new'])->default('preowned');
            $table->enum('activation_status', ['active', 'non_active', 'just_active'])->default('active');
            $table->enum('packaging', ['sealed', 'with_box', 'without_box'])->default('without_box');

            $table->string('imei1')->unique();
            $table->string('imei2')->nullable()->unique();
            $table->string('serial_number')->nullable()->unique();
            $table->unsignedTinyInteger('battery_health')->nullable();

            $table->decimal('buying_price', 12, 2);
            $table->decimal('selling_price', 12, 2);

            $table->string('warranty')->nullable();
            $table->date('purchase_date')->nullable();

            $table->enum('status', ['in_stock', 'reserved', 'sold', 'returned'])->default('in_stock');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['product_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phone_devices');
    }
};
