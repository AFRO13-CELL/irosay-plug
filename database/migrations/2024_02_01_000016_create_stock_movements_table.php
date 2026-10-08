<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Append-only ledger. Rows are never updated or deleted by the app —
        // every stock change (purchase, sale, return, adjustment, reservation)
        // gets a new row here so history can never be silently rewritten.
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('phone_device_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('type', [
                'purchase', 'sale', 'return', 'adjustment', 'reservation', 'reservation_cancelled',
            ]);

            $table->integer('quantity');          // signed: +5 received, -1 sold, etc.
            $table->unsignedInteger('previous_stock');
            $table->unsignedInteger('new_stock');

            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('reference')->nullable(); // e.g. "SALE-INV-1042", "PUR-2026-014"
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['product_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
