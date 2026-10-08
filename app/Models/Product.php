<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'supplier_id', 'name', 'brand', 'model', 'sku', 'description',
        'tracking_type', 'buying_price', 'selling_price', 'stock_quantity',
        'min_stock_level', 'status',
    ];

    protected function casts(): array
    {
        return [
            'buying_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function phoneDevices(): HasMany
    {
        return $this->hasMany(PhoneDevice::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function isSerialized(): bool
    {
        return $this->tracking_type === 'serialized';
    }

    public function isLowStock(): bool
    {
        // Only meaningful for quantity-tracked products (rule: never auto-flag serialized iPhones as low stock).
        return $this->tracking_type === 'quantity' && $this->stock_quantity <= $this->min_stock_level;
    }
}
