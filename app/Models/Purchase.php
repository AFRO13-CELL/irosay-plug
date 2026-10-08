<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    protected $fillable = [
        'supplier_id', 'user_id', 'reference', 'purchase_date', 'notes',
        'total_amount', 'amount_paid', 'status', 'received_at',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'received_at' => 'datetime',
            'total_amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function isReceived(): bool
    {
        return $this->status === 'received';
    }

    public function balance(): float
    {
        return (float) $this->total_amount - (float) $this->amount_paid;
    }

    /**
     * True only once every serialized line item has had its full quantity
     * of individual IMEI devices captured. Quantity-tracked lines don't
     * need this — their stock posts automatically when the purchase is
     * received.
     */
    public function readyToReceive(): bool
    {
        return $this->items->every(function (PurchaseItem $item) {
            return $item->product->tracking_type !== 'serialized' || $item->isFullyReceived();
        });
    }
}
