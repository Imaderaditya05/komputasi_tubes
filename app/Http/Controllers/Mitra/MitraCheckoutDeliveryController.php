<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\CheckoutOrder;
use App\Models\Menu;
use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MitraCheckoutDeliveryController extends Controller
{
    public function index(Request $request, Restaurant $restaurant): View
    {
        abort_unless($restaurant->user_id === $request->user()->id, 403);

        $slugs = $restaurant->menus()->pluck('id')->map(fn (int $id) => 'mitra-menu-'.$id)->all();

        $orders = collect();
        if ($slugs !== []) {
            $orders = CheckoutOrder::query()
                ->whereIn('box_slug', $slugs)
                ->where('fulfillment_method', 'delivery')
                ->orderByDesc('created_at')
                ->limit(50)
                ->get();
        }

        return view('mitra.delivery.checkout-orders', [
            'restaurant' => $restaurant,
            'orders' => $orders,
        ]);
    }

    public function updateCourier(Request $request, Restaurant $restaurant): RedirectResponse
    {
        abort_unless($restaurant->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'public_order_id' => ['required', 'string', 'max:64'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $order = CheckoutOrder::query()
            ->where('public_order_id', $validated['public_order_id'])
            ->firstOrFail();

        $this->assertCheckoutOrderBelongsToRestaurant($order, $restaurant);

        $order->update([
            'courier_latitude' => $validated['latitude'],
            'courier_longitude' => $validated['longitude'],
            'courier_updated_at' => now(),
        ]);

        return back()->with('status', 'Posisi kurir diperbarui. Pelanggan akan melihatnya di peta secara realtime.');
    }

    private function assertCheckoutOrderBelongsToRestaurant(CheckoutOrder $order, Restaurant $restaurant): void
    {
        if (! str_starts_with($order->box_slug, 'mitra-menu-')) {
            abort(404);
        }

        $menuId = (int) substr($order->box_slug, strlen('mitra-menu-'));
        $menu = Menu::query()->find($menuId);

        if ($menu === null || $menu->restaurant_id !== $restaurant->id) {
            abort(403);
        }
    }
}
