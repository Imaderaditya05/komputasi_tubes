<?php

namespace App\Services;

use App\Models\CheckoutOrder;
use App\Models\Menu;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MenuStockService
{
    /**
     * Kurangi stok mitra_menus untuk mystery box mitra-menu-{id} saat pembayaran terkonfirmasi.
     * Idempotent lewat checkout_orders.menu_stock_applied.
     */
    public static function applyForOrder(CheckoutOrder $order): void
    {
        $order->refresh();

        if ($order->menu_stock_applied) {
            return;
        }

        if (! self::orderQualifiesForStockDeduction($order)) {
            return;
        }

        $menuId = self::parseMitraMenuId((string) $order->box_slug);
        if ($menuId === null) {
            return;
        }

        $qty = max(1, (int) ($order->item_quantity ?? 1));

        try {
            DB::transaction(function () use ($order, $menuId, $qty): void {
                $lockedOrder = CheckoutOrder::query()->lockForUpdate()->find($order->id);
                if (! $lockedOrder || $lockedOrder->menu_stock_applied) {
                    return;
                }

                $menu = Menu::query()->lockForUpdate()->find($menuId);
                if (! $menu) {
                    Log::warning('MenuStockService: menu not found', ['menu_id' => $menuId, 'order' => $order->public_order_id]);

                    return;
                }

                $current = (int) $menu->stock;
                if ($current < $qty) {
                    Log::warning('MenuStockService: stok tidak cukup, mengurangi sebisa mungkin', [
                        'menu_id' => $menuId,
                        'stock' => $current,
                        'requested' => $qty,
                        'order' => $order->public_order_id,
                    ]);
                }

                $deduct = min($qty, max(0, $current));
                if ($deduct > 0) {
                    $menu->decrement('stock', $deduct);
                }

                $lockedOrder->forceFill(['menu_stock_applied' => true])->save();
            });
        } catch (\Throwable $e) {
            Log::error('MenuStockService: '.$e->getMessage(), ['order' => $order->public_order_id, 'exception' => $e]);
        }
    }

    private static function orderQualifiesForStockDeduction(CheckoutOrder $order): bool
    {
        return in_array($order->payment_status, ['PAID', 'PENDING_COD'], true);
    }

    public static function parseMitraMenuId(string $boxSlug): ?int
    {
        if (preg_match('/^mitra-menu-(\d+)$/', $boxSlug, $m)) {
            return (int) $m[1];
        }

        return null;
    }
}
