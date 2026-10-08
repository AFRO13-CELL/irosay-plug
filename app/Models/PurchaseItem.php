<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseItem extends Model
{
    protected $fillable = ['purchase_id', 'product_id', 'quantity', 'buying_price'];

    protected function casts(): array
    {
        return ['buying_price' => 'decimal:2'];
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function phoneDevices(): HasMany
    {
        return $this->hasMany(PhoneDevice::class);
    }

    public function devicesReceivedCount(): int
    {
        return $this->phoneDevices()->count();
    }

    public function isFullyReceived(): bool
    {
        return $this->devicesReceivedCount() >= $this->quantity;
    }

    public function remainingToReceive(): int
    {
        return max(0, $this->quantity - $this->devicesReceivedCount());
    }
}
