<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class MitraDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $restaurants = Restaurant::where('user_id', $user->id)->orderBy('id')->get();
        $restaurant = $restaurants->first();

        $menus = collect();
        $stats = self::emptyStats();

        if ($restaurant) {
            $menus = $restaurant->menus()->orderByDesc('id')->get();
            $stats = self::computeStatsStatic($menus);
        }

        $mitraLiveHash = $restaurant
            ? MitraLiveController::fingerprintForRestaurant($restaurant)
            : '';

        return view('mitra.dashboard', [
            'restaurants' => $restaurants,
            'restaurant' => $restaurant,
            'menus' => $menus,
            'stats' => $stats,
            'mitraLiveHash' => $mitraLiveHash,
        ]);
    }

    /**
     * @param  Collection<int, \App\Models\Menu>  $menus
     * @return array{total_boxes: int, total_stock: int, revenue_estimate: float, avg_savings: int}
     */
    public static function computeStatsStatic(Collection $menus): array
    {
        if ($menus->isEmpty()) {
            return self::emptyStats();
        }

        $totalBoxes = $menus->count();
        $totalStock = (int) $menus->sum('stock');

        /** Estimasi pendapatan kotor jika semua stok terjual pada harga jual */
        $revenueEstimate = (float) $menus->sum(fn ($m) => (float) $m->price * (int) $m->stock);

        $savingsValues = $menus->map(function ($m) {
            return max(0, (float) $m->original_price - (float) $m->price);
        });

        $avgSavings = (int) round($savingsValues->avg() ?? 0);

        return [
            'total_boxes' => $totalBoxes,
            'total_stock' => $totalStock,
            'revenue_estimate' => $revenueEstimate,
            'avg_savings' => $avgSavings,
        ];
    }

    /**
     * @return array{total_boxes: int, total_stock: int, revenue_estimate: float, avg_savings: int}
     */
    public static function emptyStats(): array
    {
        return [
            'total_boxes' => 0,
            'total_stock' => 0,
            'revenue_estimate' => 0.0,
            'avg_savings' => 0,
        ];
    }
}
