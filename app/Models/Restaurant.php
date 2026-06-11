<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Restaurant extends Model
{
    use HasFactory;

    protected $table = 'mitra_restaurants';

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'address_line',
        'latitude',
        'longitude',
        'pin',
        'access_status',
    ];

    protected $hidden = [
        'pin',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(MitraOrder::class);
    }

    /**
     * @return HasMany<MitraAccessAppeal, $this>
     */
    public function accessAppeals(): HasMany
    {
        return $this->hasMany(MitraAccessAppeal::class, 'restaurant_id');
    }

    /**
     * @return array{label: string, classes: string}
     */
    public function mitraAccessBadge(): array
    {
        $st = $this->access_status ?? 'active';

        return match ($st) {
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
