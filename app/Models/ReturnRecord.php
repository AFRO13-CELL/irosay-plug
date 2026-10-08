<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Named ReturnRecord (not Return) because `return` is a reserved word in PHP.
class ReturnRecord extends Model
{
    protected $table = 'returns';

    protected $fillable = ['sale_id', 'user_id', 'reason', 'refund_amount', 'status'];

    protected function casts(): array
    {
        return ['refund_amount' => 'decimal:2'];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }
}
