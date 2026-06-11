<?php

namespace App\Http\Controllers\Concerns;

use App\Models\CheckoutOrder;
use App\Models\Menu;
use App\Models\Restaurant;

trait AssertMitraRestaurantCheckoutOrder
{
    protected function assertCheckoutOrderBelongsToRestaurant(CheckoutOrder $order, Restaurant $restaurant): void
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
}
