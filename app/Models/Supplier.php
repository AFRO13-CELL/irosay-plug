<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'location', 'notes', 'amount_owed'];

    protected function casts(): array
    {
        return ['amount_owed' => 'decimal:2'];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function phoneDevices(): HasMany
    {
        return $this->hasMany(PhoneDevice::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }
}
