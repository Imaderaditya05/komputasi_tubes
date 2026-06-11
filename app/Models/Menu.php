<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Menu extends Model
{
    use HasFactory;

    protected $table = 'mitra_menus';

    protected $fillable = [
        'restaurant_id',
        'name',
        'category',
        'description',
        'price',
        'original_price',
        'stock',
        'pickup_time',
        'image_url',
        'avg_rating',
        'ratings_count',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'original_price' => 'decimal:2',
            'stock' => 'integer',
            'avg_rating' => 'decimal:2',
            'ratings_count' => 'integer',
        ];
    }

    public function savingsAmount(): float
    {
        $orig = (float) $this->original_price;
        $sell = (float) $this->price;

        return max(0, $orig - $sell);
    }

    public function savingsPercent(): int
    {
        $orig = (float) $this->original_price;
        if ($orig <= 0) {
            return 0;
        }

        return (int) round((1 - ((float) $this->price / $orig)) * 100);
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }
}
