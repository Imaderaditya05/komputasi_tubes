<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerNotification extends Model
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

    /** Satu pelanggan = satu Email User(customer); idempoten per kombinasi (user, type, order). */
    public static function recordForCustomer(?string $customerEmail, string $typeKey, ?string $publicOrderId, string $title, string $body): void
    {
        if ($customerEmail === null || trim($customerEmail) === '') {
            return;
        }

        $user = User::query()
            ->where('email', $customerEmail)
            ->where('role', 'customer')
            ->first();

        if ($user === null) {
            return;
        }

        $typeKey = substr($typeKey, 0, 64);

        self::query()->firstOrCreate(
            [
                'user_id' => $user->id,
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
