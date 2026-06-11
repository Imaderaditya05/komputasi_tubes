<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class MitraNotification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'body',
        'public_order_id',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function mitraOwnerUserIdForCheckoutOrder(CheckoutOrder $order): ?int
    {
        $slug = (string) ($order->box_slug ?? '');
        if (! str_starts_with($slug, 'mitra-menu-')) {
            return null;
        }

        $menuId = (int) substr($slug, strlen('mitra-menu-'));
        if ($menuId < 1) {
            return null;
        }

        $menu = Menu::query()->find($menuId);
        if ($menu === null) {
            return null;
        }

        $restaurant = Restaurant::query()->find($menu->restaurant_id);

        return $restaurant !== null ? (int) $restaurant->user_id : null;
    }

    /** Idempotent per (mitra_user, type, public_order_id). */
    public static function recordForOrder(?int $mitraUserId, string $typeKey, ?string $publicOrderId, string $title, string $body): void
    {
        if (! Schema::hasTable('mitra_notifications') || $mitraUserId === null || $mitraUserId < 1) {
            return;
        }

        $typeKey = substr($typeKey, 0, 64);

        self::query()->firstOrCreate(
            [
                'user_id' => $mitraUserId,
                'type' => $typeKey,
                'public_order_id' => $publicOrderId !== null && trim($publicOrderId) !== '' ? $publicOrderId : null,
            ],
            [
                'title' => $title,
                'body' => $body,
            ],
        );
    }
}
