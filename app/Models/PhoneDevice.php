<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PhoneDevice extends Model
{
    protected $fillable = [
        'product_id', 'supplier_id', 'purchase_item_id', 'model', 'storage', 'color',
        'condition', 'activation_status', 'packaging',
        'imei1', 'imei2', 'serial_number', 'battery_health',
        'buying_price', 'selling_price', 'warranty', 'purchase_date',
        'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'buying_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'purchase_date' => 'date',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    // The sale item that sold this exact unit, if any.
    public function saleItem(): HasOne
    {
        return $this->hasOne(SaleItem::class);
    }

    public function isAvailable(): bool
    {
        return $this->status === 'in_stock';
    }
}
