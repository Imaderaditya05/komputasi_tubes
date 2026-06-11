<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CheckoutOrder;
use App\Models\User;

class CartAfterPaymentService
{
    /**
     * Hapus item keranjang yang sudah dibayar (sesuai box_slug pesanan).
     */
    public static function clearForOrder(CheckoutOrder $order): void
    {
        $user = User::query()
            ->where('email', $order->customer_email)
            ->where('role', 'customer')
            ->first();

        if (! $user) {
            return;
        }

        $cart = Cart::query()->where('user_id', $user->id)->first();
        if (! $cart) {
            return;
        }

        $item = $cart->items()->where('box_slug', $order->box_slug)->first();
        if (! $item) {
            return;
        }

        $paidQty = max(1, (int) ($order->item_quantity ?? 1));

        if ($item->quantity <= $paidQty) {
            $item->delete();
        } else {
            $item->decrement('quantity', $paidQty);
        }
    }
}
