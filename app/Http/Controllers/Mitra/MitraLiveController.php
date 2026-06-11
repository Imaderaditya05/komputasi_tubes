<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Restaurant;
use App\Services\MitraRestaurantFeedbackService;
use App\Services\MitraSalesAnalyticsService;
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
        $checkoutTouch = '';

        /** @var MitraRestaurantFeedbackService $svc */
        $svc = app(MitraRestaurantFeedbackService::class);
        $q = $svc->checkoutOrdersQuery($restaurant);
        $checkoutMax = $q->max('updated_at');
        $checkoutCount = $svc->checkoutOrdersCount($restaurant);
        if ($menus->count() > 0) {
            $checkoutTouch = (string) $checkoutMax.'|'.$checkoutCount;
        }

        $salesTouch = app(MitraSalesAnalyticsService::class)->paidOrdersFingerprint($restaurant);

        return md5(implode('|', [
            (string) $restaurant->updated_at,
            (string) ($restaurant->access_status ?? 'active'),
            (string) $menus->max('updated_at'),
            (string) $menus->count(),
            (string) $menus->sum(fn ($m) => (int) ($m->ratings_count ?? 0)),
            (string) $menus->sum(fn ($m) => (float) ($m->avg_rating ?? 0)),
            (string) $menus->sum(fn ($m) => (int) ($m->stock ?? 0)),
            $checkoutTouch,
            $salesTouch,
        ]));
    }

    public function restaurantSnapshot(Request $request, Restaurant $restaurant): JsonResponse
    {
        abort_unless($restaurant->user_id === $request->user()->id, 403);

        if (($restaurant->access_status ?? 'active') === 'locked') {
            return response()->json([
                'error' => 'access_locked',
                'message' => 'Akses restoran ditahan admin.',
            ], 403);
        }

        $menus = $restaurant->menus()->orderByDesc('id')->get();
        $stats = MitraDashboardController::computeStatsStatic($menus);
        $hash = self::fingerprintForRestaurant($restaurant);
        $ordersCount = app(MitraRestaurantFeedbackService::class)->checkoutOrdersCount($restaurant);

        $salesSvc = app(MitraSalesAnalyticsService::class);
        $salesPeriod = $salesSvc->sanitizePeriod($request->query('sales_period'));
        $sales = $salesSvc->buildPayload($restaurant, $salesPeriod);

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
                'avg_rating' => $m->avg_rating !== null ? (float) $m->avg_rating : null,
                'ratings_count' => (int) ($m->ratings_count ?? 0),
            ];
        })->values()->all();

        return response()->json([
            'hash' => $hash,
            'stats' => $stats,
            'menus' => $menusPayload,
            'menus_count' => $menus->count(),
            'orders_count' => $ordersCount,
            'sales' => $sales,
        ]);
    }
}
