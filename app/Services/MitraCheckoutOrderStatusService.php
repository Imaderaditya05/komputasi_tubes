<?php

namespace App\Services;

use App\Models\CheckoutOrder;
use App\Models\Menu;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MitraCheckoutOrderStatusService
{
    public static function lifecyclePhase(CheckoutOrder $order): string
    {
        if (($order->fulfillment_status ?? '') === 'completed') {
            return 'completed';
        }
        if (($order->payment_status ?? '') === 'PAID') {
            return 'paid';
        }

        return 'pending';
    }

    public function assertOrderBelongsToRestaurant(CheckoutOrder $order, Restaurant $restaurant): void
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

    /**
     * Kelola tingkat sederhana: pending (menunggu pembayaran), paid (lunas/proses restoran), completed (selesai).
     *
     * @throws ValidationException
     */
    public function applyPhase(Restaurant $restaurant, CheckoutOrder $order, string $phase, User $mitra): void
    {
        abort_unless($mitra->role === 'mitra', 403);

        $this->assertOrderBelongsToRestaurant($order, $restaurant);

        $phase = strtolower(trim($phase));
        if (! in_array($phase, ['pending', 'paid', 'completed'], true)) {
            throw ValidationException::withMessages(['phase' => 'Status tidak valid.']);
        }

        DB::transaction(function () use ($order, $phase, $mitra): void {
            /** @var CheckoutOrder $fresh */
            $fresh = CheckoutOrder::query()->whereKey($order->getKey())->lockForUpdate()->firstOrFail();

            $method = strtolower((string) ($fresh->payment_method ?? ''));

            if ($phase === 'completed') {
                if (($fresh->payment_status ?? '') !== 'PAID') {
                    throw ValidationException::withMessages([
                        'phase' => 'Hanya pesanan lunas yang dapat ditandai selesai.',
                    ]);
                }

                $data = ['fulfillment_status' => 'completed'];

                if ($fresh->fulfillment_method === 'pickup') {
                    $data['pickup_validation_status'] = CheckoutOrder::PICKUP_VALIDATION_VALIDATED;
                    $data['pickup_validated_at'] = now();
                    $data['pickup_validated_by'] = $mitra->id;
                }

                $fresh->update($data);

                return;
            }

            if ($phase === 'paid') {
                if (($fresh->payment_status ?? '') === 'PAID') {
                    if (in_array($fresh->fulfillment_status, [null, 'awaiting_payment'], true)) {
                        $fresh->update(['fulfillment_status' => 'pending_confirmation']);
                    }
                    MenuStockService::applyForOrder($fresh);

                    return;
                }

                if (($fresh->payment_status ?? '') === 'PENDING_COD') {
                    $fresh->update([
                        'payment_status' => 'PAID',
                    ]);
                    MenuStockService::applyForOrder($fresh);

                    return;
                }

                if (($fresh->payment_status ?? '') === 'PENDING' && $method !== 'cod') {
                    throw ValidationException::withMessages([
                        'phase' => 'Pembayaran online dikonfirmasi otomatis (Midtrans). Mitra tidak perlu menandai lunas secara manual.',
                    ]);
                }

                throw ValidationException::withMessages([
                    'phase' => 'Pesanan ini tidak dapat ditandai lunas dalam status pembayaran saat ini.',
                ]);
            }

            // pending — pengembalian ke «menunggu» untuk COD tertentu; jika sudah unpaid, noop
            if ($method !== 'cod') {
                throw ValidationException::withMessages([
                    'phase' => 'Hanya pesanan COD yang dapat dikembalikan ke menunggu pembayaran.',
                ]);
            }

            if (($fresh->payment_status ?? '') !== 'PAID') {
                return;
            }

            if ($fresh->menu_stock_applied) {
                throw ValidationException::withMessages([
                    'phase' => 'Stok sudah dicatat; tidak dapat mengembalikan ke menunggu pembayaran.',
                ]);
            }

            $fresh->update([
                'payment_status' => 'PENDING_COD',
                'fulfillment_status' => 'awaiting_payment',
            ]);
        });
    }
}
