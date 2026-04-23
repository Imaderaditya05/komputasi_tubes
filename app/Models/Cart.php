<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = ['user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function getTotalPrice(): float
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += ($item->price * $item->quantity);
        }
        return (float) $total;
    }

    public function getTotalQuantity(): int
    {
        return (int) $this->items()->sum('quantity');
    }

    public function isEmpty(): bool
    {
        return $this->items()->count() === 0;
    }

    public function getRestaurants(): array
    {
        return $this->items()->distinct('restaurant_name')->pluck('restaurant_name')->toArray();
    }

    public function clear(): void
    {
        $this->items()->delete();
    }
}
