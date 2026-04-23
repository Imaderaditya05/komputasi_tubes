<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminRestaurant extends Model
{
    protected $fillable = [
        'slug',
        'name',
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
}
