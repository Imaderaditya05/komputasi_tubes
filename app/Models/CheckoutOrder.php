<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckoutOrder extends Model
{
    protected $fillable = [
        'public_order_id',
        'customer_id',
        'customer_email',
        'box_slug',
        'box_title',
        'restaurant_name',
        'amount_idr',
        'item_quantity',
        'menu_stock_applied',
        'payment_method',
        'fulfillment_method',
        'delivery_address',
        'restaurant_latitude',
        'restaurant_longitude',
        'courier_latitude',
        'courier_longitude',
        'courier_updated_at',
        'midtrans_transaction_id',
        'payment_status',
        'payment_redirect_url',
        'fulfillment_status',
        'pickup_time',
        'reviewed',
    ];

    protected function casts(): array
    {
        return [
            'item_quantity' => 'integer',
            'menu_stock_applied' => 'boolean',
            'reviewed' => 'boolean',
            'restaurant_latitude' => 'float',
            'restaurant_longitude' => 'float',
            'courier_latitude' => 'float',
            'courier_longitude' => 'float',
            'courier_updated_at' => 'datetime',
        ];
    }

    /** @var list<string> */
    public const FULFILLMENT_SEQUENCE = [
        'pending_confirmation',
        'received',
        'preparing',
        'ready',
        'completed',
    ];

    public function isPaidOrCod(): bool
    {
        return in_array($this->payment_status, ['PAID', 'PENDING_COD'], true);
    }

    public function isHistory(): bool
    {
        return $this->fulfillment_status === 'completed';
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Fallback when customer_id menyimpan id pengguna (users) — nama untuk monitoring.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
