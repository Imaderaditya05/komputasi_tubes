<?php

namespace App\Services;

use App\Models\CheckoutOrder;
use App\Models\Menu;
use App\Models\Restaurant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PickupValidationService
{
    public function windowMinutes(): int
    {
        return (int) config('surprisebite.pickup_validation_window_minutes', 15);
    }

    /**
     * Mulai window validasi jika pesanan pickup aktif dan belum ada sub-status final.
     * Dipanggil secara lazy dari controller/polling/order tracking.
     */
    public function ensurePickupValidationWindowStarted(CheckoutOrder $order): void
    {
        if (! $order->fulfillmentAllowsPickupValidation()) {
            return;
        }

        if (! $order->isPaidOrCod()) {
            return;
        }

        if (in_array($order->pickup_validation_status, [
            CheckoutOrder::PICKUP_VALIDATION_VALIDATED,
            CheckoutOrder::PICKUP_VALIDATION_EXPIRED,
        ], true)) {
            return;
        }

        if (
            $order->pickup_validation_status === CheckoutOrder::PICKUP_VALIDATION_PENDING
            && $order->pickup_validation_deadline_at instanceof Carbon
        ) {
            return;
        }

        $now = now();
        $order->pickup_validation_status = CheckoutOrder::PICKUP_VALIDATION_PENDING;
        $order->pickup_validation_started_at ??= $now;
        $order->pickup_validation_deadline_at = $order->pickup_validation_started_at->copy()->addMinutes($this->windowMinutes());

        CheckoutOrder::query()->whereKey($order->getKey())->update([
            'pickup_validation_status' => CheckoutOrder::PICKUP_VALIDATION_PENDING,
            'pickup_validation_started_at' => $order->pickup_validation_started_at,
            'pickup_validation_deadline_at' => $order->pickup_validation_deadline_at,
        ]);

        $order->refresh();
    }

    /** @return bool true jika order terubah (kedaluwarsa) */
    public function syncExpiredIfMissedDeadline(CheckoutOrder $order): bool
    {
        if ($order->pickup_validation_status !== CheckoutOrder::PICKUP_VALIDATION_PENDING) {
            return false;
        }

        $deadline = $order->pickup_validation_deadline_at;
        if ($deadline === null) {
            return false;
        }

        if (now()->lte($deadline)) {
            return false;
        }

        CheckoutOrder::query()->whereKey($order->getKey())->where(
            'pickup_validation_status',
            CheckoutOrder::PICKUP_VALIDATION_PENDING,
        )->update(['pickup_validation_status' => CheckoutOrder::PICKUP_VALIDATION_EXPIRED]);

        $order->refresh();

        return true;
    }

    /** Sisa detik sampai deadline (0 jika kedaluwarsa / tidak relevan); dipaksa dari server clock. */
    public function remainingSeconds(?Carbon $deadline): int
    {
        if ($deadline === null) {
            return 0;
        }

        return max(0, $deadline->timestamp - now()->timestamp);
    }

    /** Payload ringkas untuk frontend / polling. */
    public function livePayloadForMitra(CheckoutOrder $order): array
    {
        $deadline = $order->pickup_validation_deadline_at;

        return [
            'pickup_validation_status' => $order->pickup_validation_status,
            'pickup_validation_started_at' => $order->pickup_validation_started_at?->toIso8601String(),
            'pickup_validation_deadline_at' => $deadline?->toIso8601String(),
            'pickup_validated_at' => $order->pickup_validated_at?->toIso8601String(),
            'pickup_validation_note' => $order->pickup_validation_note,
            'pickup_validation_seconds_remaining' => $order->pickup_validation_status === CheckoutOrder::PICKUP_VALIDATION_PENDING
                && $order->fulfillmentAllowsPickupValidation()
                ? $this->remainingSeconds($deadline instanceof Carbon ? $deadline : null)
                : 0,
            'fulfillment_status' => $order->fulfillment_status,
            'updated_at' => $order->updated_at?->toIso8601String(),
            /** Waktu server untuk sinkron tampilan realtime (sumber kebenaran di backend). */
            'server_time_iso' => now()->toIso8601String(),
        ];
    }

    /** Payload untuk pelanggan (tanpa menyimpan catatan mitra secara default). */
    public function livePayloadForCustomer(CheckoutOrder $order): array
    {
        $deadline = $order->pickup_validation_deadline_at;

        return [
            'pickup_validation_status' => $order->pickup_validation_status,
            'pickup_validation_started_at' => $order->pickup_validation_started_at?->toIso8601String(),
            'pickup_validation_deadline_at' => $deadline?->toIso8601String(),
            'pickup_validated_at' => $order->pickup_validated_at?->toIso8601String(),
            'pickup_validation_seconds_remaining' => $order->pickup_validation_status === CheckoutOrder::PICKUP_VALIDATION_PENDING
                && $order->fulfillmentAllowsPickupValidation()
                ? $this->remainingSeconds($deadline instanceof Carbon ? $deadline : null)
                : 0,
        ];
    }

    public function rejectUnlessMitraRestaurant(CheckoutOrder $order, Restaurant $restaurant): void
    {
        if (! str_starts_with((string) $order->box_slug, 'mitra-menu-')) {
            abort(404);
        }

        $menuId = (int) substr((string) $order->box_slug, strlen('mitra-menu-'));
        $menu = Menu::query()->find($menuId);

        if ($menu === null || $menu->restaurant_id !== $restaurant->id) {
            abort(403);
        }
    }

    /** @throws ValidationException */
    public function submitForMitra(CheckoutOrder $order, Restaurant $restaurant, User $mitra, string $note): CheckoutOrder
    {
        $this->rejectUnlessMitraRestaurant($order, $restaurant);

        if (! $order->fulfillmentAllowsPickupValidation()) {
            throw ValidationException::withMessages([
                'pickup_validation_note' => [
                    'Validasi pickup hanya tersedia setelah pembayaran/COD sah, untuk pesanan yang sudah masuk ke restoran '
                    .'(konfirmasi, diterima, disiapkan, atau siap diambil).',
                ],
            ]);
        }

        return DB::transaction(function () use ($order, $mitra, $note): CheckoutOrder {
            /** @var CheckoutOrder $fresh */
            $fresh = CheckoutOrder::query()->whereKey($order->getKey())->firstOrFail();

            $this->ensurePickupValidationWindowStarted($fresh);
            $fresh->refresh();
            $this->syncExpiredIfMissedDeadline($fresh);

            if ($fresh->pickup_validation_status === CheckoutOrder::PICKUP_VALIDATION_EXPIRED) {
                throw ValidationException::withMessages([
                    'pickup_validation_note' => ['Batas waktu validasi pickup sudah lewat.'],
                ]);
            }

            if ($fresh->pickup_validation_status !== CheckoutOrder::PICKUP_VALIDATION_PENDING) {
                throw ValidationException::withMessages([
                    'pickup_validation_note' => ['Pesanan ini tidak dalam status menunggu validasi pickup.'],
                ]);
            }

            $deadline = $fresh->pickup_validation_deadline_at;
            if ($deadline === null || now()->gt($deadline)) {
                CheckoutOrder::query()->whereKey($fresh->getKey())->update([
                    'pickup_validation_status' => CheckoutOrder::PICKUP_VALIDATION_EXPIRED,
                ]);
                $fresh->refresh();

                throw ValidationException::withMessages([
                    'pickup_validation_note' => ['Batas waktu validasi pickup sudah lewat.'],
                ]);
            }

            $now = now();
            $allowed = CheckoutOrder::FULFILLMENT_PICKUP_VALIDATION_ACTIVE;
            $affected = CheckoutOrder::query()
                ->whereKey($fresh->getKey())
                ->whereIn('fulfillment_status', $allowed)
                ->where('pickup_validation_status', CheckoutOrder::PICKUP_VALIDATION_PENDING)
                ->update([
                    'pickup_validation_status' => CheckoutOrder::PICKUP_VALIDATION_VALIDATED,
                    'pickup_validated_at' => $now,
                    'pickup_validation_note' => $note !== '' ? $note : null,
                    'pickup_validated_by' => $mitra->getKey(),
                    'fulfillment_status' => 'completed',
                ]);

            if ($affected !== 1) {
                throw ValidationException::withMessages([
                    'pickup_validation_note' => ['Gagal menyimpan validasi pickup. Muat ulang halaman dan coba lagi.'],
                ]);
            }

            $fresh->refresh();

            return $fresh;
        });
    }
}
