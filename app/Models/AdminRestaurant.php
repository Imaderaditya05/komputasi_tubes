<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminRestaurant extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'owner_name',
        'area',
        'city',
        'rating',
        'reviews_count',
        'description',
        'image_url',
        'address_line',
        'latitude',
        'longitude',
        'status',
        'sort_order',
        'boxes_json',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'float',
            'reviews_count' => 'integer',
            'sort_order' => 'integer',
            'boxes_json' => 'array',
        ];
    }

    /**
     * @return array{label: string, classes: string}
     */
    public function mitraAccessBadge(): array
    {
        return match ($this->status) {
            'locked' => [
                'label' => '🔒 Locked',
                'classes' => 'bg-red-500 text-white shadow',
            ],
            'pending' => [
                'label' => 'Pending',
                'classes' => 'bg-amber-500 text-white shadow',
            ],
            'active' => [
                'label' => '✓ Unlocked',
                'classes' => 'bg-emerald-500 text-white shadow',
            ],
            default => [
                'label' => 'Pending',
                'classes' => 'bg-amber-500 text-white shadow',
            ],
        };
    }
}
