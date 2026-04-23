<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'box_slug',
        'box_title',
        'restaurant_name',
        'price',
        'quantity',
        'stock_available',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'stock_available' => 'integer',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function getSubtotal(): float
    {
        return (float) ($this->price * $this->quantity);
    }

    public function getFormattedPrice(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    public function getFormattedSubtotal(): string
    {
        return 'Rp ' . number_format($this->getSubtotal(), 0, ',', '.');
    }
}
