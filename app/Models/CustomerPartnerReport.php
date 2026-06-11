<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerPartnerReport extends Model
{
    protected $fillable = [
        'user_id',
        'partner_key',
        'mitra_restaurant_id',
        'admin_restaurant_id',
        'restaurant_display_name',
        'box_slug',
        'category',
        'message',
        'status',
    ];

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function mitraRestaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class, 'mitra_restaurant_id');
    }

    public function adminRestaurant(): BelongsTo
    {
        return $this->belongsTo(AdminRestaurant::class, 'admin_restaurant_id');
    }
}
