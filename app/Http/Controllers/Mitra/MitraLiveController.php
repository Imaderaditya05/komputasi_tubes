<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MitraLiveController extends Controller
{
    /**
     * Sidik jari untuk polling — berubah saat menu/stok atau pesanan mitra berubah.
     */
    public static function fingerprintForRestaurant(Restaurant $restaurant): string
    {
        $menus = $restaurant->menus()->orderByDesc('id')->get();
        $maxOrderAt = $restaurant->orders()->max('updated_at');
        $ordersCount = (int) $restaurant->orders()->count();

        return md5(implode('|', [
            (string) $menus->max('updated_at'),
            (string) $menus->count(),
            (string) $menus->sum(fn ($m) => (int) $m->stock),
            (string) $maxOrderAt,
            (string) $ordersCount,
        ]));
    }

    public function restaurantSnapshot(Request $request, Restaurant $restaurant): JsonResponse
    {
        abort_unless($restaurant->user_id === $request->user()->id, 403);

        $menus = $restaurant->menus()->orderByDesc('id')->get();
        $stats = MitraDashboardController::computeStatsStatic($menus);
        $hash = self::fingerprintForRestaurant($restaurant);
        $ordersCount = (int) $restaurant->orders()->count();

        $menusPayload = $menus->map(static function (Menu $m): array {
            return [
                'id' => $m->id,
                'name' => $m->name,
                'price' => (float) $m->price,
                'original_price' => (float) $m->original_price,
                'category' => $m->category,
                'description' => $m->description,
                'stock' => (int) $m->stock,
                'pickup_time' => $m->pickup_time,
                'image_url' => $m->image_url,
                'savings_percent' => $m->savingsPercent(),
            ];
        })->values()->all();

        return response()->json([
            'hash' => $hash,
            'stats' => $stats,
            'menus' => $menusPayload,
            'menus_count' => $menus->count(),
            'orders_count' => $ordersCount,
        ]);
    }
}
