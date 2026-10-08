<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Links a received IMEI unit back to the purchase line that brought
        // it in, so a purchase can show "3 of 5 devices received" and know
        // exactly which units still need their IMEI captured.
        Schema::table('phone_devices', function (Blueprint $table) {
            $table->foreignId('purchase_item_id')->nullable()->after('supplier_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('phone_devices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('purchase_item_id');
        });
    }
};
