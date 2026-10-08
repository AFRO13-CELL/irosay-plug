<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'address', 'notes', 'outstanding_balance'];

    protected function casts(): array
    {
        return ['outstanding_balance' => 'decimal:2'];
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function totalSpent(): float
    {
        return (float) $this->sales()->where('status', 'completed')->sum('total');
    }

    /**
     * Sum of every unpaid balance across this customer's sales — computed
     * live from sales.balance rather than trusting the stored column, so it
     * can never drift out of sync with what was actually sold and paid.
     */
    public function outstandingBalance(): float
    {
        return (float) $this->sales()
            ->where('balance', '>', 0)
            ->get()
            ->sum(fn (Sale $sale) => (float) $sale->balance);
    }

    public function lastSaleAt(): ?string
    {
        return optional($this->sales()->latest('sold_at')->value('sold_at'))->format('d M Y');
    }
}
