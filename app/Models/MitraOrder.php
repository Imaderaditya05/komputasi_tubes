<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MitraOrder extends Model
{
    use HasFactory;

    protected $table = 'mitra_orders';

    protected $fillable = [
        'restaurant_id',
        'customer_name',
        'total_price',
        'status',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }
}
